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
    $owner_id = $decoded->user->id;
    $role = $decoded->user->role;

    if ($role !== 'owner') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Ehhez a művelethez szalon tulajdonosnak kell lenned!"]);
        exit;
    }

    $salonStmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ? LIMIT 1");
    $salonStmt->execute([$owner_id]);
    $salon = $salonStmt->fetch();

    if (!$salon) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Ehhez a felhasználóhoz nincs szalon regisztrálva!"]);
        exit;
    }
    $owner_salon_id = $salon['salon_id'];

    $input = json_decode(file_get_contents("php://input"), true);
    $coupon_code = trim($input['coupon_code'] ?? '');

    if (empty($coupon_code)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Hiányzó kuponkód!"]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT coupon_id, user_id, is_used, discount_amount FROM coupons WHERE code = ?");
    $stmt->execute([$coupon_code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "A kupon nem található az adatbázisban!"]);
        exit;
    }

    if ($coupon['is_used'] == 1) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ezt a kupont már korábban felhasználták!"]);
        exit;
    }

    $client_id = $coupon['user_id'];


    $appStmt = $pdo->prepare("
        SELECT a.appointment_id, s.price 
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE a.user_id = ? 
          AND a.salon_id = ? 
          AND a.appointment_date = CURDATE() 
          AND a.status = 'booked'
        LIMIT 1
    ");
    $appStmt->execute([$client_id, $owner_salon_id]);
    $appointment = $appStmt->fetch();

    if (!$appointment) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "status" => "error", 
            "message" => "Ennek a vendégnek nincs mára lefoglalt, aktív időpontja az Ön szalonjában!"
        ]);
        exit;
    }

    $appointment_id = $appointment['appointment_id'];
    $original_price = $appointment['price'];

    $discount_percent = $coupon['discount_amount']; // 20
    $final_price = $original_price * (1 - ($discount_percent / 100));

    $updateAppStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'completed', final_price = ?, coupon_id = ? 
        WHERE appointment_id = ?
    ");
    $updateAppStmt->execute([$final_price, $coupon['coupon_id'], $appointment_id]);

    $updateCouponStmt = $pdo->prepare("UPDATE coupons SET is_used = 1, used_at = NOW() WHERE code = ?");
    $updateCouponStmt->execute([$coupon_code]);

    $points_to_give = 20;
    $updatePointsStmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE user_id = ?");
    $updatePointsStmt->execute([$points_to_give, $client_id]);

    $pointsCheckStmt = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
    $pointsCheckStmt->execute([$client_id]);
    $currentPoints = $pointsCheckStmt->fetchColumn();

    if ($currentPoints >= 100) {
        $newCode = strtoupper(bin2hex(random_bytes(4)));
        $insertCoupon = $pdo->prepare("INSERT INTO coupons (user_id, code, discount_amount, discount_type, is_used) VALUES (?, ?, 20, 'percent', 0)");
        $insertCoupon->execute([$client_id, $newCode]);
        
        $resetPoints = $pdo->prepare("UPDATE users SET total_points = total_points - 100 WHERE user_id = ?");
        $resetPoints->execute([$client_id]);
    }

    $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $userStmt->execute([$client_id]);
    $client = $userStmt->fetch();

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Kupon sikeresen érvényesítve, a foglalás lezárva 20% kedvezménnyel!",
        "username" => $client['username'] ?? 'Vendég',
        "discount" => $discount_percent
    ]);

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Szerver hiba történt!", "details" => $e->getMessage()]);
}
exit;