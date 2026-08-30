<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once "../vendor/autoload.php";
require_once "../classes/AppointmentManager.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST metódus engedélyezett!"]);
    exit;
}

// 1. JWT TOKEN ELLENŐRZÉSE
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['X-Authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Nincs engedélyed a módosításra!"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALG));
    $loggedInUserId = $decoded->user->id; 
    $role = $decoded->user->role ?? 'user'; //Kimentjük a szerepkört (owner vagy user)
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"]);
    exit;
}

// 2. input adatok beolvasása
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['appointment_id'], $input['date'], $input['time'])) {
    
    $appointment_id = $input['appointment_id'];
    $new_date = $input['date']; // YYYY-MM-DD
    $new_time = $input['time']; // HH:MM:SS
    $normalized_new_time = date("H:i", strtotime($new_time));
    
    try {
        //Lekérjük a foglalást alapból, hogy megnézzük a részleteit
        $appStmt = $pdo->prepare("SELECT salon_id, service_id, user_id, appointment_date, appointment_time FROM appointments WHERE appointment_id = ? AND status != 'cancelled'");
        $appStmt->execute([$appointment_id]);
        $currentApp = $appStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentApp) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "A foglalás nem található!"]);
            exit;
        }

        //jogosultság ellenőrzése szerepkör alapján
        if ($role === 'owner') {
            // Ha tulajdonos, ellenőrizzük, hogy az ő szalonjához tartozik-e a foglalás
            $salonCheck = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ? AND salon_id = ? LIMIT 1");
            $salonCheck->execute([$loggedInUserId, $currentApp['salon_id']]);
            if (!$salonCheck->fetch()) {
                http_response_code(403);
                echo json_encode(["status" => "error", "message" => "Nincs jogod a szalon foglalásait módosítani!"]);
                exit;
            }
        } else {
            // User esetén az időpont módosítása teljesen le van tiltva!
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "A vendégek számára az időpont módosítása nem engedélyezett!"]);
            exit;
        }

        $salon_id = $currentApp['salon_id'];
        $service_id = $currentApp['service_id'];
        $old_date = $currentApp['appointment_date'];
        $old_time = $currentApp['appointment_time'];

        // validáció az appointment managerrel
        $appointmentManager = new AppointmentManager($pdo);
        
        // 1: Hétfő, 7: Vasárnap -> working_hours tablahoz valo illeszkedes
        $dayNumber = (int)date('N', strtotime($new_date));
        $workingHours = $appointmentManager->getWorkingHours($salon_id, $dayNumber);

        if (!$workingHours || $workingHours['is_closed'] == 1) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Ezen a napon a szalon zárva tart!"]);
            exit;
        }

        $duration = $appointmentManager->getServiceDuration($service_id);
        $booked_data = $appointmentManager->getBookedSlots($salon_id, $new_date);

        // szerkesztés logika: ha ugyanarra a napra teszi át a foglalást, akkor a saját 
        // régi foglalásának blokkjait ki kell vennünk a foglaltak listájából, különben önmagával ütközne!
        if ($new_date === $old_date) {
            $formattedOldTime = date("H:i:s", strtotime($old_time));
            $booked_data = array_filter($booked_data, function($booking) use ($formattedOldTime) {
                return date("H:i:s", strtotime($booking['appointment_time'])) !== $formattedOldTime;
            });
        }

        // Legeneráljuk az elérhető helyeket az új adatok alapján
        $available_slots = $appointmentManager->generateAvailableSlots($workingHours, $duration, $booked_data, $new_date);

        // Ellenőrizzük, hogy a választott új időpont szabad-e
        $is_slot_free = false;
        foreach ($available_slots as $slot) {
            if ($slot['time'] === $normalized_new_time && !$slot['booked']) {
                $is_slot_free = true;
                break;
            }
        }

        if (!$is_slot_free) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "A kiválasztott új időpont már foglalt, vagy nem fér be záróráig!"]);
            exit;
        }

        // 3. frissítés ha minden rendben van
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET appointment_date = ?, appointment_time = ? 
            WHERE appointment_id = ?
        ");
        
        $success = $stmt->execute([$new_date, $new_time, $appointment_id]);
        
        if ($success) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Sikeresen módosítva!"]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Nem sikerült frissíteni az adatbázist."]);
            exit;
        }
        
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()]);
        exit;
    }

} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hiányzó adatok a módosításhoz!"]);
    exit;
}
?>