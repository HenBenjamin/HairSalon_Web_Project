<?php
session_start();
require_once "config.php";

// Csak a tulajdonos férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$appointment_id = $_GET['id'] ?? null;

if (!$appointment_id) {
    header("Location: owner_appointments.php");
    exit;
}

// 1. Ellenőrizzük a foglalást alapadatait
$stmt = $pdo->prepare("
    SELECT a.*, u.username, s.name AS service_name, sa.name AS salon_name 
    FROM appointments a
    JOIN salons sa ON a.salon_id = sa.salon_id
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_id = ? AND sa.owner_id = ?
");
$stmt->execute([$appointment_id, $owner_id]);
$app = $stmt->fetch();

if (!$app) {
    die("Hiba: A foglalás nem található, vagy nincs jogosultsága módosítani!");
}

$salon_id = $app['salon_id'];
$selected_date = $_GET['date'] ?? $app['appointment_date'];

// 2.Meghatározzuk a hét napját a kiválasztott dátumból (1 = Hétfő, 7 = Vasárnap)
$day_num = date('N', strtotime($selected_date));

// 3.Lekérjük a szalon nyitvatartását a working_hours táblából az adott napra
$stmt = $pdo->prepare("
    SELECT start_time, end_time, is_closed 
    FROM working_hours 
    WHERE salon_id = ? AND day_of_week = ?
");
$stmt->execute([$salon_id, $day_num]);
$hours = $stmt->fetch();

// Alapértelmezett idősávok
$db_start = '08:00:00';
$db_end = '16:00:00';
$is_closed = false;

if ($hours) {
    $db_start = $hours['start_time'];
    $db_end = $hours['end_time'];
    $is_closed = (bool)$hours['is_closed'];
}

// 4. Szolgáltatások lekérése
$stmt = $pdo->prepare("SELECT service_id, name FROM services WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$services = $stmt->fetchAll();

// 5. Már foglalt időpontok lekérése az adott napra a szalonban
$stmt = $pdo->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE salon_id = ? 
      AND appointment_date = ? 
      AND status = 'booked' 
      AND appointment_id != ?
");
$stmt->execute([$salon_id, $selected_date, $appointment_id]);
$booked_times = $stmt->fetchAll(PDO::FETCH_COLUMN);

$booked_slots = array_map(function($t) {
    return date('H:i', strtotime($t));
}, $booked_times);

// 6. Módosítás mentése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_service = $_POST['service_id'];
    $new_date = $_POST['date'];
    $new_time = $_POST['time'];
    $new_status = $_POST['status'];

    // Ha aznap zárva van a szalon, biztonsági ellenőrzésképp megakadályozzuk a mentést (kivéve ha lemondja/teljesíti)
    if ($is_closed && $new_status === 'booked') {
        $error = "Ezen a napon a szalon zárva tart!";
    } else {
        $update = $pdo->prepare("
            UPDATE appointments 
            SET service_id = ?, appointment_date = ?, appointment_time = ?, status = ? 
            WHERE appointment_id = ?
        ");
        $update->execute([$new_service, $new_date, $new_time, $new_status, $appointment_id]);

        header("Location: owner_appointments.php?msg=updated");
        exit;
    }
}

// 7.Idősávok generálása a nyitvatartás és a foglalások alapján
$time_slots = [];
if (!$is_closed) {
    $start_time = strtotime($db_start);
    $end_time = strtotime($db_end);

    while ($start_time < $end_time) {
        $time_slots[] = date('H:i', $start_time);
        $start_time = strtotime('+30 minutes', $start_time);
    }
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Foglalás szerkesztése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger shadow-sm mb-3"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card shadow border-0" style="border-radius: 12px变量">
                <div class="card-header bg-warning text-dark fw-bold py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    Foglalás módosítása: <?php echo htmlspecialchars($app['username']); ?>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Vendég neve</label>
                            <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($app['username']); ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Szolgáltatás</label>
                            <select name="service_id" class="form-select" required>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?php echo $s['service_id']; ?>" <?php echo ($s['service_id'] == $app['service_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Dátum</label>
                                <input type="date" name="date" id="appointment_date" class="form-control" value="<?php echo $selected_date; ?>" required onchange="window.location.search = '?id=<?php echo $appointment_id; ?>&date=' + this.value;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">
                                    Időpont 
                                    <span class="text-muted small">
                                        <?php if ($is_closed): ?>
                                            (ZÁRVA)
                                        <?php endif; ?>
                                    </span>
                                </label>
                                
                                <select name="time" class="form-select" required <?php echo $is_closed ? 'disabled' : ''; ?>>
                                    <?php if ($is_closed): ?>
                                        <option value="">Ezen a napon a szalon zárva</option>
                                    <?php else: ?>
                                        <option value="">-- Válassz idősávot --</option>
                                        <?php 
                                        $current_app_time = date('H:i', strtotime($app['appointment_time']));
                                        
                                        foreach ($time_slots as $slot): 
                                            $is_booked = in_array($slot, $booked_slots);
                                            // Kiválasztott állapot kezelése
                                            $is_selected = ($slot === $current_app_time && $selected_date === $app['appointment_date']);
                                            
                                            // HA foglalt ÉS nem ez a jelenlegi foglalás, akkor tiltjuk!
                                            $disabled = ($is_booked && !$is_selected) ? 'disabled' : '';
                                            $label_suffix = ($is_booked && !$is_selected) ? ' (Foglalt)' : '';
                                        ?>
                                            <option value="<?php echo $slot; ?>" <?php echo $is_selected ? 'selected' : ''; ?> <?php echo $disabled; ?>>
                                                <?php echo $slot . $label_suffix; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Állapot</label>
                            <select name="status" class="form-select">
                                <option value="booked" <?php echo ($app['status'] == 'booked') ? 'selected' : ''; ?>>Lefoglalva (booked)</option>
                                <option value="completed" <?php echo ($app['status'] == 'completed') ? 'selected' : ''; ?>>Teljesítve (completed)</option>
                                <option value="cancelled" <?php echo ($app['status'] == 'cancelled') ? 'selected' : ''; ?>>Lemondva (cancelled)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg" <?php echo ($is_closed && $app['status'] === 'booked') ? 'disabled' : ''; ?>>Módosítások mentése</button>
                            <a href="owner_appointments.php" class="btn btn-outline-secondary">Mégse</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>