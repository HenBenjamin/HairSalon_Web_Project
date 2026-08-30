<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../config.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 1. Pre-flight OPTIONS kérés kezelése
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak GET metodus engedelyezett!"]);
    exit;
}

// 2. Token beolvasása a fejlécből
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed az adatok megtekintéséhez! Be kell jelentkezned."]);
    exit;
}

$jwt = $matches[1];

try {
    // 3. Token dekódolása a config.php-ban megadott konstansokkal
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    
    // Kiszedjük a tokenből a bejelentkezett felhasználó adatait
    $loggedInUserId = $decoded->user->id;
    $role = $decoded->user->role ?? 'user';

    // Biztonsági ellenőrzés
    if ($role !== 'owner') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Ehhez a felülethez szalon tulajdonosnak kell lenned!"]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

try {
    // 4. Megkeressük a tulajdonos szalonjának az ID-ját
    $salonStmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ?");
    $salonStmt->execute([$loggedInUserId]);
    $salon = $salonStmt->fetch(PDO::FETCH_ASSOC);

    if (!$salon) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Nem található szalon ehhez a felhasználóhoz."]);
        exit;
    }

    $salon_id = $salon['salon_id'];

    // 5. Lekérjük a szalon összes lezárt (completed) vagy lemondott (cancelled) foglalását
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id, 
            a.appointment_date, 
            a.appointment_time, 
            a.status,
            u.username AS guest_name,
            u.email AS guest_email,
            ser.name AS service_name,
            ser.price
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN services ser ON a.service_id = ser.service_id
        WHERE a.salon_id = ? AND a.status IN ('completed', 'cancelled')
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    
    $stmt->execute([$salon_id]);
    $archive = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tömb visszaküldése JSON formátumban
    http_response_code(200);
    echo json_encode(["status" => "success", "data" => $archive]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt: " . $e->getMessage()]);
}