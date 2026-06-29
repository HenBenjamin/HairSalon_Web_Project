<?php
session_start();
require_once "config.php";
require_once "classes/AppointmentManager.php";

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

$appointmentManager = new AppointmentManager($pdo);

// 1. Nyitvatartás lekérése objektummal
$hours = $appointmentManager->getWorkingHours($salon_id, $day_of_week);
$is_closed = (!$hours || $hours['is_closed']);

$all_slots = [];
$duration = 30;

if (!$is_closed) {
    // 2. Szolgáltatás időtartama objektummal
    $duration = $appointmentManager->getServiceDuration($service_id);

    // 3. Foglalt időpontok lekérése objektummal
    $booked_data = $appointmentManager->getBookedSlots($salon_id, $date);

    // 4. Idősávok generálása az objektum logikájával
    $all_slots = $appointmentManager->generateAvailableSlots($hours, $duration, $booked_data, $date);
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

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
                <span class="badge bg-danger">Foglalt</span>
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

                        <!-- // Slotok generalasa, varolistaval egyutt -->
                        <!-- <?php foreach ($all_slots as $slot): ?>
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
                        <?php endforeach; ?> -->

                        <!-- //varolista nelkuli slotok generalasa -->
                        <?php foreach ($all_slots as $slot): ?>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <?php if ($slot['booked']): ?>
                                    <button type="button" class="btn btn-danger w-100 slot-box shadow-sm" disabled style="opacity: 0.65; cursor: not-allowed;">
                                        <span class="fw-bold"><?php echo $slot['time']; ?></span>
                                        <small style="font-size: 0.6rem; text-transform: uppercase;">Foglalt</small>
                                    </button>
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

                    <a href="foglalas.php?salon_id=<?php echo $salon_id; ?>&service_id=<?php echo $service_id; ?>&date=<?php echo $date; ?>" class="btn btn-link w-100 text-decoration-none text-muted text-center">
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