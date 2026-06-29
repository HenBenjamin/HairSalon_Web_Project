<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Szerver oldali hiba történt!",
            "error_details" => $error['message']
        ]);
        exit;
    }
});

try {
    require_once "../config.php";          
    require_once '../vendor/autoload.php'; 

    // Token kiolvasása a fejlécből
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

    // A "Bearer " előtag levágása, hogy csak a tiszta JWT karakterlánc maradjon
    if (!empty($token) && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
        $token = $matches[1];
    }

    // Ha nincs meg a token, akkor küldünk hibát
    if (!$token) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Hiányzó token."]);
        exit;
    }

    // JWT Dekódolás
    $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALG));
    $userId = $decoded->user->id ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Érvénytelen token struktúra."]);
        exit;
    }

    // Pontok lekérése a felhasználóhoz
    $userStmt = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ? LIMIT 1");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    // A legfrissebb aktív (is_used = 0) kupon lekérése
    $couponStmt = $pdo->prepare("SELECT code FROM coupons WHERE user_id = ? AND is_used = 0 ORDER BY created_at DESC LIMIT 1");
    $couponStmt->execute([$userId]);
    $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);

    // Sikeres válasz küldése
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "points" => $user ? (int)$user['total_points'] : 0,
        "active_qr_token" => $coupon ? $coupon['code'] : null,
        "debug_user_id" => $userId
    ]);

} catch (\Throwable $e) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Munkamenet vagy adatbázis hiba.",
        "error_details" => $e->getMessage()
    ]);
    exit;
}