<?php
session_start();
require_once "config.php";

// Csak a tulajdonos (owner) férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];

// Megkeressük az owner szalonját
$stmt = $pdo->prepare("SELECT salon_id, name FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();

if (!$salon) {
    die("Hiba: Önhöz még nincs szalon rendelve! Kérje az Admin segítségét.");
}

$salon_id = $salon['salon_id'];

// --- TÖRLÉS KEZELÉSE ---
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    // Biztonsági ellenőrzés: csak a saját szalonjából törölhet
    $del = $pdo->prepare("DELETE FROM services WHERE service_id = ? AND salon_id = ?");
    $del->execute([$del_id, $salon_id]);
    header("Location: services.php?msg=deleted");
    exit;
}

// Új szolgáltatás hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $duration = (int)$_POST['duration'];

    if (!empty($name) && $price > 0 && $duration > 0) {
        $ins = $pdo->prepare("INSERT INTO services (salon_id, name, price, duration) VALUES (?, ?, ?, ?)");
        $ins->execute([$salon_id, $name, $price, $duration]);
        $message = "Szolgáltatás hozzáadva!";
    }
}

// Szolgáltatások listázása
$stmt = $pdo->prepare("SELECT * FROM services WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$services = $stmt->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szolgáltatások kezelése</title>
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
    <h2><?php echo htmlspecialchars($salon['name']); ?> - Szolgáltatások</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Új szolgáltatás</h5>
                <form method="post">
                    <div class="mb-2">
                        <label>Név (pl. Férfi hajvágás)</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Ár (Dinár)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Időtartam (perc)</label>
                        <input type="number" name="duration" class="form-control" required>
                    </div>
                    <button type="submit" name="add_service" class="btn btn-primary w-100">Mentés</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h5>Már felvitt szolgáltatások</h5>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Név</th>
                        <th>Ár</th>
                        <th>Idő</th>
                        <th class="text-end">Műveletek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($services as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo number_format($s['price'], 0, ',', ' '); ?> Din</td>
                            <td><?php echo $s['duration']; ?> perc</td>
                            <td class="text-end">
                                <a href="service_edit.php?id=<?php echo $s['service_id']; ?>"
                                   class="btn btn-sm btn-warning me-1">
                                    Szerkesztés
                                </a>
                                <a href="services.php?delete_id=<?php echo $s['service_id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Biztosan törölni akarod ezt a szolgáltatást?')">
                                    Törlés
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>