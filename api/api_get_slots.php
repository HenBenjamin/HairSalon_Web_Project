<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$salonId = $_GET['salon_id'] ?? null;
$date = $_GET['date'] ?? null;
$serviceId = $_GET['service_id'] ?? null;
$currentAppointmentTime = $_GET['current_appointment_time'] ?? null; 

if (!$salonId || !$date || !$serviceId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok (salon_id, date vagy service_id)!"]);
    exit;
}

try {
    $dayNumber = (int)date('N', strtotime($date));

    // 1. Nyitvatartás lekérése
    $workStmt = $pdo->prepare("SELECT start_time, end_time, is_closed FROM working_hours WHERE salon_id = ? AND day_of_week = ? LIMIT 1");
    $workStmt->execute([$salonId, $dayNumber]);
    $workingHours = $workStmt->fetch(PDO::FETCH_ASSOC);

    if (!$workingHours || $workingHours['is_closed'] == 1) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "A szalon ezen a napon zárva tart!",
            "slots" => []
        ]);
        exit;
    }

    // 2.lekérjük a kiválasztott új szolgáltatás időtartamát
    $serviceStmt = $pdo->prepare("SELECT duration FROM services WHERE service_id = ? LIMIT 1");
    $serviceStmt->execute([$serviceId]);
    $service = $serviceStmt->fetch(PDO::FETCH_ASSOC);
    $newServiceDuration = $service['duration'] ?? 30; // Alapértelmezett 30 perc, ha nincs meg
    $requiredBlocks = ceil($newServiceDuration / 30); // Hány darab 30 perces blokk kell az új foglalásnak

    // Lekérjük a már meglévő foglalásokat ÉS azok hosszát is
    $stmt = $pdo->prepare("
        SELECT a.appointment_time, s.duration 
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        WHERE a.salon_id = ? AND a.appointment_date = ? AND a.status != 'cancelled'
    ");
    $stmt->execute([$salonId, $date]);
    $rawBookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // szerkesztéshez: formázzuk meg a szerkesztendő időpontot (hh:mm:ss)
    $formattedCurrentTime = null;
    if ($currentAppointmentTime) {
        $formattedCurrentTime = date('H:i:s', strtotime($currentAppointmentTime));
    }

    // Összegyűjtjük az összes lefoglalt 30 perces részblokkot egy tömbbe
    $takenBlocks = [];
    foreach ($rawBookedSlots as $booking) {
        //Ha ezt a foglalást szerkeszti épp a felhasználó, 
        // akkor a teljes korábbi idősávját átugorjuk, így az szabadnak fog látszódni neki
        if ($formattedCurrentTime && date('H:i:s', strtotime($booking['appointment_time'])) === $formattedCurrentTime) {
            continue; 
        }

        $bStart = strtotime($booking['appointment_time']);
        $bDuration = (int)$booking['duration'];
        $bBlocks = ceil($bDuration / 30);

        for ($i = 0; $i < $bBlocks; $i++) {
            $takenBlocks[] = date("H:i:s", $bStart + ($i * 30 * 60));
        }
    }

    // 4. Idősávok generálása és ellenőrzése
    $start = strtotime($workingHours['start_time']);
    $end = strtotime($workingHours['end_time']);

    $slots = [];
    
    // Addig megyünk, amíg legalább egy 30 perces blokk befér a záróráig
    while ($start + (30 * 60) <= $end) {
        $timeStr = date('H:i', $start);
        
        $isBooked = false;

        // Megnézzük, hogy az aktuális ponttól kezdve befér-e a kívánt szolgáltatás összes blokkja
        for ($j = 0; $j < $requiredBlocks; $j++) {
            $checkTime = $start + ($j * 30 * 60);
            $checkTimeStr = date("H:i:s", $checkTime);

            // Ha túlcsúszik a szalon záróráján, vagy a blokk benne van a foglaltak között
            if (($checkTime + (30 * 60) > $end) || in_array($checkTimeStr, $takenBlocks)) {
                $isBooked = true;
                break;
            }
        }

        $slots[] = [
            "time" => $timeStr,
            "is_booked" => $isBooked
        ];

        // Mindig 30 perccel lépünk tovább a felület miatt
        $start += 30 * 60;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "slots" => $slots
    ]);
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}