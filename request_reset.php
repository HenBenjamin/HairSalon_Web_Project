<?php
session_start();
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Ellenőrizzük, létezik-e a felhasználó
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Token mentése
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // PHPMailer küldés
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 587;
            $mail->Username = 'cdea5d5a5f0a76';
            $mail->Password = '5e6b961416d809';

            $mail->setFrom('no-reply@hairsalon.hu', 'Fodrászat - Jelszókezelő');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Jelszo visszaallitasi kerelem';

            //$resetLink = "http://localhost/hwp/hairsalon_projekt/reset_password.php?token=$token";
            // Aktivációs link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $resetLink = "$protocol://$host$path/reset_password.php?token=$token";

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px;'>
                    <h2>Elfelejtetted a jelszavad?</h2>
                    <p>Semmi baj, kattints az alabbi gombra az uj jelszo megadasahoz:</p>
                    <a href='$resetLink' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Uj jelszo beallitasa</a>
                    <p>A link 1 oran belul lejar.</p>
                </div>";

            $mail->send();
            $message = "Az e-mailt elküldtük! Ellenőrizd a Mailtrap fiókodat.";
            $messageClass = "alert-success";
        } catch (Exception $e) {
            $message = "Hiba történt az üzenet küldésekor.";
            $messageClass = "alert-danger";
        }
    } else {
        $message = "Ezzel az e-mail címmel nincs regisztrált felhasználó.";
        $messageClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jelszó visszaállítása</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow p-4">
                <h3 class="text-center mb-4">Elfelejtett jelszó</h3>

                <?php if ($message): ?>
                    <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="request_reset.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Regisztrált e-mail cím:</label>
                        <input type="email" name="email" class="form-control" placeholder="pelda@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Jelszó-emlékeztető küldése</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">Vissza a bejelentkezéshez</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>