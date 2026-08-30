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
            $mail->CharSet = 'UTF-8';

            $mail->Subject = 'Jelszó visszaállítási kérelem - HairSalon';

            //$resetLink = "http://localhost/hwp/hairsalon_projekt/reset_password.php?token=$token";
            // Aktivációs link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $resetLink = "$protocol://$host$path/reset_password.php?token=$token";

            $mail->Body = "
            <div style='background-color: #f4f6f8; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                    
                    <h2 style='margin-top: 0; color: #1a202c; font-size: 24px; font-weight: 700; text-align: center; letter-spacing: -0.5px;'>HairSalon</h2>
                    
                    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'>
                    
                    <h3 style='color: #2d3748; font-size: 19px; margin-bottom: 12px;'>Elfelejtetted a jelszavad?</h3>
                    <p style='color: #4a5568; font-size: 15px; line-height: 1.6; margin-bottom: 32px;'>
                        Semmi baj. Kattints az alábbi gombra az új jelszavad megadásához. Biztonsági okokból ez a link <strong>1 órán belül lejár</strong>.
                    </p>
                    
                    <div style='text-align: center; margin-bottom: 32px;'>
                        <a href='$resetLink' style='background-color: #2d3748; color: #ffffff; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; display: inline-block;'>
                            Új jelszó beállítása
                        </a>
                    </div>
                    
                    <p style='color: #718096; font-size: 13px; line-height: 1.5; margin-bottom: 0;'>
                        Ha nem te kérted a jelszavad visszaállítását, nyugodtan hagyd figyelmen kívül ezt az üzenetet, a fiókod és a jelenlegi jelszavad biztonságban marad.<br><br>
                        Üdvözlettel,<br>
                        <strong>HairSalon csapat</strong>
                    </p>
                    
                </div>
            </div>
            ";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav>
        <?php include 'navbar.php'; ?>
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow p-4">
                <h3 class="text-center fw-bold mb-4">Elfelejtett jelszó</h3>

                <?php if ($message): ?>
                    <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="request_reset.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="fa-regular fa-envelope"></i> Regisztrált e-mail cím:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-szalon-foglalas btn-primary w-100">Jelszó-emlékeztető küldése</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">Vissza a bejelentkezéshez</a>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</body>
</html>