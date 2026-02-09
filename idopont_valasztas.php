<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$salon_id = $_GET['salon_id'] ?? null;
$service_id = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$salon_id || !$service_id || !$date) {
    header("Location: index.php");
    exit;
}

$day_of_week = date('N', strtotime($date));

// 1. Nyitvatartás lekérése
$stmt = $pdo->prepare("SELECT * FROM working_hours WHERE salon_id = ? AND day_of_week = ?");
$stmt->execute([$salon_id, $day_of_week]);
$hours = $stmt->fetch();

// Változó a zárva tartás állapotának tárolására
$is_closed = (!$hours || $hours['is_closed']);

$all_slots = [];
$duration = 30;

if (!$is_closed) {
    // 2. Szolgáltatás időtartamának lekérése
    $stmt = $pdo->prepare("SELECT duration FROM services WHERE service_id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
    $duration = $service['duration'] ?? 30;

    // 3. Foglalt időpontok lekérése
    $stmt = $pdo->prepare("SELECT appointment_time, appointment_id FROM appointments WHERE salon_id = ? AND appointment_date = ? AND status != 'cancelled'");
    $stmt->execute([$salon_id, $date]);
    $booked_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Idősávok generálása
    $start = strtotime($hours['start_time']);
    $end = strtotime($hours['end_time']);

    while ($start + ($duration * 60) <= $end) {
        $current_slot = date("H:i", $start);
        $db_format = $current_slot . ":00";

        $is_booked = isset($booked_data[$db_format]);

        $all_slots[] = [
            'time' => $current_slot,
            'booked' => $is_booked,
            'appointment_id' => $is_booked ? $booked_data[$db_format] : null
        ];

        $start += 30 * 60;
    }
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szabad időpontok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .btn-check:checked + .btn-outline-success {
            background-color: #198754;
            color: white;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.5);
        }
        .slot-box {
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: all 0.2s ease;
        }
        .slot-box:hover {
            transform: translateY(-2px);
        }
        .closed-container {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mt-5">

    <?php if ($is_closed): ?>
        <div class="closed-container">
            <div class="card shadow border-0 p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-calendar-x text-danger" style="font-size: 5rem;"></i>
                </div>
                <h3 class="fw-bold">Sajnáljuk, a szalon zárva!</h3>
                <p class="text-muted">Ezen a napon (<strong><?php echo htmlspecialchars($date); ?></strong>) nem fogadunk vendégeket.</p>
                <hr>
                <div class="mt-4">
                    <a href="foglalas.php?salon_id=<?php echo $salon_id; ?>" class="btn btn-primary btn-lg w-100 shadow-sm">
                        <i class="bi bi-arrow-left"></i> Másik dátum választása
                    </a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <?php if (isset($_GET['waitlist_msg'])): ?>
            <div class="alert alert-info alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <?php
                if($_GET['waitlist_msg'] == 'success') echo "Sikeresen feliratkoztál a várólistára!";
                if($_GET['waitlist_msg'] == 'already_subscribed') echo "Már szerepelszel ezen a várólistán.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow p-4 border-0">
            <h4 class="mb-3">Időpontok kiválasztása: <?php echo htmlspecialchars($date); ?></h4>
            <div class="d-flex gap-3 mb-4 flex-wrap">
                <span class="badge bg-success">Szabad</span>
                <span class="badge bg-danger">Foglalt (Várólista)</span>
                <span class="text-muted ms-auto">
                    <i class="bi bi-clock me-1"></i> Időtartam: <?php echo $duration; ?> perc
                </span>
            </div>

            <form action="foglalas_mentese.php" method="POST">
                <input type="hidden" name="salon_id" value="<?php echo $salon_id; ?>">
                <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">

                <div class="row g-3">
                    <?php if (empty($all_slots)): ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">Nincs elérhető időpont erre a napra a megadott nyitvatartás alapján.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($all_slots as $slot): ?>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <?php if ($slot['booked']): ?>
                                    <a href="varolista_csatlakozas.php?id=<?php echo $slot['appointment_id']; ?>&service_id=<?php echo $service_id; ?>"
                                       class="btn btn-danger w-100 slot-box shadow-sm text-decoration-none"
                                       title="Kattints a várólistához!">
                                        <span class="fw-bold"><?php echo $slot['time']; ?></span>
                                        <small style="font-size: 0.6rem; text-transform: uppercase;">Várólista</small>
                                    </a>
                                <?php else: ?>
                                    <input type="radio" class="btn-check" name="time" id="t-<?php echo $slot['time']; ?>" value="<?php echo $slot['time']; ?>" required>
                                    <label class="btn btn-outline-success w-100 slot-box fw-bold shadow-sm" for="t-<?php echo $slot['time']; ?>">
                                        <?php echo $slot['time']; ?>
                                    </label>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-5 border-top pt-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 shadow-sm" <?php echo empty($all_slots) ? 'disabled' : ''; ?>>
                        <i class="bi bi-calendar-check me-2"></i> Foglalás megerősítése
                    </button>

                    <a href="foglalas.php?salon_id=<?php echo $salon_id; ?>" class="btn btn-link w-100 text-decoration-none text-muted text-center">
                        <i class="bi bi-arrow-left"></i> Vissza a dátumválasztáshoz
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>