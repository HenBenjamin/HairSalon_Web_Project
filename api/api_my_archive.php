<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak GET metodus engedelyezett!"]);
    exit;
}

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Be kell jelentkezned."]);
    exit;
}

try {
    $decoded = JWT::decode($matches[1], new Key(JWT_SECRET, JWT_ALG));
    $loggedInUserId = $decoded->user->id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen token!"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.salon_id, 
            a.appointment_date, 
            a.appointment_time,
            a.status, 
            s.name AS salon_name, 
            ser.name AS service_name 
        FROM appointments a
        JOIN salons s ON a.salon_id = s.salon_id
        JOIN services ser ON a.service_id = ser.service_id
        WHERE a.user_id = ? 
          AND (a.status = 'completed' OR a.status = 'cancelled')
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    
    $stmt->execute([$loggedInUserId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($appointments);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()]);
}