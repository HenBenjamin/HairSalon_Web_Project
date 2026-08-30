<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
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

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Hiányzó vagy hibás Authorization token!"]);
    exit;
}

$jwtToken = $matches[1];

try {
    $decoded = JWT::decode($jwtToken, new Key(JWT_SECRET, JWT_ALG));
    
    $userId = $decoded->user->id;
    $userRole = $decoded->user->role;

    if ($userRole !== 'owner') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Nincs jogosultságod a szalon galéria megtekintéséhez!"]);
        exit;
    }

    $salonStmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ? LIMIT 1");
    $salonStmt->execute([$userId]);
    $salonRow = $salonStmt->fetch();

    if (!$salonRow || empty($salonRow['salon_id'])) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Ehhez a tulajdonoshoz nem található szalon a rendszerben!"]);
        exit;
    }

    $salon_id = $salonRow['salon_id'];

    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id, a.appointment_date, a.appointment_time, a.status,
            u.username AS client_name, u.mobilenumber AS client_phone,
            hr.style_name, hr.image_url AS style_image, hr.description AS style_desc
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN user_favored_styles ufs ON a.appointment_id = ufs.appointment_id
        JOIN hair_recommendations hr ON ufs.recommendation_id = hr.id
        WHERE a.salon_id = ? 
          AND a.status NOT IN ('completed', 'cancelled')
          AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute([$salon_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $appointments
    ]);
    exit;

} catch (\Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "A token lejárt, jelentkezz be újra!"]);
    exit;
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen token! Hozzáférés megtagadva."]);
    exit;
}