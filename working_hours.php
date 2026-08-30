<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();
$salon_id = $salon['salon_id'];

$napok = [1 => 'Hétfő', 2 => 'Kedd', 3 => 'Szerda', 4 => 'Csütörtök', 5 => 'Péntek', 6 => 'Szombat', 7 => 'Vasárnap'];

// Mentés
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($napok as $key => $nap) {
        //Ha le van tiltva a mező, akkor üres stringet adunk neki, vagy megtartjuk az alapértelmezettet
        $start = $_POST['start_'.$key] ?? '00:00';
        $end = $_POST['end_'.$key] ?? '00:00';
        $closed = isset($_POST['closed_'.$key]) ? 1 : 0;

        // Megnézzük, van-e már rögzítve adat ehhez a naphoz
        $check = $pdo->prepare("SELECT working_hour_id FROM working_hours WHERE salon_id = ? AND day_of_week = ?");
        $check->execute([$salon_id, $key]);

        if ($check->rowCount() > 0) {
            $upd = $pdo->prepare("UPDATE working_hours SET start_time = ?, end_time = ?, is_closed = ? WHERE salon_id = ? AND day_of_week = ?");
            $upd->execute([$start, $end, $closed, $salon_id, $key]);
        } else {
            $ins = $pdo->prepare("INSERT INTO working_hours (salon_id, day_of_week, start_time, end_time, is_closed) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$salon_id, $key, $start, $end, $closed]);
        }
    }
    $message = "Nyitvatartás frissítve!";
}

// Aktuális adatok lekérése a formhoz
$hours_stmt = $pdo->prepare("SELECT * FROM working_hours WHERE salon_id = ?");
$hours_stmt->execute([$salon_id]);
$current_hours = [];
while($row = $hours_stmt->fetch()) {
    $current_hours[$row['day_of_week']] = $row;
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Nyitvatartás beállítása</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav>
        <?php include 'navbar.php'; ?>
    </nav>
<div class="container my-5 flex-grow-1">
    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
        <h3 class="working_h3 text-dark fw-bold mb-4"><i class="fa-solid fa-business-time"></i> Heti nyitvatartás beállítása</h3>
        
        <?php if(isset($message)) echo "<div class='alert alert-success border-0 shadow-sm rounded-3'>$message</div>"; ?>
        
        <form method="post">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-secondary">
                    <tr>
                        <th class="py-3"><i class="fa-regular fa-calendar"></i> Nap</th>
                        <th class="py-3"><i class="fa-solid fa-lock-open"></i> Nyitás</th>
                        <th class="py-3"><i class="fa-solid fa-lock"></i> Zárás</th>
                        <th class="py-3 text-center"><i class="fa-solid fa-moon"></i> Zárva?</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($napok as $num => $nev):
                        $h = $current_hours[$num] ?? ['start_time' => '08:00', 'end_time' => '16:00', 'is_closed' => 0];
                        $isClosed = $h['is_closed'] == 1;
                        ?>
                        <tr id="row_<?= $num ?>" class="<?= $isClosed ? 'disabled-row' : '' ?>">
                            <td><span class="fs-6 fw-semibold text-dark"><?= $nev ?></span></td>
                            <td>
                                <div class="time-input-group">
                                    <input type="time" name="start_<?= $num ?>" id="start_<?= $num ?>" class="form-control time-picker-custom" value="<?= substr($h['start_time'], 0, 5) ?>" <?= $isClosed ? 'disabled' : '' ?>>
                                </div>
                            </td>
                            <td>
                                <div class="time-input-group">
                                    <input type="time" name="end_<?= $num ?>" id="end_<?= $num ?>" class="form-control time-picker-custom" value="<?= substr($h['end_time'], 0, 5) ?>" <?= $isClosed ? 'disabled' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input type="checkbox" name="closed_<?= $num ?>" id="closed_<?= $num ?>" class="form-check-input switch-trigger" data-day="<?= $num ?>" <?= $isClosed ? 'checked' : '' ?>>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm fw-bold"><i class="fa-regular fa-floppy-disk"></i> Minden nap mentése</button>
                <a href="services.php" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Vissza a szolgáltatásokhoz</a>
            </div>
        </form>
    </div>
</div>
<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const switches = document.querySelectorAll('.switch-trigger');

    switches.forEach(sw => {
        sw.addEventListener('change', function () {
            const dayNum = this.getAttribute('data-day');
            const row = document.getElementById(`row_${dayNum}`);
            const startInput = document.getElementById(`start_${dayNum}`);
            const endInput = document.getElementById(`end_${dayNum}`);

            if (this.checked) {
                row.classList.add('disabled-row');
                startInput.disabled = true;
                endInput.disabled = true;
            } else {
                row.classList.remove('disabled-row');
                startInput.disabled = false;
                endInput.disabled = false;
            }
        });
    });
});
</script>
</body>
</html>