<?php
session_start();
require_once "config.php";

// Csak bejelentkezett felhasználó foglalhat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ellenőrizzük, kaptunk-e szalon ID-t
if (!isset($_GET['salon_id'])) {
    header("Location: szalonok.php");
    exit;
}

$salon_id = $_GET['salon_id'];

// Szalon adatainak lekérése
$stmt = $pdo->prepare("SELECT name, city, address FROM salons WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$salon = $stmt->fetch();

if (!$salon) {
    header("Location: szalonok.php");
    exit;
}

// Szolgáltatások lekérése
$stmt = $pdo->prepare("SELECT service_id, name, price, duration FROM services WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$services = $stmt->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Időpontfoglalás - <?php echo htmlspecialchars($salon['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0 text-white"><?php echo htmlspecialchars($salon['name']); ?> - Időpontfoglalás</h3>
                    <small><?php echo htmlspecialchars($salon['city'] . ", " . $salon['address']); ?></small>
                </div>
                <div class="card-body">
                    <form action="idopont_valasztas.php" method="GET">
                        <input type="hidden" name="salon_id" value="<?php echo $salon_id; ?>">

                        <h5 class="mb-3">Válasszon szolgáltatást:</h5>
                        <div class="list-group mb-4">
                            <?php foreach ($services as $s): ?>
                                <label class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <input class="form-check-input me-2" type="radio" name="service_id" value="<?php echo $s['service_id']; ?>" required>
                                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                                        <br><small class="text-muted">Időtartam: <?php echo $s['duration']; ?> perc</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo number_format($s['price'], 0, ',', ' '); ?> Din
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-4">
                            <label for="date" class="form-label fw-bold">Válasszon egy szabad napot:</label>
                            <input type="date" name="date" id="date" class="form-control"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <div id="info-text" class="form-text">
                                A kiválasztott napon elérhető időpontokat a következő lépésben láthatja.
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-success btn-lg w-100">Szabad időpontok keresése</button>
                        <a href="szalonok.php" class="btn btn-link w-100 mt-2 text-decoration-none">Vissza a szalonokhoz</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>