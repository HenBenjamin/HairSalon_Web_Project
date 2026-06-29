<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Hiba: Nincs megadva az időpont azonosítója!");
}

$appointment_id = (int)$_GET['id'];

try {
    // ellenorzes hogy letezik-e az idopont
    $stmt = $pdo->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appointmentExists = $stmt->fetch();

    if (!$appointmentExists) {
        header("Location: owner_appointments.php?msg=not_found");
        exit;
    }

    // 2. Időpont lemondása
    $update = $pdo->prepare("UPDATE appointments SET status = 'cancelled', user_id = NULL WHERE appointment_id = ?");
    $update->execute([$appointment_id]);

    header("Location: owner_appointments.php?msg=cancelled_success");
    exit;

} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
} catch (Exception $e) {
    die("Általános hiba: " . $e->getMessage());
}