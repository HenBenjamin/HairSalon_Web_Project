<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$salon_id = $_GET['salon_id'] ?? null;
$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    // 1. Ellenőrzés: Értékelt már ez a user?
    $check = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND salon_id = ?");
    $check->execute([$user_id, $salon_id]);

    if ($check->fetchColumn() > 0) {
        $error = "Ezt a szalont már egyszer értékelted!";
    } elseif ($salon_id && $rating >= 1 && $rating <= 5) {
        // 2. Mentés: Csak ha még nem értékelt
        $stmt = $pdo->prepare("INSERT INTO reviews (salon_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$salon_id, $user_id, $rating, $comment]);

        header("Location: szalonok.php?msg=success");
        exit;
    } else {
        $error = "Kérjük, válassz legalább egy csillagot az értékeléshez!";
    }
}

// Szalon nevének lekérése
$st = $pdo->prepare("SELECT name FROM salons WHERE salon_id = ?");
$st->execute([$salon_id]);
$salon = $st->fetch();

if (!$salon) {
    header("Location: szalonok.php"); exit;
}

//Összes korábbi értékelés lekérése a szalonhoz a felhasználók nevével
$reviewsStmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.salon_id = ? 
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$salon_id]);
$reviews = $reviewsStmt->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Értékelés - <?php echo htmlspecialchars($salon['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<?php include 'navbar.php'; ?>

<div class="container mt-5 flex-grow-1 mb-5">
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mx-auto" style="max-width: 1000px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row justify-content-center g-4">
        
        <div class="col-md-4 align-self-start">
            <div class="card card-opinion border-0 shadow-sm p-4 sticky-top" style="top: 20px;">
                <h3 class="text-center mb-1"><?php echo htmlspecialchars($salon['name']); ?></h3>
                
                <?php
                // Megnézzük, hogy ez a konkrét user értékelte-e már ezt a szalont
                $checkUserReview = $pdo->prepare("SELECT rating FROM reviews WHERE user_id = ? AND salon_id = ?");
                $checkUserReview->execute([$user_id, $salon_id]);
                $userReview = $checkUserReview->fetch();
                ?>

                <?php if ($userReview): ?>
                    <div class="text-center py-4">
                        <div class="text-success mb-3">
                            <i class="bi bi-patch-check-fill" style="font-size: 3rem;"></i>
                        </div>
                        <h5>Köszönjük az értékelést!</h5>
                        <p class="text-muted small">Te már értékelted ezt a szalont</p>
                        <div class="text-warning mb-3">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $userReview['rating'] ? '<i class="bi bi-star-fill me-1"></i>' : '<i class="bi bi-star me-1" style="color: #ccc;"></i>';
                            }
                            ?>
                        </div>
                        <a href="szalonok.php" class="btn btn-secondary btn-sm w-100">Vissza a szalonokhoz</a>
                    </div>

                <?php else: ?>
                    <p class="text-muted text-center small">Kattints a csillagokra az értékeléshez!</p>

                    <form method="POST" id="ratingForm">
                        <div class="star-rating mb-3 text-center" id="star-container">
                            <i class="bi bi-star-fill" data-value="1"></i>
                            <i class="bi bi-star-fill" data-value="2"></i>
                            <i class="bi bi-star-fill" data-value="3"></i>
                            <i class="bi bi-star-fill" data-value="4"></i>
                            <i class="bi bi-star-fill" data-value="5"></i>
                        </div>

                        <input type="hidden" name="rating" id="rating-input" value="" required>

                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold small">Szöveges vélemény</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Írd le a tapasztalataidat (opcionális)..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-dark btn-lg w-100">Értékelés beküldése</button>
                        <a href="szalonok.php" class="btn btn-link mt-2 w-100 text-center text-muted text-decoration-none">Mégse</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-opinion border-0 shadow-sm p-4">
                <h4 class="mb-4 text-secondary">
                    <i class="fa-regular fa-comment-dots"></i> Vendégek véleményei
                </h4>
                
                <?php if (count($reviews) === 0): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-square-dots" style="font-size: 2.5rem;"></i>
                        <p class="mt-2">Ehhez a szalonhoz még nem érkezett értékelés. Legyél te az első!</p>
                    </div>
                <?php else: ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark"><?php echo htmlspecialchars($rev['username']); ?></strong>
                                    <small class="text-muted"><?php echo date('Y.m.d', strtotime($rev['created_at'] ?? 'now')); ?></small>
                                </div>
                                <div class="text-warning mb-2">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rev['rating'] ? '<i class="bi bi-star-fill me-1"></i>' : '<i class="bi bi-star me-1" style="color: #ccc;"></i>';
                                    }
                                    ?>
                                </div>
                                <?php if (!empty($rev['comment'])): ?>
                                    <p class="text-muted mb-0 small bg-light p-2 rounded italic">
                                        "<?php echo htmlspecialchars($rev['comment']); ?>"
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const stars = document.querySelectorAll('#star-container i');
    const ratingInput = document.getElementById('rating-input');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            updateStars(value);
        });

        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            highlightStars(value);
        });

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
            s.classList.remove('hover');
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