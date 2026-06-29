<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

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

try {
    $token = null;
    if (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $token = trim($_SERVER["HTTP_X_AUTHORIZATION"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $token = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (isset($_SERVER['Authorization'])) {
        $token = trim($_SERVER["Authorization"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['X-Authorization'])) {
            $token = trim($requestHeaders['X-Authorization']);
        } elseif (isset($requestHeaders['Authorization'])) {
            $token = trim($requestHeaders['Authorization']);
        }
    }

    if (!empty($token) && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
        $token = $matches[1];
    }

    if (!$token) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Hiányzó token."]);
        exit;
    }

    $decoded = \Firebase\JWT\JWT::decode($token, new Key(JWT_SECRET, JWT_ALG));
    $userId = $decoded->user->id ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Érvénytelen munkamenet."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id, username, email, role, profile_pic, mobilenumber FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "A felhasználó nem található."]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $user['user_id'],
            "user_id" => $user['user_id'],
            "username" => $user['username'],
            "email" => $user['email'],
            "role" => $user['role'],
            "profile_picture" => $user['profile_pic'],
            "mobilenumber" => $user['mobilenumber']
        ]
    ]);
    exit;

} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Hiba történt a profil adatok lekérésekor.",
        "error_details" => $e->getMessage()
    ]);
    exit;
}