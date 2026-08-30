<?php
require_once __DIR__ . "/config.php"; 
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Keressük ki a holnapi foglalásokat, amikről még nem ment ki emlékeztető
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$stmt = $pdo->prepare("
    SELECT a.*, u.email, u.username, s.name as service_name 
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_date = ? 
    AND a.status = 'booked' 
    AND a.reminder_sent = 0
");
$stmt->execute([$tomorrow]);
$reminders = $stmt->fetchAll();

foreach ($reminders as $rem) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = 'cdea5d5a5f0a76';
        $mail->Password = '5e6b961416d809';

        $mail->setFrom('no-reply@hairsalon.hu', 'Hairsalon Ertesito');
        $mail->addAddress($rem['email'], $rem['username']);

        $mail->isHTML(true);
        $mail->Subject = 'Emlekezteto: Holnapi idopont';
        $mail->Body    = "Kedves <b>{$rem['username']}</b>!<br><br>
                          Szeretnenk emlekeztetni, hogy holnap (<b>{$rem['appointment_date']}</b>) 
                          <b>{$rem['appointment_time']}</b> orakor varunk a kovetkezore: 
                          <b>{$rem['service_name']}</b>.<br><br>
                          Udvozlettel: A Szalon";

        $mail->send();

        $update = $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE appointment_id = ?");
        $update->execute([$rem['appointment_id']]);

        echo "E-mail elküldve: " . $rem['email'] . "\n";

    } catch (Exception $e) {
        echo "Hiba küldés közben: " . $mail->ErrorInfo . "\n";
    }
}