<?php
session_start();
require_once 'config.php'; // Adatbázis kapcsolat ($pdo)

// Csak bejelentkezett felhasználók láthatják
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Felhasználó pontjainak lekérése
$stmt = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$userPoints = $stmt->fetchColumn() ?: 0;

// 2. Aktív (még fel nem használt) kuponok lekérése
$stmt = $pdo->prepare("SELECT * FROM coupons WHERE user_id = ? AND is_used = 0 ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$activeCoupons = $stmt->fetchAll();

// Konstans a ponthathárhoz (ezt később könnyen módosíthatod)
$POINTS_LIMIT = 100;
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hűségprogram - SzalonFoglaló</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-4 text-center">
                    <h3 class="mb-0">Hűségprogram</h3>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="display-4 fw-bold text-warning mb-2">
                        <i class="bi bi-star-fill"></i> <?= $userPoints ?>
                    </div>
                    <p class="text-muted mb-4">Összesített hűségpontjaid száma</p>

                    <?php 
                        $percent = min(($userPoints / $POINTS_LIMIT) * 100, 100); 
                    ?>
                    <div class="mb-2 d-flex justify-content-between small fw-bold">
                        <span>Haladás a következő kuponig</span>
                        <span><?= $userPoints ?> / <?= $POINTS_LIMIT ?> pont</span>
                    </div>
                    <div class="progress mb-4" style="height: 30px; border-radius: 15px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: <?= $percent ?>%">
                             <?= round($percent) ?>%
                        </div>
                    </div>

                    <?php if ($userPoints >= $POINTS_LIMIT): ?>
                        <div class="alert alert-success border-0 shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Gratulálunk! Elérted a ponthatárt. A következő fizetésnél automatikusan kapsz egy kupont!
                        </div>
                    <?php else: ?>
                        <p class="small text-muted">Gyűjts még <?= $POINTS_LIMIT - $userPoints ?> pontot az újabb kedvezményért!</p>
                    <?php endif; ?>
                </div>
            </div>

            <h4 class="mb-4 fw-bold mt-5">Elérhető kuponjaid</h4>
            <div class="row">
                <?php if (empty($activeCoupons)): ?>
                    <div class="col-12 text-center py-4">
                        <p class="text-muted italic">Még nincsenek elérhető kuponjaid. Vegyél igénybe szolgáltatásokat a pontgyűjtéshez!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeCoupons as $coupon): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm text-center p-3" style="border-left: 5px solid #0d6efd !important;">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted small fw-bold">Kedvezményes Kupon</h6>
                                    <div class="my-3">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= $coupon['code'] ?>" 
                                             alt="QR Code" class="img-fluid rounded shadow-sm">
                                    </div>
                                    <h5 class="fw-bold text-primary mb-1"><?= $coupon['code'] ?></h5>
                                    <p class="badge bg-primary fs-6 mb-0"><?= $coupon['discount_amount'] ?>% Kedvezmény</p>
                                    <hr>
                                    <p class="small text-muted mb-0">Kiállítva: <?= date('Y.m.d.', strtotime($coupon['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>