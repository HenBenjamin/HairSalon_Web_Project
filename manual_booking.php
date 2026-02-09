<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php"); exit;
}

$owner_id = $_SESSION['user_id'];

// 1. Szalon és szolgáltatások lekérése
$stmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();
$salon_id = $salon['salon_id'];

$stmt = $pdo->prepare("SELECT service_id, name FROM services WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$services = $stmt->fetchAll();

// 2. Összes regisztrált felhasználó lekérése
$stmt = $pdo->query("SELECT user_id, username FROM users WHERE role = 'user' ORDER BY username ASC");
$users = $stmt->fetchAll();

// 3. Foglalt időpontok lekérése a kiválasztott napra (ha van ilyen)
$selected_date = $_POST['date'] ?? null;
$booked_times = [];

if ($selected_date) {
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE salon_id = ? AND appointment_date = ? AND status = 'booked'");
    $stmt->execute([$salon_id, $selected_date]);
    $booked_times = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $booked_times = array_map(function($t) { return date('H:i', strtotime($t)); }, $booked_times);
}

// 4. Mentés feldolgozása (Csak ha a 'final_save' gombbal küldték be)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_save'])) {
    $user_id = $_POST['user_id'];
    $service_id = $_POST['service_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Biztonsági ellenőrzés mentés előtt is
    $check = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND (salon_id = ? OR user_id = ?) AND status = 'booked'");
    $check->execute([$date, $time, $salon_id, $user_id]);

    if ($check->fetchColumn() > 0) {
        $error = "Hiba: Ez az időpont időközben foglalt lett!";
    } else {
        $ins = $pdo->prepare("INSERT INTO appointments (user_id, salon_id, service_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'booked')");
        $ins->execute([$user_id, $salon_id, $service_id, $date, $time]);
        header("Location: owner_appointments.php?msg=success");
        exit;
    }
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új foglalás rögzítése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .time-slot-btn:disabled { cursor: not-allowed; }
    </style>
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3 text-center">
                    <h4 class="mb-0">Időpont manuális rögzítése</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezárás"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="bookingForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Vendég kiválasztása</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-Válasszon vendéget-</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['user_id']; ?>" <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $u['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Szolgáltatás</label>
                            <select name="service_id" class="form-select" required>
                                <option value="">-Válasszon szolgáltatást-</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?php echo $s['service_id']; ?>" <?php echo (isset($_POST['service_id']) && $_POST['service_id'] == $s['service_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Dátum</label>
                            <input type="date" name="date" class="form-control"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($selected_date); ?>"
                                   onchange="this.form.submit()" required>
                            <small class="text-muted">A dátum kiválasztása után láthatja a szabad időpontokat.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">4. Időpont</label>
                            <?php if ($selected_date): ?>
                                <button type="button" class="btn btn-outline-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#timePickerModal">
                                    <span id="selected_time_label">Kattintson az idősáv kiválasztásához</span>
                                </button>
                                <input type="hidden" name="time" id="final_time_input" value="<?php echo $_POST['time'] ?? ''; ?>" required>
                            <?php else: ?>
                                <div class="alert alert-secondary py-2 text-center small">Válasszon dátumot az időpontokhoz!</div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="final_save" class="btn btn-success btn-lg w-100 shadow-sm">Foglalás véglegesítése</button>
                        <a href="owner_appointments.php" class="btn btn-link w-100 mt-2">Mégse</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="timePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Szabad időpontok: <?php echo $selected_date; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="row g-2">
                    <?php
                    $times = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00'];
                    foreach ($times as $t):
                        $is_booked = in_array($t, $booked_times);
                        ?>
                        <div class="col-3">
                            <button type="button"
                                    class="btn <?php echo $is_booked ? 'btn-danger disabled opacity-50' : 'btn-outline-primary'; ?> w-100 py-2 time-slot-btn"
                                    data-time="<?php echo $t; ?>"
                                <?php echo $is_booked ? 'disabled' : ''; ?>>
                                <?php echo $t; ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light py-1">
                <small class="text-danger">● Foglalt</small>
                <small class="text-primary ms-2">○ Szabad</small>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Időpont választás kezelése a modalban
    document.querySelectorAll('.time-slot-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.disabled) {
                const selectedTime = this.getAttribute('data-time');

                // Rejtett mező kitöltése
                document.getElementById('final_time_input').value = selectedTime;

                // Gomb szövegének módosítása
                document.getElementById('selected_time_label').innerHTML = "<b>Kiválasztott időpont: " + selectedTime + "</b>";

                // Modal bezárása
                const modalEl = document.getElementById('timePickerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            }
        });
    });

    // Ha az oldalfrissítés után már van kiválasztott idő (pl. hiba után), írjuk ki
    window.onload = function() {
        const savedTime = document.getElementById('final_time_input')?.value;
        if (savedTime) {
            document.getElementById('selected_time_label').innerHTML = "<b>Kiválasztott időpont: " + savedTime + "</b>";
        }
    };
</script>

</body>
</html>