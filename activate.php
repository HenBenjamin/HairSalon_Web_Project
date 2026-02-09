<?php
require_once "config.php";

$status = "info";
$title = "";
$message = "";

if (!isset($_GET['token'])) {
    $status = "danger";
    $title = "Hiba!";
    $message = "Érvénytelen aktivációs link.";
} else {
    $token = $_GET['token'];
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE activation_token = ? AND is_active = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $update = $pdo->prepare("UPDATE users SET is_active = 1, activation_token = '' WHERE user_id = ?");
            $update->execute([$user['user_id']]);
            $status = "success";
            $title = "Sikeres aktiválás!";
            $message = "A fiókodat sikeresen aktiváltuk. Most már bejelentkezhetsz.";
        } else {
            $status = "warning";
            $title = "Figyelem!";
            $message = "Ez a fiók már aktív, vagy a link lejárt.";
        }
    } catch (PDOException $e) {
        $status = "danger";
        $title = "Adatbázis hiba";
        $message = "Váratlan hiba történt az aktiválás során.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiók aktiválása - HairSalon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; }
        .activation-card { max-width: 450px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .icon-box { font-size: 4rem; margin-bottom: 20px; }
        .btn-custom { border-radius: 25px; padding: 10px 30px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 text-center">
            <div class="card activation-card mx-auto p-4 p-md-5">
                <div class="card-body">
                    <div class="icon-box text-<?php echo $status; ?>">
                        <?php if($status == "success"): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif($status == "warning"): ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i>
                        <?php endif; ?>
                    </div>

                    <h2 class="fw-bold mb-3"><?php echo $title; ?></h2>
                    <p class="text-muted mb-4"><?php echo $message; ?></p>

                    <?php if($status == "success"): ?>
                        <a href="login.php" class="btn btn-primary btn-custom shadow-sm">
                            <i class="fas fa-sign-in-alt me-2"></i>Tovább a bejelentkezéshez
                        </a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-outline-secondary btn-custom">
                            Vissza a főoldalra
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <p class="mt-4 text-muted small">&copy; 2026 HairSalon Booking System</p>
        </div>
    </div>
</div>

</body>
</html>