<?php
session_start();
require_once "config.php";

if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: services.php");
exit;