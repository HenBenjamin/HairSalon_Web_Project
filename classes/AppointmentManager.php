<?php
class AppointmentManager {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getWorkingHours(int $salon_id, int $day_of_week) {
        $stmt = $this->pdo->prepare("SELECT * FROM working_hours WHERE salon_id = ? AND day_of_week = ?");
        $stmt->execute([$salon_id, $day_of_week]);
        return $stmt->fetch();
    }

    public function getBookedSlots(int $salon_id, string $date): array {
        // Osszekotjuk a services tablaval, hogy tudjuk, melyik korabbi foglalas hany percet vesz el
        $stmt = $this->pdo->prepare("
            SELECT a.appointment_time, s.duration 
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            WHERE a.salon_id = ? AND a.appointment_date = ? AND a.status != 'cancelled'
        ");
        $stmt->execute([$salon_id, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    /**
     * Lekerjuk egy adott szolgaltatas idotartamat
     */
    public function getServiceDuration(int $service_id): int {
        $stmt = $this->pdo->prepare("SELECT duration FROM services WHERE service_id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        return $service['duration'] ?? 30;
    }

    /**
     *Legeneraljuk az elerheto idosavokat, kiszurjuk a multbelieket es a foglaltakat
     */
    public function generateAvailableSlots(array $hours, int $duration, array $booked_data, string $date): array {
        $all_slots = [];
        $start = strtotime($hours['start_time']);
        $end = strtotime($hours['end_time']);
        $now = time();

        // Kigyűjtsük az összes foglalt időpontot
        // Ha egy régi foglalás 60 perces, akkor az 2 darab 30 perces blokkot foglal el
        $taken_blocks = [];
        foreach ($booked_data as $booking) {
            $b_start = strtotime($booking['appointment_time']);
            $b_duration = (int)$booking['duration'];
            $b_blocks = ceil($b_duration / 30); // Hány 30 perces blokk kell neki

            for ($i = 0; $i < $b_blocks; $i++) {
                $taken_blocks[] = date("H:i:s", $b_start + ($i * 30 * 60));
            }
        }

        // Hány darab egymás utáni szabad 30 perces blokkra van szüksége az új szolgáltatásnak
        $required_blocks = ceil($duration / 30);

        // Végigmegyünk a napon 30 perces lépésekkel a nyitvatartáson belül
        while ($start + (30 * 60) <= $end) {
            $current_slot_time = date("H:i", $start);
            $db_format = $current_slot_time . ":00";
            $slot_timestamp = strtotime($date . ' ' . $current_slot_time);

            // Múltbeli időpontok kiszűrése
            if ($slot_timestamp < $now) {
                $start += 30 * 60;
                continue;
            }

            $is_booked = false;

            // Megnézzük, hogy az aktuális időponttól kezdve elfér-e az új szolgáltatás
            // Ha 60 perces, akkor ellenőrizzük az aktuális slotot es a következőt is
            for ($j = 0; $j < $required_blocks; $j++) {
                $check_time = $start + ($j * 30 * 60);
                $check_time_str = date("H:i:s", $check_time);

                // Túlnyúlik-e a szolgáltatás a szalon záróráján
                // Benne van-e ez a részblokk a már lefoglalt blokkok között ($taken_blocks)
                if (($check_time + (30 * 60) > $end) || in_array($check_time_str, $taken_blocks)) {
                    $is_booked = true;
                    break;
                }
            }

            $all_slots[] = [
                'time' => $current_slot_time,
                'booked' => $is_booked
            ];

            $start += 30 * 60;
        }

        return $all_slots;
    }
}