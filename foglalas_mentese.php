<?php
session_start();
require_once "config.php";
require_once "classes/AppointmentManager.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    //1. Biztonsági ellenőrzés: megérkezett-e minden szükséges adat?
    $salon_id = $_POST['salon_id'] ?? null;
    $service_id = $_POST['service_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;

    if (!$salon_id || !$service_id || !$date || !$time) {
        echo "<script>alert('Hiányzó foglalási adatok!'); window.location.href='index.php';</script>";
        exit;
    }

    // Normalizáljuk az időpont formátumát (csak óra:perc), hogy biztosan egyezzen a generálttal
    $normalized_time = date("H:i", strtotime($time));

    // 2. oop szerver oldali ellenorzes
    $appointmentManager = new AppointmentManager($pdo);

    // Lekérjük a napot, nyitvatartást, szolgáltatás hosszát és a foglalt időpontokat
    $day_of_week = date('N', strtotime($date));
    $hours = $appointmentManager->getWorkingHours($salon_id, $day_of_week);

    if (!$hours || $hours['is_closed']) {
        echo "<script>alert('Sajnáljuk, a szalon ezen a napon zárva van!'); window.location.href='index.php';</script>";
        exit;
    }

    $duration = $appointmentManager->getServiceDuration($service_id);
    $booked_data = $appointmentManager->getBookedSlots($salon_id, $date);

    // Legeneráljuk a szabad slotokat az uj szolgáltatás hossza alapján
    $available_slots = $appointmentManager->generateAvailableSlots($hours, $duration, $booked_data, $date);

    // Megnézzük, hogy a kiválasztott időpont szerepel-e a szabad helyek között
    $is_slot_free = false;
    foreach ($available_slots as $slot) {
        if ($slot['time'] === $normalized_time && !$slot['booked']) {
            $is_slot_free = true;
            break;
        }
    }

    //3. Ha az időpont időközben foglalt lett, vagy nem fér el záróráig
    if (!$is_slot_free) {
        echo "<script>alert('Sajnáljuk, ezt az időpontot időközben már lefoglalták, vagy a szolgáltatás nem fér el a szalon zárórájáig!'); window.location.href='index.php';</script>";
        exit;
    }

    //4. Ha a validáció sikeres, mentünk az adatbázisba
    $sql = "INSERT INTO appointments (user_id, salon_id, service_id, appointment_date, appointment_time, status) 
            VALUES (?, ?, ?, ?, ?, 'booked')";

    $stmt = $pdo->prepare($sql);
    
    // Az adatbázisba  mentjük el
    $db_time_format = $normalized_time . ":00";

    if ($stmt->execute([$user_id, $salon_id, $service_id, $date, $db_time_format])) {
        echo "<script>alert('Sikeres foglalás!'); window.location.href='index.php';</script>";
    } else {
        echo "Hiba történt a mentés során.";
    }
} else {
    header("Location: login.php");
    exit;
}
?>