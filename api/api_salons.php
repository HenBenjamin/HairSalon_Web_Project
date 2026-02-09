<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak GET metodus engedelyezett!"]);
    exit;
}

$stmt = $pdo->query("SELECT salon_id, name, city, address FROM salons");
$salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode($salons);