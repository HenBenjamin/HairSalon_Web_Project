<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "../config.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Csak DELETE metodus engedelyezett!"
    ]);
    exit;
}

// jwt ellenorzese
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed a művelethez! Be kell jelentkezned."]);
    exit;
}

$jwt = $matches[1];

try {
    // Dekódoljuk a tokent a configban lévő titkos kulccsal
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    $loggedInUserId = $decoded->user->id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

// torlesi folyamat
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['appointment_id'] ?? null;

if ($id) {
    //csak akkor torolhet ha a foglalas user_id-ja is megegyezik a tokenbol
    $stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE appointment_id = ? AND user_id = ? AND status='booked'");
    
    if ($stmt->execute([$id, $loggedInUserId])) {
        // ellenorizzuk valoban toroltuk e a sort
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Foglalás sikeresen lemondva."]);
        } else {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Nincs jogosultságod a foglalás lemondásához, vagy az időpont nem létezik."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt a lemondás során."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó időpont azonosító."]);
}
exit;