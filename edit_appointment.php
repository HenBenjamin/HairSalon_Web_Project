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

// 1. Ellenőrizzük, hogy a foglalás valóban a tulajdonos szalonjához tartozik-e
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

// 2. Szolgáltatások lekérése a módosításhoz (ha mást kérne a vendég)
$stmt = $pdo->prepare("SELECT service_id, name FROM services WHERE salon_id = ?");
$stmt->execute([$app['salon_id']]);
$services = $stmt->fetchAll();

// 3. Módosítás mentése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_service = $_POST['service_id'];
    $new_date = $_POST['date'];
    $new_time = $_POST['time'];
    $new_status = $_POST['status'];

    $update = $pdo->prepare("
        UPDATE appointments 
        SET service_id = ?, appointment_date = ?, appointment_time = ?, status = ? 
        WHERE appointment_id = ?
    ");
    $update->execute([$new_service, $new_date, $new_time, $new_status, $appointment_id]);

    header("Location: owner_appointments.php?msg=updated");
    exit;
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Foglalás szerkesztése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark fw-bold">
                    Foglalás módosítása: <?php echo htmlspecialchars($app['username']); ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted">Vendég neve</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($app['username']); ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Szolgáltatás</label>
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
                                <label class="form-label">Dátum</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $app['appointment_date']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Időpont</label>
                                <input type="time" name="time" class="form-control" value="<?php echo date('H:i', strtotime($app['appointment_time'])); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Állapot</label>
                            <select name="status" class="form-select">
                                <option value="booked" <?php echo ($app['status'] == 'booked') ? 'selected' : ''; ?>>Lefoglalva (booked)</option>
                                <option value="completed" <?php echo ($app['status'] == 'completed') ? 'selected' : ''; ?>>Teljesítve (completed)</option>
                                <option value="cancelled" <?php echo ($app['status'] == 'cancelled') ? 'selected' : ''; ?>>Lemondva (cancelled)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Módosítások mentése</button>
                            <a href="owner_appointments.php" class="btn btn-outline-secondary">Vissza a listához</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>