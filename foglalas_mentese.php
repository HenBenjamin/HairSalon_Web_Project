<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $salon_id = $_POST['salon_id'];
    $service_id = $_POST['service_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Mentés az appointments táblába
    $sql = "INSERT INTO appointments (user_id, salon_id, service_id, appointment_date, appointment_time, status) 
            VALUES (?, ?, ?, ?, ?, 'booked')";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$user_id, $salon_id, $service_id, $date, $time])) {
        echo "<script>alert('Sikeres foglalás!'); window.location.href='index.php';</script>";
    } else {
        echo "Hiba történt a mentés során.";
    }
}
?>