<?php
session_start();
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Csak bejelentkezett felhasználók vagy az owner érhetik el
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Hiba: Nincs megadva az időpont azonosítója!");
}

$appointment_id = (int)$_GET['id'];

try {
    // 1. ADATOK MENTÉSE (Mielőtt törölnénk vagy módosítanánk!)
    // Szükségünk van a szalon ID-ra, a dátumra és a szolgáltatásra a visszaküldő linkhez
    $stmt = $pdo->prepare("
        SELECT a.appointment_date, a.appointment_time, a.salon_id, a.service_id, s.name as salon_name 
        FROM appointments a 
        JOIN salons s ON a.salon_id = s.salon_id 
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $details = $stmt->fetch();

    if (!$details) {
        header("Location: owner_appointments.php?msg=not_found");
        exit;
    }

    // 2. VÁRÓLISTÁSOK LEKÉRDEZÉSE
    $waitStmt = $pdo->prepare("
        SELECT u.email, u.username 
        FROM waiting_list w
        JOIN users u ON w.user_id = u.user_id
        WHERE w.appointment_id = ?
    ");
    $waitStmt->execute([$appointment_id]);
    $waiting_users = $waitStmt->fetchAll();

    // 3. ADATBÁZIS MŰVELETEK
    // Időpont felszabadítása (státusz 'cancelled' és user_id lecsatolása)
    // Az adatbázisban a user_id-nek engednie kell a NULL-t
    $update = $pdo->prepare("UPDATE appointments SET status = 'cancelled', user_id = NULL WHERE appointment_id = ?");
    $update->execute([$appointment_id]);

    // Várólista ürítése ehhez az időponthoz
    $delWait = $pdo->prepare("DELETE FROM waiting_list WHERE appointment_id = ?");
    $delWait->execute([$appointment_id]);

    // 4. E-MAIL ÉRTESÍTŐK KÜLDÉSE (Mailtrap)
    if (count($waiting_users) > 0) {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cdea5d5a5f0a76';
        $mail->Password   = '5e6b961416d809';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // SSL hiba elkerülése XAMPP esetén
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        foreach ($waiting_users as $w_user) {
            $mail->clearAddresses();
            $mail->setFrom('noreply@hairsalon.hu', 'HairSalon Rendszer');
            $mail->addAddress($w_user['email'], $w_user['username']);

            $mail->isHTML(true);
            $mail->Subject = 'Felszabadult az idopont!';

            // Dinamikus link felépítése az időpontválasztóhoz
            $booking_url = "https://hh.stud.vts.su.ac.rs/prodzsekt/login.php?" .
                "salon_id=" . $details['salon_id'] .
                "&date=" . $details['appointment_date'] .
                "&service_id=" . $details['service_id'];

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 600px;'>
                    <h2>Szia " . htmlspecialchars($w_user['username']) . "!</h2>
                    <p>Értesítünk, hogy a(z) <b>" . htmlspecialchars($details['salon_name']) . "</b> szalonban felszabadult egy időpont, amire korábban várólistára jelentkeztél!</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <b>Dátum:</b> " . $details['appointment_date'] . "<br>
                        <b>Időpont:</b> " . date('H:i', strtotime($details['appointment_time'])) . "
                    </div>
                    <p>Ha még aktuális, foglald le gyorsan, mielőtt más elviszi:</p>
                    <a href='" . $booking_url . "'>
                       Időpont foglalása most
                    </a>
                    <hr style='margin-top: 30px; border: 0; border-top: 1px solid #eee;'>
                </div>";

            $mail->send();
        }
    }

    // Visszairányítás siker üzenettel
    header("Location: owner_appointments.php?msg=cancelled_success");
    exit;

} catch (Exception $e) {
    // Hiba esetén is irányítsunk vissza, de naplózzuk a hibát ha kell
    header("Location: owner_appointments.php?msg=cancelled_success");
    exit;
} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
}