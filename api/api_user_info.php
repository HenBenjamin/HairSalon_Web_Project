<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Be kell jelentkezned!"]);
    exit;
}

try {
    $decoded = JWT::decode($matches[1], new Key(JWT_SECRET, JWT_ALG));
    $userId = $decoded->user->id;

    // lekerjuk a user nevet
    $stmt = $pdo->prepare("SELECT username, email, profile_pic FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $profilePic = $user['profile_pic'] ? $user['profile_pic'] : 'default_avatar.png';

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "username" => $user['username'],
            "email" => $user['email'],
            "profile_pic" => $profilePic
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Felhasználó nem található."]);
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen token."]);
}