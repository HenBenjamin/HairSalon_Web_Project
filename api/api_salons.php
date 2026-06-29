<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../config.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// token ellenorzese
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed az adatok megtekintéséhez! Be kell jelentkezned."]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    // Kiszedjük a bejelentkezett felhasználó nevét és ID-ját a tokenből!
    $loggedInUserId = $decoded->user->id;
    $loggedInUserName = $decoded->user->name ?? 'Vendég'; 
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

// token jo, jon az adatbazis lekerdezes
try {
    $stmt = $pdo->query("SELECT salon_id, name, city, address, phone, image_url FROM salons WHERE active = 1");
    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    // Becsomagoljuk a választ: visszaküldjük a szalonokat ÉS a tokenből kiolvasott aktuális felhasználónevet is
    echo json_encode([
        "status" => "success",
        "user_name" => $loggedInUserName,
        "salons" => $salons
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()]);
}