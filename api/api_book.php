<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "../config.php";

function sendSmsNotification($messageText) {
    $client = new \Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
    $client->messages->create(MY_PHONE, [
        'from' => TWILIO_PHONE,
        'body' => $messageText
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST metodus engedelyezett!"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['user_id'], $input['salon_id'], $input['service_id'], $input['date'], $input['time'])) {

    $salon_id = $input['salon_id'];
    $date = $input['date'];
    $time = $input['time'];

    $dayNumber = (int)date('N', strtotime($date));

    // 1. Nyitvatartás lekérése
    $workStmt = $pdo->prepare("SELECT start_time, end_time, is_closed FROM working_hours WHERE salon_id = ? AND day_of_week = ?");
    $workStmt->execute([$salon_id, $dayNumber]);
    $workingHours = $workStmt->fetch();

    if (!$workingHours || $workingHours['is_closed'] == 1) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ezen a napon a szalon zárva tart!"]);
        exit;
    }

    // 2. IDŐPONT ELLENŐRZÉSE (Zárás előtt max 30 perccel)
    $bookingTimestamp = strtotime($time);
    $startTimestamp = strtotime($workingHours['start_time']);
    $endTimestamp = strtotime($workingHours['end_time']);

    // Az utolsó foglalható időpont 30 perccel a zárás előtt van
    $lastPossibleSlot = $endTimestamp - (30 * 60);


    if($bookingTimestamp < $startTimestamp){
        $minTime = date('H:i', $startTimestamp);
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "A szalon ekkor még nem nyit! Első időpont: $minTime"
        ]);
        exit;
    }

    if ($bookingTimestamp > $lastPossibleSlot) {
        $maxTime = date('H:i', $lastPossibleSlot);
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "A szalon ekkor már nem fogad vendéget! Utolsó időpont: $maxTime"
        ]);
        exit;
    }

    // 3. ÜTKÖZÉS ELLENŐRZÉSE (Már foglalt-e?)
    // Biztosítjuk, hogy az időpont formátuma megegyezzen az adatbáziséval
    $dbTimeFormat = date('H:i:00', $bookingTimestamp);
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE salon_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
    $checkStmt->execute([$salon_id, $date, $dbTimeFormat]);

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Sajnos ez az időpont már foglalt!"]);
        exit;
    }

    // 4. MENTÉS
    $stmt = $pdo->prepare("INSERT INTO appointments (user_id, salon_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'booked')");

    if ($stmt->execute([$input['user_id'], $input['salon_id'], $input['service_id'], $input['date'], $dbTimeFormat])) {
        try {
            $message = "Sikeres foglalás! Időpont: " . $input['date'] . " " . $input['time'] . ". Várunk szeretettel!";
            sendSmsNotification($message);
        } catch (Exception $e) {
            // Itt ne állítsuk le a folyamatot exit-tel, ha a foglalás már sikerült!
            // Csak logoljuk, vagy küldjünk visszajelzést a sikeres foglalás mellé.
        }

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Sikeres foglalás!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok!"]);
}