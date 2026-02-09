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
        $start = $_POST['start_'.$key];
        $end = $_POST['end_'.$key];
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
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Szalon Kezelés</a>
        <div class="navbar-nav">
            <a class="nav-link" href="services.php">Szolgáltatások</a>
            <a class="nav-link" href="working_hours.php">Nyitvatartás</a>
            <a class="nav-link" href="owner_appointments.php">Időpontok kezelése</a>
            <a class="nav-link text-danger" href="logout.php">Kijelentkezés</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3>Heti nyitvatartás beállítása</h3>
        <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
        <form method="post">
            <table class="table">
                <thead>
                <tr>
                    <th>Nap</th>
                    <th>Nyitás</th>
                    <th>Zárás</th>
                    <th>Zárva?</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($napok as $num => $nev):
                    $h = $current_hours[$num] ?? ['start_time' => '08:00', 'end_time' => '16:00', 'is_closed' => 0];
                    ?>
                    <tr>
                        <td><strong><?= $nev ?></strong></td>
                        <td><input type="time" name="start_<?= $num ?>" class="form-control" value="<?= substr($h['start_time'], 0, 5) ?>"></td>
                        <td><input type="time" name="end_<?= $num ?>" class="form-control" value="<?= substr($h['end_time'], 0, 5) ?>"></td>
                        <td><input type="checkbox" name="closed_<?= $num ?>" <?= $h['is_closed'] ? 'checked' : '' ?>></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Minden nap mentése</button>
            <a href="services.php" class="btn btn-secondary">Vissza a szolgáltatásokhoz</a>
        </form>
    </div>
</div>
</body>
</html>