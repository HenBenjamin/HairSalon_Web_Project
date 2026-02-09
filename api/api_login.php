<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

use Detection\MobileDetect;

// 1. Pre-flight (OPTIONS) kezelés
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 2. Metódus szűrése
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST metodus engedelyezett!"]);
    exit;
}

// 3. Adatok beolvasása
$input = json_decode(file_get_contents("php://input"), true);
$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

if (!$email || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Hianyzo email vagy jelszo!"]);
    exit;
}

// 4. Felhasználó keresése
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// 5. Hitelesítés
if ($user && password_verify($password, $user['password'])) {

    try {
        $deviceType = 'pc';
        if (class_exists('Detection\MobileDetect')) {
            $detect = new MobileDetect();
            if ($detect->isTablet()) $deviceType = 'tablet';
            elseif ($detect->isMobile()) $deviceType = 'mobile';
        }

        $ip = $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $city = "Ismeretlen";

        // Külső API hívás (ip-api.com)
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $ip_json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city", false, $ctx);

        if ($ip_json) {
            $ip_data = json_decode($ip_json);
            if ($ip_data && ($ip_data->status ?? '') === 'success') {
                $city = $ip_data->city;
            }
        }

        $u_id = $user['user_id'] ?? $user['id'];
        $logStmt = $pdo->prepare("INSERT INTO visitor_logs (user_id, ip_address, device_type, city) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$u_id, $ip, $deviceType, $city]);

    } catch (\Throwable $e) {
        // Opcionális: itt küldhetunk hibat
    }

    // SIKERES VÁLASZ
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $user['user_id'] ?? $user['id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]
    ]);

} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["status" => "error", "message" => "Hibas email vagy jelszo!"]);
}