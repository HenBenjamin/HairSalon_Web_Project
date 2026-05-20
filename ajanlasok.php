<?php
session_start();
require_once "config.php";

// Megkapjuk az arcformát az URL-ből vagy a Session-ből
$shape = $_GET['shape'] ?? $_SESSION['last_detected_shape'] ?? '';
$gender = $_SESSION['user_gender'] ?? 'ferfi';
$user_image = $_SESSION['last_uploaded_image'] ?? './images/default_face.jpg';

if (empty($shape)) {
    header("Location: arc_elemzes.php");
    exit;
}

// Lekérdezzük az adatbázisból a megfelelő frizurákat
$stmt = $pdo->prepare("SELECT * FROM hair_recommendations WHERE face_shape = ? AND gender = ?");
$stmt->execute([$shape, $gender]);
$recommendations = $stmt->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Személyre szabott ajánlatok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<nav><?php include 'navbar.php'; ?></nav>

<div class="container mt-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-4 text-center">
            <!-- Megmutatjuk a felhasználó saját fotóját -->
            <img src="<?= htmlspecialchars($user_image) ?>" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;">
            <h4 class="mt-3">A te arcformád: <span class="text-primary"><?= ucfirst(htmlspecialchars($shape)) ?></span></h4>
            <p class="text-muted">Nemed: <?= ucfirst(htmlspecialchars($gender)) ?></p> <!-- Nem megjelenítése, meg nem biztos hogy kell -->
        </div>
        <div class="col-md-8">
            <div class="alert alert-info border-0 shadow-sm">
                <h2 class="alert-heading">AI Elemzés Kész!</h2>
                <p>Az OpenCV alapú modulunk kielemezte az arcvonalasidat. Az alábbi frizurák segítenek a legjobban kiemelni az előnyös vonásaidat.</p>
            </div>
        </div>
    </div>

    <h3 class="mb-4">Ajánlott stílusok neked:</h3>
    <div class="row">
        <?php if (count($recommendations) > 0): ?>
            <?php foreach ($recommendations as $hair): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <!-- Az images/styles mappából töltjük be a képet -->
                        <img src="styles/<?= htmlspecialchars($hair['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($hair['style_name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($hair['style_name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($hair['description']) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="szalonok.php" class="btn btn-outline-primary btn-sm w-100">Vissza a szalonokhoz</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted">Sajnos ehhez az arcformához még nincsenek feltöltve ajánlások az adatbázisba.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>