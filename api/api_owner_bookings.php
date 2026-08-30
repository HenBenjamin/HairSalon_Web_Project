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

// 1. JWT Token ellenőrzése a fejlécből
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs jogosultságod, hiányzó token!"]);
    exit;
}

$jwt = $matches[1];

try {
    // Token dekódolása
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    $owner_id = $decoded->user->id;
    $role = $decoded->user->role;

    // Biztonsági ellenőrzés: Biztosan tulajdonos kéri le?
    if ($role !== 'owner') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Ehhez a felülethez szalon tulajdonosnak kell lenned!"]);
        exit;
    }

    // 2. Megkeressük a tulajdonoshoz tartozó szalont
    $salonStmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ? LIMIT 1");
    $salonStmt->execute([$owner_id]);
    $salon = $salonStmt->fetch();

    if (!$salon) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Ehhez a felhasználóhoz nincs szalon regisztrálva!"]);
        exit;
    }

    $salon_id = $salon['salon_id'];

    // 3. Lekérjük a szalonhoz tartozó összes foglalást a vendég nevével és a szolgáltatással együtt
    // A státuszt is lekérjük, hogy tudjuk szűrni, ami már completed
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            a.salon_id,
            u.username AS client_name,
            s.name AS service_name
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN services s ON a.service_id = s.service_id
        WHERE a.salon_id = ? AND a.status = 'booked'
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute([$salon_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "salon_id" => $salon_id,
        "appointments" => $appointments
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!", "details" => $e->getMessage()]);
}