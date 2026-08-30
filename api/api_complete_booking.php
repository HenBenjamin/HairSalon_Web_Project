<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST metódus engedélyezett!"]);
    exit;
}

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs jogosultságod, hiányzó token!"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    $role = $decoded->user->role;

    if ($role !== 'owner') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Ehhez a művelethez szalon tulajdonosnak kell lenned!"]);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $appointment_id = trim($input['appointment_id'] ?? '');

    if (empty($appointment_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Hiányzó időpont azonosító!"]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT user_id, status FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "A foglalás nem található az adatbázisban!"]);
        exit;
    }

    if ($appointment['status'] === 'completed') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ez az időpont már korábban le lett zárva!"]);
        exit;
    }

    $updateAppStmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
    $updateAppStmt->execute([$appointment_id]);

    $points_to_give = 20;
    $client_id = $appointment['user_id'];
    
    $updatePointsStmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE user_id = ?");
    $updatePointsStmt->execute([$points_to_give, $client_id]);

    $stmt = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
    $stmt->execute([$client_id]);
    $currentPoints = $stmt->fetchColumn();

    $coupon_generated = false;

    if ($currentPoints >= 100) {
        $newCode = strtoupper(bin2hex(random_bytes(4)));
        
        $insertCoupon = $pdo->prepare("INSERT INTO coupons (user_id, code, discount_amount, discount_type, is_used) VALUES (?, ?, 20, 'percent', 0)");
        $insertCoupon->execute([$client_id, $newCode]);
        
        $resetPoints = $pdo->prepare("UPDATE users SET total_points = total_points - 100 WHERE user_id = ?");
        $resetPoints->execute([$client_id]);
        
        $coupon_generated = true;
    }

    $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $userStmt->execute([$client_id]);
    $client = $userStmt->fetch();

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Időpont sikeresen lezárva.",
        "username" => $client['username'] ?? 'Vendég',
        "points_earned" => $points_to_give,
        "coupon_generated" => $coupon_generated
    ]);

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Szerver hiba történt!", "details" => $e->getMessage()]);
}
exit;