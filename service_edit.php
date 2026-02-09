<?php
session_start();
require_once "config.php";

$id = $_GET['id'] ?? null;
$owner_id = $_SESSION['user_id'];

// Ellenőrizzük, hogy hozzáférhet-e (saját szaloné-e a szolgáltatás)
$stmt = $pdo->prepare("SELECT s.* FROM services s JOIN salons sa ON s.salon_id = sa.salon_id WHERE s.service_id = ? AND sa.owner_id = ?");
$stmt->execute([$id, $owner_id]);
$service = $stmt->fetch();

if (!$service) die("Hiba: Szolgáltatás nem található!");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upd = $pdo->prepare("UPDATE services SET name = ?, price = ?, duration = ? WHERE service_id = ?");
    $upd->execute([$_POST['name'], $_POST['price'], $_POST['duration'], $id]);
    header("Location: services.php?msg=updated");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Szerkesztés</title>
</head>
<body class="bg-light p-5">
<div class="container" style="max-width: 500px;">
    <div class="card p-4 shadow-sm">
        <h3>Szolgáltatás módosítása</h3>
        <form method="post">
            <div class="mb-3">
                <label>Név</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($service['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Ár (Dinár)</label>
                <input type="number" name="price" class="form-control" value="<?php echo $service['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Időtartam (perc)</label>
                <input type="number" name="duration" class="form-control" value="<?php echo $service['duration']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Mentés</button>
            <a href="services.php" class="btn btn-link w-100 mt-2">Mégse</a>
        </form>
    </div>
</div>
</body>
</html>