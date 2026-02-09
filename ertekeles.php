<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$salon_id = $_GET['salon_id'] ?? null;
$user_id = $_SESSION['user_id'];

// Ha elküldték a formot
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    // 1. ELLENŐRZÉS: Értékelt már ez a user?
    $check = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND salon_id = ?");
    $check->execute([$user_id, $salon_id]);

    if ($check->fetchColumn() > 0) {
        $error = "Ezt a szalont már egyszer értékelted!";
    } elseif ($salon_id && $rating >= 1 && $rating <= 5) {
        // 2. MENTÉS: Csak ha még nem értékelt
        $stmt = $pdo->prepare("INSERT INTO reviews (salon_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$salon_id, $user_id, $rating, $comment]);

        header("Location: szalonok.php?msg=success");
        exit;
    }
}

$st = $pdo->prepare("SELECT name FROM salons WHERE salon_id = ?");
$st->execute([$salon_id]);
$salon = $st->fetch();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Értékelés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .star-rating {
            font-size: 2.5rem;
            color: #ddd;
            display: inline-block;
        }
        .star-rating i {
            cursor: pointer;
            transition: color 0.2s;
        }
        /* Amikor egy csillag ki van választva vagy fölé viszik az egeret */
        .star-rating i.active, .star-rating i.hover {
            color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm text-center p-4">
                <h3><?php echo htmlspecialchars($salon['name']); ?></h3>
                <p class="text-muted">Kattints a csillagokra az értékeléshez!</p>

                <form method="POST" id="ratingForm">
                    <div class="star-rating mb-3" id="star-container">
                        <i class="bi bi-star-fill" data-value="1"></i>
                        <i class="bi bi-star-fill" data-value="2"></i>
                        <i class="bi bi-star-fill" data-value="3"></i>
                        <i class="bi bi-star-fill" data-value="4"></i>
                        <i class="bi bi-star-fill" data-value="5"></i>
                    </div>

                    <input type="hidden" name="rating" id="rating-input" value="" required>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small">Szöveges vélemény</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Opcionális..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-dark btn-lg w-100">Értékelés beküldése</button>
                    <a href="szalonok.php" class="btn btn-link mt-2 text-muted">Mégse</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const stars = document.querySelectorAll('#star-container i');
    const ratingInput = document.getElementById('rating-input');

    stars.forEach(star => {
        // KATTINTÁS: Ez rögzíti az értéket
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            updateStars(value);
        });

        // HOVER (Vizuális segédlet): Megmutatja, mit fogsz választani
        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            highlightStars(value);
        });

        // EGERET ELHÚZZA: Visszaállítja a kattintott értékre
        star.addEventListener('mouseout', function() {
            updateStars(ratingInput.value);
        });
    });

    function highlightStars(value) {
        stars.forEach(s => {
            if (s.getAttribute('data-value') <= value) {
                s.classList.add('hover');
            } else {
                s.classList.remove('hover');
            }
        });
    }

    function updateStars(value) {
        stars.forEach(s => {
            s.classList.remove('hover'); // Hover törlése
            if (s.getAttribute('data-value') <= value) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    }
</script>
</body>
</html>