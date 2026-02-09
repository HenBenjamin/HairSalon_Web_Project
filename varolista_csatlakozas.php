<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Elmentjük a service_id-t is, amit az URL-ből kapunk
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if ($appointment_id <= 0) {
    header("Location: szalonok.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status, appointment_date, salon_id FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment || $appointment['status'] !== 'booked') {
        header("Location: szalonok.php");
        exit;
    }

    $check = $pdo->prepare("SELECT COUNT(*) FROM waiting_list WHERE appointment_id = ? AND user_id = ?");
    $check->execute([$appointment_id, $user_id]);

    if ($check->fetchColumn() > 0) {
        $status = "already_subscribed";
    } else {
        $insert = $pdo->prepare("INSERT INTO waiting_list (appointment_id, user_id, joined_at) VALUES (?, ?, NOW())");
        $insert->execute([$appointment_id, $user_id]);
        $status = "success";
    }

    // Visszairányítás minden szükséges paraméterrel!
    header("Location: idopont_valasztas.php?salon_id=" . $appointment['salon_id'] .
        "&date=" . $appointment['appointment_date'] .
        "&service_id=" . $service_id .
        "&waitlist_msg=" . $status);
    exit;

} catch (PDOException $e) {
    die("Hiba történt: " . $e->getMessage());
}