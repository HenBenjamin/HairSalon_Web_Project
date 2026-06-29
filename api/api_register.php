<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

use Detection\MobileDetect;

// 1. Pre-flight (OPTIONS) kezelés a CORS-hoz
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

// 3. Adatok beolvasása a JSON kérésből
$input = json_decode(file_get_contents("php://input"), true);
$username = isset($input['username']) ? trim($input['username']) : null;
$email = isset($input['email']) ? trim($input['email']) : null;
$mobilenumber = isset($input['mobilenumber']) ? trim($input['mobilenumber']) : null;
$password = $input['password'] ?? null;

// Szerver oldali alapvető validáció
if (!$username || !$email || !$mobilenumber || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Minden mező kitöltése kötelező!"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Érvénytelen email formátum!"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A jelszónak legalább 6 karakterből kell állnia!"]);
    exit;
}

// 4. Ellenőrzés: Létezik-e már a felhasználó ezzel az email címmel?
$checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$checkStmt->execute([$email]);
if ($checkStmt->fetch()) {
    http_response_code(409); // Conflict
    echo json_encode(["status" => "error", "message" => "Ez az email cím már regisztrálva van!"]);
    exit;
}

// 5. Jelszó biztonságos titkosítása
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$defaultRole = 'user';
$isActive = 1; 

try {
    // Adatbázisba mentés
    $stmt = $pdo->prepare("INSERT INTO users (username, email, mobilenumber, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $mobilenumber, $hashedPassword, $defaultRole, $isActive]);

    // Lekérjük a frissen generált felhasználói ID-t
    $newUserId = $pdo->lastInsertId();

    // 6. Látogatói naplózás (Visitor Log)
    try {
        $deviceType = 'pc';
        if (class_exists('Detection\MobileDetect')) {
            $detect = new MobileDetect();
            if ($detect->isTablet()) $deviceType = 'tablet';
            elseif ($detect->isMobile()) $deviceType = 'mobile';
        }

        $ip = $_SERVER['REMOTE_ADDR'] === '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $city = "Ismeretlen";

        // Külső API hívás a helyszínhez
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $ip_json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city", false, $ctx);

        if ($ip_json) {
            $ip_data = json_decode($ip_json);
            if ($ip_data && ($ip_data->status ?? '') === 'success') {
                $city = $ip_data->city;
            }
        }

        // Naplózás az adatbázisba
        $logStmt = $pdo->prepare("INSERT INTO visitor_logs (user_id, ip_address, device_type, city) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$newUserId, $ip, $deviceType, $city]);

    } catch (\Throwable $e) {
        // Ha a naplózás elhasalna (pl. nincs net az IP API-hoz), a regisztráció akkor is sikeres lesz!
    }

    // SIKERES REGISZTRÁCIÓ VÁLASZ
    http_response_code(201); // Created
    echo json_encode([
        "status" => "success",
        "message" => "Sikeres regisztráció!",
        "user" => [
            "id" => $newUserId,
            "user_id" => $newUserId,
            "username" => $username,
            "email" => $email,
            "mobilenumber" => $mobilenumber,
            "role" => $defaultRole
        ]
    ]);

} catch (\PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt a mentés során!"]);
}