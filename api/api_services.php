<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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

// token ellenorzes
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed az adatok megtekintéséhez! Be kell jelentkezned."]);
    exit;
}

$jwt = $matches[1];

try {
    // token ervenyes-e
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

// Paraméter beolvasása és adatbázis lekérdezés
$salon_id = $_GET['salon_id'] ?? null;

if ($salon_id) {
    try {
        $stmt = $pdo->prepare("SELECT service_id, name, price, duration FROM services WHERE salon_id = ?");
        $stmt->execute([$salon_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $services
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt!"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzik a salon_id!"]);
}
exit;