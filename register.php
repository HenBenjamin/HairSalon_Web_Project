<?php
session_start();
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $passwordRaw = $_POST['password'];

    // 1. SZERVER OLDALI VALIDÁCIÓ
    if (empty($username) || empty($email) || empty($passwordRaw)) {
        $error = "Minden mező kitöltése kötelező!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Érvénytelen e-mail formátum!";
    } elseif (strlen($passwordRaw) < 6) {
        $error = "A jelszónak legalább 6 karakterből kell állnia!";
    } else {
        try {
            // 2. Létezik-e már az email?
            $checkEmailStmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $checkEmailStmt->execute([$email]);

            if ($checkEmailStmt->rowCount() > 0) {
                $error = "Ez az e-mail cím már regisztrálva van!";
            } else {
                // 3. Adatok előkészítése a beszúráshoz
                $password = password_hash($passwordRaw, PASSWORD_BCRYPT);
                $token = bin2hex(random_bytes(16)); // Token generálása ITT történik

                // Aktivációs link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $activationLink = "$protocol://$host$path/activate.php?token=$token";

                // 4. Mentés az adatbázisba (PONTOS OSZLOP SORRENDBEN)
                // user_id (null), email, username, password, role, created_at (now), activation_token, is_active
                $stmt = $pdo->prepare("INSERT INTO users (email, username, password, role, activation_token, is_active) 
                                     VALUES (:email, :username, :password, :role, :token, :is_active)");

                $success = $stmt->execute([
                    'email'            => $email,
                    'username'         => $username,
                    'password'         => $password,
                    'role'             => 'user',
                    'token'            => $token,
                    'is_active'        => 0
                ]);

                if ($success) {
                    $phpmailer = new PHPMailer(true);
                    try {
                        $phpmailer->isSMTP();
                        $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
                        $phpmailer->SMTPAuth = true;
                        $phpmailer->Port = 587;
                        $phpmailer->Username = 'cdea5d5a5f0a76';
                        $phpmailer->Password = '5e6b961416d809';

                        $phpmailer->setFrom('noreply@hairsalon.com', 'HairSalon');
                        $phpmailer->addAddress($email);
                        $phpmailer->isHTML(true);
                        $phpmailer->Subject = 'Fiok aktivalasa - HairSalon';
                        $phpmailer->Body = "Szia $username!<br><br>Kattints ide az aktivalashoz:<br><a href='$activationLink'>$activationLink</a>";

                        $phpmailer->send();
                        $message = "Sikeres regisztráció! Ellenőrizd az e-mailedet az aktiváláshoz.";
                    } catch (Exception $e) {
                        $error = "Hiba az email küldésnél, de a token az adatbázisban van!";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Adatbázis hiba: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - HairSalon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-dark text-white">

<nav class="navbar navbar-expand-sm navbar-dark" style="background-color: #010B78;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">HairSalon</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 card p-4 bg-secondary text-white">
            <h2 class="mb-4">Regisztráció</h2>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>

            <form action="register.php" method="post" id="regForm">
                <div class="mb-3">
                    <label>Felhasználónév</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Jelszó</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Regisztráció</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('regForm').addEventListener('submit', function(e) {
        if(document.getElementById('username').value.length < 3) {
            e.preventDefault();
            alert("A név túl rövid!");
        }
    });
</script>
</body>
</html>