<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak PUT metodus engedelyezett!"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id = $input['appointment_id'] ?? null;
$newDate = $input['appointment_date'] ?? null;
$newTime = $input['appointment_time'] ?? null;

if ($id && $newDate && $newTime) {
    try {
        // 1. Meglévő foglalás adatainak lekérése (időpont és szalon ID egyszerre)
        $currentQuery = $pdo->prepare("SELECT appointment_date, appointment_time, salon_id FROM appointments WHERE appointment_id = ?");
        $currentQuery->execute([$id]);
        $currentAppt = $currentQuery->fetch();

        if (!$currentAppt) {
            echo json_encode(["status" => "error", "message" => "A foglalás nem található."]);
            exit;
        }

        // 2. ELLENŐRZÉS: A jelenlegi (régi) időpont már elmult-e?
        // Ha már elmult, nem engedjük módosítani.
        if (strtotime($currentAppt['appointment_date'] . ' ' . $currentAppt['appointment_time']) < time()) {
            echo json_encode(["status" => "error", "message" => "Múltbeli vagy éppen zajló időpont nem módosítható!"]);
            exit;
        }

        // 3. ELLENŐRZÉS: Az ÚJ időpont a jövőben van-e?
        if (strtotime($newDate . ' ' . $newTime) < time()) {
            echo json_encode(["status" => "error", "message" => "Nem módosíthatod az időpontot a múltba!"]);
            exit;
        }

        $salon_id = $currentAppt['salon_id'];

        // 4. Nyitvatartás ellenőrzése az új napra
        $dayNumber = (int)date('N', strtotime($newDate));
        $workStmt = $pdo->prepare("SELECT start_time, end_time, is_closed FROM working_hours WHERE salon_id = ? AND day_of_week = ?");
        $workStmt->execute([$salon_id, $dayNumber]);
        $workingHours = $workStmt->fetch();

        if (!$workingHours || $workingHours['is_closed'] == 1) {
            echo json_encode(["status" => "error", "message" => "A szalon ezen a napon zárva tart!"]);
            exit;
        }

        // 5. Időintervallum ellenőrzése (zárás előtt max 30 perccel)
        $bookingTimestamp = strtotime($newTime);
        $startTimestamp = strtotime($workingHours['start_time']);
        $endTimestamp = strtotime($workingHours['end_time']);
        $lastPossibleSlot = $endTimestamp - (30 * 60);

        if ($bookingTimestamp < $startTimestamp || $bookingTimestamp > $lastPossibleSlot) {
            $maxTime = date('H:i', $lastPossibleSlot);
            echo json_encode(["status" => "error", "message" => "A választott időpont kívül esik a nyitvatartáson! Utolsó foglalható: $maxTime"]);
            exit;
        }

        // 6. Ütközés ellenőrzése (MÁSIK foglalással)
        $dbTimeFormat = date('H:i:00', $bookingTimestamp);
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE salon_id = ? AND appointment_date = ? AND appointment_time = ? AND appointment_id != ? AND status != 'cancelled'");
        $checkStmt->execute([$salon_id, $newDate, $dbTimeFormat, $id]);

        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(["status" => "error", "message" => "Sajnos ez az időpont már foglalt!"]);
            exit;
        }

        // 7. Frissítés
        $stmt = $pdo->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE appointment_id = ?");
        if ($stmt->execute([$newDate, $dbTimeFormat, $id])) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Időpont sikeresen módosítva!"]);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok!"]);
}