<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "../config.php";
require_once "../vendor/autoload.php";
require_once "../classes/AppointmentManager.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// function sendSmsNotification($message) {
//     try {
//         $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);

//         $client->messages->create(
//             MY_PHONE, 
//             [
//                 'from' => TWILIO_PHONE,
//                 'body' => $message
//             ]
//         );

//         return true;
//     } catch (\Exception $e) {
//         error_log("Twilio SMS hiba: " . $e->getMessage());
//         return false;
//     }
// }

function sendSmsNotification($messageText) {
    try {
        $client = new \Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
        $message = $client->messages->create(MY_PHONE, [
            'from' => TWILIO_PHONE,
            'body' => $messageText
        ]);
        return true;
    } catch (\Exception $e) {
        // Ez a sor megmondja, miért omlik össze a szerver
        die("TWILIO HIBA: " . $e->getMessage());
    }
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

//JWT TOKEN ELLENŐRZÉSE
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed a foglalásra! Be kell jelentkezned."]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    $loggedInUserId = $decoded->user->id;
    $loggedInUserRole = $decoded->user->role;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['salon_id'], $input['service_id'], $input['date'], $input['time'])) {

    $salon_id = $input['salon_id'];
    $service_id = $input['service_id'];
    $date = $input['date'];
    $time = $input['time'];

    // Normalizáljuk a kapott időpontot (HH:MM), hogy biztosan egyezzen a generálttal
    $normalized_time = date("H:i", strtotime($time));

    // 2. lépés: oop alapú menedzser példányosítása
    $appointmentManager = new AppointmentManager($pdo);
    $dayNumber = (int)date('N', strtotime($date));

    // Nyitvatartás lekérése objektummal
    $workingHours = $appointmentManager->getWorkingHours($salon_id, $dayNumber);

    if (!$workingHours || $workingHours['is_closed'] == 1) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ezen a napon a szalon zárva tart!"]);
        exit;
    }

    // 3. lépés: lekérjük a szolgáltatás hosszát és a meglévő foglalásokat
    $duration = $appointmentManager->getServiceDuration($service_id);
    $booked_data = $appointmentManager->getBookedSlots($salon_id, $date);

    // 4. lépés: Legeneráljuk a szabad slotokat az ÚJ szolgáltatás hossza alapján
    // Ez a metódus automatikusan kiszűri a múltbelieket, az ütközéseket és a záróra utáni túlcsúszásokat is!
    $available_slots = $appointmentManager->generateAvailableSlots($workingHours, $duration, $booked_data, $date);

    // Megnézzük, hogy a mobilról beküldött időpont szerepel-e a valójában szabad listában
    $is_slot_free = false;
    foreach ($available_slots as $slot) {
        if ($slot['time'] === $normalized_time && !$slot['booked']) {
            $is_slot_free = true;
            break;
        }
    }

    // Ha az időpont nem szabad (mert ütközik, vagy nem fér be záróráig)3
    if (!$is_slot_free) {
        http_response_code(409);
        echo json_encode([
            "status" => "error", 
            "message" => "Sajnos ez az időpont már foglalt, vagy a szolgáltatás nem fejezhető be záróráig!"
        ]);
        exit;
    }

    // 5. lépés: mentés
    $dbTimeFormat = $normalized_time . ":00";
    $stmt = $pdo->prepare("INSERT INTO appointments (user_id, salon_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'booked')");

    if ($stmt->execute([$loggedInUserId, $salon_id, $service_id, $date, $dbTimeFormat])) {
        
        try {
            $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ? LIMIT 1");
            $userStmt->execute([$loggedInUserId]);
            $userRow = $userStmt->fetch();
            
            $clientName = !empty($userRow['username']) ? $userRow['username'] : "Kedves Vendégünk";

            $message = "Szia " . $clientName . "! Sikeres foglalás! Időpont: " . $date . " " . $normalized_time . ". Várunk szeretettel!";
            
            sendSmsNotification($message);
        } catch (\Throwable $e) {
            error_log("SMS küldési hiba: " . $e->getMessage());
        }

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Sikeres foglalás!"]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba történt a mentés során."]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok!"]);
    exit;
}

// function sendSmsNotification($message) {
//     try {
//         $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);

//         $client->messages->create(
//             MY_PHONE, 
//             [
//                 'from' => TWILIO_PHONE,
//                 'body' => $message
//             ]
//         );

//         return true;
//     } catch (\Exception $e) {
//         error_log("Twilio SMS hiba: " . $e->getMessage());
//         return false;
//     }
// }