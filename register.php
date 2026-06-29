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
    $phone = trim($_POST['phone']);
    $passwordRaw = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    // 1. SZERVER OLDALI VALIDÁCIÓ
    if (empty($username) || empty($email) || empty($phone) || empty($passwordRaw) || empty($passwordConfirm)) {
        $error = "Minden mező kitöltése kötelező!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Érvénytelen e-mail formátum!";
    } elseif (strlen($passwordRaw) < 6) {
        $error = "A jelszónak legalább 6 karakterből kell állnia!";
    }
    elseif (!preg_match("/^[0-9+\s-]{7,20}$/", $phone)) {
        $error = "Érvénytelen telefonszám formátum!";
    }
     elseif ($passwordRaw !== $passwordConfirm) { // 2. Jelszo egyezés ellenőrzése
        $error = "A két jelszó nem egyezik meg!";
    } else {
        try {
            // Letezik e az email
            $checkEmailStmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $checkEmailStmt->execute([$email]);

            if ($checkEmailStmt->rowCount() > 0) {
                $error = "Ez az e-mail cím már regisztrálva van!";
            } else {
                // Adatok előkészítése a beszúráshoz
                $password = password_hash($passwordRaw, PASSWORD_BCRYPT);
                $token = bin2hex(random_bytes(16)); 

                // Aktivációs link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $activationLink = "$protocol://$host$path/activate.php?token=$token";

                // 4. Mentés az adatbázisba
                $stmt = $pdo->prepare("INSERT INTO users (email, username, mobilenumber, password, role, activation_token, is_active) 
                                     VALUES (:email, :username, :mobilenumber, :password, :role, :token, :is_active)");

                $success = $stmt->execute([
                    'email'            => $email,
                    'username'         => $username,
                    'mobilenumber'     => $phone,
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

                        // $phpmailer->setFrom('noreply@hairsalon.com', 'HairSalon');
                        // $phpmailer->addAddress($email);
                        // $phpmailer->isHTML(true);
                        // $phpmailer->Subject = 'Fiok aktivalasa - HairSalon';
                        // $phpmailer->Body = "Szia $username!<br><br>Kattints ide az aktivalashoz:<br><a href='$activationLink'>$activationLink</a>";

                        // $phpmailer->send();
                        // $message = "Sikeres regisztráció! Ellenőrizd az e-mailedet az aktiváláshoz.";

                        $phpmailer->setFrom('noreply@hairsalon.com', 'HairSalon');
                        $phpmailer->addAddress($email);
                        $phpmailer->isHTML(true);

                        
                        $phpmailer->CharSet = 'UTF-8'; 

                        $phpmailer->Subject = 'Fiók aktiválása - HairSalon';

                       
                        $phpmailer->Body = "
                        <div style='background-color: #f4f6f8; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                            <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                                
                                <h2 style='margin-top: 0; color: #1a202c; font-size: 24px; font-weight: 700; text-align: center; letter-spacing: -0.5px;'>HairSalon</h2>
                                
                                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'>
                                
                                <h3 style='color: #2d3748; font-size: 19px; margin-bottom: 12px;'>Hello $username!</h3>
                                <p style='color: #4a5568; font-size: 15px; line-height: 1.6; margin-bottom: 32px;'>
                                    Köszönjük, hogy regisztráltál a rendszerünkbe. Kérjük, kattints az alábbi gombra a fiókod aktiválásához:
                                </p>
                                
                                <div style='text-align: center; margin-bottom: 32px;'>
                                    <a href='$activationLink' style='background-color: #2d3748; color: #ffffff; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; display: inline-block;'>
                                        Fiók aktiválása
                                    </a>
                                </div>
                                
                                <p style='color: #718096; font-size: 13px; line-height: 1.5; margin-bottom: 0;'>
                                    Ha nem te hoztad létre a fiókot, az üzenetet nyugodtan figyelmen kívül hagyhatod.<br><br>
                                    Udvözlettel,<br>
                                    <strong>HairSalon csapat</strong>
                                </p>
                                
                            </div>
                        </div>
                        ";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

<div class="container d-flex justify-content-center">
    <div class="login-container w-100">
        <div class="card card-login p-4 shadow">
            <h2 class="text-center fw-bold mb-4">Regisztráció</h2>
            
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>

             <form action="register.php" method="post" id="regForm">
                
                <div class="mb-3">
                    <label for="username" class="form-label"><i class="fa-regular fa-user"></i> Felhasználónév</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fa-regular fa-envelope"></i> Email cím</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label"><i class="fa-solid fa-phone"></i> Telefonszám</label>
                    <input type="tel" name="phone" id="phone" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fa-solid fa-lock"></i> Jelszó</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label"><i class="fa-solid fa-shield-halved"></i> Jelszó megerősítése</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-szalon-foglalas btn-primary w-100 py-2">Regisztráció</button>
            </form>
            
            <p class="mt-3 text-center">Már van fiókja? <a href="login.php" class="link-primary">Jelentkezzen be itt</a></p>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script>
    // 4. Kliens oldali validáció
    document.getElementById('regForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;

        // Felhasználónév hossz ellenőrzése
        if(username.length < 3) {
            e.preventDefault();
            alert("A felhasználónévnek legalább 3 karakterből kell állnia!");
            return;
        }

        if(phone.length < 7) {
            e.preventDefault();
            alert("Kérjük, adjon meg érvényes telefonszámot!");
            return;
        }

        // Jelszó hossz ellenőrzése
        if(password.length < 6) {
            e.preventDefault();
            alert("A jelszónak legalább 6 karakterből kell állnia!");
            return;
        }

        // Egyeznek-e a jelszavak?
        if(password !== passwordConfirm) {
            e.preventDefault();
            alert("A két jelszó nem egyezik meg!");
            return;
        }
    });
</script>
</body>
</html>