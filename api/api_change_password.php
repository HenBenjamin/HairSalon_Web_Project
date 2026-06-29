<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

try {
    // 3. Token kiolvasása
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

    // 4. JWT Dekódolás
    $decoded = \Firebase\JWT\JWT::decode($token, new Key(JWT_SECRET, JWT_ALG));
    $userId = $decoded->user->id ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Érvénytelen munkamenet."]);
        exit;
    }

    // 5. Adatok beolvasása a JSON body-ból
    $input = json_decode(file_get_contents("php://input"), true);
    $currentPassword = $input['current_password'] ?? null;
    $newPassword = $input['new_password'] ?? null;
    $confirmPassword = $input['confirm_password'] ?? null;

    // Validációk
    if (!$currentPassword || !$newPassword || !$confirmPassword) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Minden mező kitöltése kötelező!"]);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "A két új jelszó nem egyezik meg!"]);
        exit;
    }

    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Az új jelszónak legalább 6 karakterből kell állnia!"]);
        exit;
    }

    // 6. Felhasználó jelenlegi jelszavának ellenőrzése
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "A jelenlegi jelszó hibás!"]);
        exit;
    }

    // 7. Új jelszó lehashelése és mentése
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateStmt->execute([$hashedPassword, $userId]);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Jelszavad sikeresen megváltozott!"
    ]);
    exit;

} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Hiba történt a feldolgozás során.",
        "error_details" => $e->getMessage()
    ]);
    exit;
}