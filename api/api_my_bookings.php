<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Csak GET metodus engedelyezett!"
    ]);
    exit;
}

// A user_id-t GET paraméterként várjuk: api_my_bookings.php?user_id=5
$user_id = $_GET['user_id'] ?? null;

if ($user_id) {
    try {
        // Összekapcsoljuk az appointments, salons és services táblákat
        $sql = "SELECT 
                    a.appointment_id, 
                    a.appointment_date, 
                    a.appointment_time, 
                    a.status,
                    s.name as salon_name, 
                    s.address as salon_address,
                    ser.name as service_name,
                    ser.price
                FROM appointments a
                JOIN salons s ON a.salon_id = s.salon_id
                JOIN services ser ON a.service_id = ser.service_id
                WHERE a.user_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($bookings);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó user_id!"]);
}