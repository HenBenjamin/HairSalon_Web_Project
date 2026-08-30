<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
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

$method = $_SERVER['REQUEST_METHOD'];

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
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    // bejelentkezett user id-ja
    $loggedInUserId = $decoded->user->id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

try {
    // kedvenc szalonok lekérése
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT s.* FROM salons s JOIN favorites f ON s.salon_id = f.salon_id WHERE f.user_id = ?");
        $stmt->execute([$loggedInUserId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($results);
    }
    // kedvenc szalonokhoz adás
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $salon_id = $input['salon_id'] ?? null;

        if (!$salon_id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Hiányzó salon_id!"]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, salon_id) VALUES (?, ?)");
        $stmt->execute([$loggedInUserId, $salon_id]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Kedvencekhez adva"]);
    }
    // eltávolítás a kedvencek közül
    elseif ($method === 'DELETE') {
        // salon_id az url-bol szedjuk ki
        $salon_id = $_GET['salon_id'] ?? null;

        if (!$salon_id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Hiányzó salon_id az URL-ből!"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$loggedInUserId, $salon_id]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Eltávolítva a kedvencekből"]);
    }
    else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Nem támogatott metódus"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Szerverhiba: " . $e->getMessage()]);
}