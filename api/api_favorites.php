<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó user_id"]);
    exit;
}

try {
    if ($method === 'GET') {
        // Kedvenc szalonok listázása
        $stmt = $pdo->prepare("SELECT s.* FROM salons s JOIN favorites f ON s.salon_id = f.salon_id WHERE f.user_id = ?");
        $stmt->execute([$user_id]);
        $results = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode($results);
    }
    elseif ($method === 'POST') {
        // Hozzáadás a kedvencekhez
        $input = json_decode(file_get_contents('php://input'), true);
        $salon_id = $input['salon_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, salon_id) VALUES (?, ?)");  //ignore, ha a user veletlenul ketszer kuldi ugyanazt a szalont
        $stmt->execute([$user_id, $salon_id]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Kedvencekhez adva"]);
    }
    elseif ($method === 'DELETE') {
        // Eltávolítás a kedvencekből
        $salon_id = $_GET['salon_id'];
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$user_id, $salon_id]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Eltávolítva a kedvencekből"]);
    }
    else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["status" => "error", "message" => "Nem támogatott metódus"]);
    }
} catch (Exception $e) {
    http_response_code(500);  //server error
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}