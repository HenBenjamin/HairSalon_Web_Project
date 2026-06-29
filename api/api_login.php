<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

use Detection\MobileDetect;
use Firebase\JWT\JWT;

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

    // 1. JWT TOKEN GENERÁLÁSA
    $issuedAt = time();
    $expire = $issuedAt + (60 * 60 * 24 * 30); // 30 napig érvényes token

    $payload = [
        "iat" => $issuedAt, 
        "exp" => $expire,  
        "user" => [
            "id" => $user['user_id'] ?? $user['id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]
    ];

    $jwt = \Firebase\JWT\JWT::encode($payload, JWT_SECRET, JWT_ALG);


    try {
        $deviceType = 'pc';
        if (class_exists('Detection\MobileDetect')) {
            $detect = new \Detection\MobileDetect();
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
        // Ha a naplózás elhasalna (pl. nincs net az IP API-hoz), a login akkor is működni fog!
    }


    // 3. egyetlen, sikeres válasz kiküldése a tokannel
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "token" => $jwt, // A mobilapp megkapja a titkosított tokent
        "user" => [
            "id" => $user['user_id'] ?? $user['id'],
            "user_id" => $user['user_id'] ?? $user['id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]
    ]);
    exit;

} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Hibás email vagy jelszó!"]);
    exit;
}