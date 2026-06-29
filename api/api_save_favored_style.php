<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST kérések engedélyezettek."]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "A funkció használatához be kell jelentkezned!"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$recommendationId = $input['recommendation_id'] ?? null;
$appointmentId = $input['appointment_id'] ?? null;

if (!$recommendationId || !$appointmentId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó frizura vagy időpont azonosító!"]);
    exit;
}

try {
    $checkStmt = $pdo->prepare("
        SELECT appointment_id 
        FROM appointments 
        WHERE appointment_id = ? AND user_id = ? AND status = 'booked'
    ");
    $checkStmt->execute([$appointmentId, $userId]);
    $isValidAppointment = $checkStmt->fetch();

    if (!$isValidAppointment) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt időpont foglalás!"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO user_favored_styles (user_id, appointment_id, recommendation_id) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$userId, $appointmentId, $recommendationId]);

    echo json_encode(["status" => "success", "message" => "Stílus sikeresen hozzárendelve az időpontodhoz!"]);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()]);
    exit;
}