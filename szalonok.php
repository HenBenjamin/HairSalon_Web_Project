<?php
session_start();
require_once "config.php";

// Megnézzük, be van-e lépve valaki (ha nincs, 0 lesz az ID)
$current_user_id = $_SESSION['user_id'] ?? 0;

// Frissített lekérdezés egy segéd-lekérdezéssel (subquery)
$stmt = $pdo->prepare("
    SELECT s.salon_id, s.name, s.city, s.address, s.phone, s.image_url, 
           AVG(r.rating) as avg_rating, 
           COUNT(r.review_id) as review_count,
           -- Ez nézi meg, hogy az adott USER és SZALON páros szerepel-e már a táblában
           (SELECT COUNT(*) FROM reviews WHERE user_id = ? AND salon_id = s.salon_id) as already_reviewed
    FROM salons s 
    LEFT JOIN reviews r ON s.salon_id = r.salon_id 
    WHERE s.active = 1 
    GROUP BY s.salon_id
");
$stmt->execute([$current_user_id]);
$salons = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Szalonok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav>
    <?php include 'navbar.php'; ?>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Elérhető fodrászszalonok</h2>
    <div class="row">
        <?php foreach ($salons as $salon): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="./images/<?php echo htmlspecialchars($salon['image_url'] ?? 'default.jpg'); ?>"
                         class="card-img-top"
                         alt="<?php echo htmlspecialchars($salon['name']); ?>"
                         style="height: 200px; object-fit: cover;">

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($salon['name']); ?></h5>
                        <div class="mb-2">
                            <?php if ($salon['review_count'] > 0): ?>
                                <?php
                                $rating = round($salon['avg_rating'], 1); // Kerekített átlag (pl. 4.5)
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                ?>
                                <span class="text-warning">
            <?php
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $fullStars) echo '<i class="bi bi-star-fill"></i>';
                elseif ($i == $fullStars + 1 && $hasHalfStar) echo '<i class="bi bi-star-half"></i>';
                else echo '<i class="bi bi-star"></i>';
            }
            ?>
        </span>
                                <span class="small text-muted">(<?php echo $rating; ?> / <?php echo $salon['review_count']; ?> vélemény)</span>
                            <?php else: ?>
                                <small class="text-muted italic">Még nincs értékelés</small>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted small">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($salon['city'] . ", " . $salon['address']); ?>
                        </p>

                        <div class="card-footer bg-transparent border-top-0">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="foglalas.php?salon_id=<?php echo $salon['salon_id']; ?>"
                                   class="btn btn-primary w-100">
                                    <i class="bi bi-calendar-check"></i> Időpontot foglalok
                                </a>
                                <?php if ($salon['already_reviewed'] > 0): ?>
                                    <button class="btn btn-outline-success w-100 disabled" disabled>
                                        <i class="bi bi-check-all"></i> Már értékelted
                                    </button>
                                <?php else: ?>
                                    <a href="ertekeles.php?salon_id=<?= $salon['salon_id']; ?>" class="btn btn-outline-success w-100">
                                        <i class="bi bi-star"></i> Értékelem a szalont
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <?php endif; ?>
                                <div class="d-grid gap-2">
                                    <a href="login.php" class="btn btn-outline-secondary">
                                        Jelentkezz be a foglaláshoz
                                    </a>
                                    <small class="text-center text-muted">Még nincs fiókod? <a href="register.php">Regisztrálj!</a></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-6 text-md-start">
                <h5>HairSalon Szabadka</h5>
                <p class="small text-muted">A legmodernebb időpontfoglaló rendszer fodrászoknak és vendégeknek.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; 2025 HairSalon Projekt. Minden jog fenntartva.</p>
            </div>
        </div>
    </div>
</footer>
</body>
</html>