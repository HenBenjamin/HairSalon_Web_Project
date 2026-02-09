<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Csak DELETE metodus engedelyezett!"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['appointment_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        if ($stmt->execute([$id])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Foglalás törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Adatbázis hiba."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Hiányzó azonosító."]);
    }
    exit;
}