<?php
session_start();
require_once "config.php";

// Admin ellenőrzés
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// 1. Szalon aktiválása/deaktiválása
if (isset($_GET['toggle_active'])) {
    $salon_id = (int)$_GET['toggle_active'];
    $current_status = (int)$_GET['status'];
    $new_status = ($current_status === 1) ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE salons SET active = ? WHERE salon_id = ?");
    $stmt->execute([$new_status, $salon_id]);
    header("Location: admin_salons.php?msg=status_updated");
    exit;
}

// 2. Új szalon mentése
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_salon'])) {
    $name = trim($_POST['salon_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $owner_id = $_POST['owner_id'];

    if (!empty($name) && !empty($owner_id)) {
        // Alapértelmezetten aktív (1)
        $stmt = $pdo->prepare("INSERT INTO salons (name, owner_id, address, city, phone, active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $owner_id, $address, $city, $phone]);

        // Tulajdonos rang beállítása
        $update = $pdo->prepare("UPDATE users SET role = 'owner' WHERE user_id = ?");
        $update->execute([$owner_id]);

        $message = "Szalon sikeresen létrehozva!";
    }
}

// 3. Szalonok listázása a státusszal együtt
$salons = $pdo->query("SELECT s.*, u.username FROM salons s JOIN users u ON s.owner_id = u.user_id ORDER BY s.name ASC")->fetchAll();

// 4. Potenciális tulajdonosok lekérése
$potential_owners = $pdo->query("SELECT user_id, username FROM users WHERE role = 'owner'")->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Admin - Szalonok Kezelése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand h1 mb-0"><i class="fas fa-cut me-2"></i>HairSalon Admin</span>
        <div class="navbar-nav ms-auto">
            <a href="admin_users.php" class="nav-link">Felhasználók kezelése</a>
            <a href="admin_salons.php" class="nav-link active">Szalonok kezelése</a>
            <a href="logout.php" class="nav-link text-danger">Kijelentkezés</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">Új szalon regisztrálása</div>
                <div class="card-body">
                    <?php if($message) echo "<div class='alert alert-success small'>$message</div>"; ?>
                    <form method="post">
                        <div class="mb-2">
                            <label class="small fw-bold">Szalon neve</label>
                            <input type="text" name="salon_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Város</label>
                            <input type="text" name="city" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Cím</label>
                            <input type="text" name="address" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Telefon</label>
                            <input type="text" name="phone" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Tulajdonos hozzárendelése</label>
                            <select name="owner_id" class="form-select form-select-sm">
                                <?php foreach($potential_owners as $po): ?>
                                    <option value="<?= $po['user_id'] ?>"><?= htmlspecialchars($po['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="create_salon" class="btn btn-primary btn-sm w-100">Létrehozás</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between">
                    <span>Regisztrált szalonok</span>
                    <small>Összesen: <?= count($salons) ?></small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Név / Város</th>
                            <th>Tulajdonos</th>
                            <th class="text-center">Állapot</th>
                            <th class="text-end px-3">Művelet</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($salons as $s): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($s['city']) ?>, <?= htmlspecialchars($s['address']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($s['username']) ?></td>
                                <td class="text-center">
                                    <?php if ($s['active']): ?>
                                        <span class="badge bg-success">Megjelenik</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejtett</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-3">
                                    <a href="admin_salons.php?toggle_active=<?= $s['salon_id'] ?>&status=<?= $s['active'] ?>"
                                       class="btn btn-sm <?= $s['active'] ? 'btn-outline-danger' : 'btn-success' ?>">
                                        <?= $s['active'] ? 'Deaktiválás' : 'Aktiválás' ?>
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
</div>

</body>
</html>