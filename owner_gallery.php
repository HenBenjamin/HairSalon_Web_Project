<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$salon_id = $_SESSION['salon_id'];

try {
    //  a lekérdezés lényege:
    // összekötjük a foglalásokat a felhasználóval, az elmentett stílusaival és a frizurák képeivel.
    // csak azokat kérjük le, ahol a foglalás állapota nem 'completed' (lezárt) és nem 'cancelled' (törölt),
    // valamint a mai vagy jövőbeni dátumra vonatkoznak.
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            u.username AS client_name,
            u.mobilenumber AS client_phone,
            hr.style_name,
            hr.image_url AS style_image,
            hr.description AS style_desc
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN user_favored_styles ufs ON u.user_id = ufs.user_id
        JOIN hair_recommendations hr ON ufs.recommendation_id = hr.id
        WHERE a.salon_id = ? 
          AND a.status NOT IN ('completed', 'cancelled')
          AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->execute([$salon_id]);
    $active_galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (\Throwable $e) {
    die("Adatbázis hiba történt: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Inspirációs Galéria - Aktív Foglalások</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav>
    <?php include 'navbar.php'; ?>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Vendégek Elképzelései</h2>
            <p class="text-muted">Az aktív, elkövetkező foglalásokhoz kiválasztott frizura ajánlások.</p>
        </div>
        <!-- <span class="badge bg-primary fs-6 px-3 py-2">
            <?= count($active_galleries) ?> aktív igény
        </span> -->
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <?php if (count($active_galleries) > 0): ?>
            <?php foreach ($active_galleries as $item): ?>
                <div class="col">
                    <div class="card card-ai-image h-100 border-3 shadow-sm overflow-hidden position-relative">
                        
                        <!-- <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark shadow-sm">
                            <i class="bi bi-clock-history"></i> Aktív foglalás
                        </span> -->

                        <img src="styles/<?= htmlspecialchars($item['style_image']) ?>" class="card-img-top" alt="Frizura" style="height: 320px; object-fit: cover; object-position: top;">
                        
                        <div class="card-body p-4">
                            <h4 class="card-title fw-bold text-primary mb-1"><?= htmlspecialchars($item['style_name']) ?></h4>
                            <p class="text small mb-3"><?= htmlspecialchars($item['style_desc']) ?></p>
                            
                            <hr class="text-muted my-3">
                            
                            <h6 class="fw-bold text-dark text-uppercase fs-7 mb-2" style="letter-spacing: 0.5px;">Vendég adatai</h6>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-regular fa-user me-2"></i>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($item['client_name']) ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fa-solid fa-phone me-2"></i>
                                <span class="text small"><?= htmlspecialchars($item['client_phone']) ?></span>
                            </div>

                            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Dátum</small>
                                    <strong class="text-dark"><?= date("Y.m.d", strtotime($item['appointment_date'])) ?></strong>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Időpont</small>
                                    <strong class="text-dark"><?= date("H:i", strtotime($item['appointment_time'])) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="card-footer bg-white border-0 pt-0 pb-4 px-4">
                            <a href="appointment_details.php?id=<?= $item['appointment_id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-eye"></i> Foglalás kezelése
                            </a>
                        </div> -->
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 w-100 text-center py-5">
                <div class="card border-0 shadow-sm p-5">
                    <i class="bi bi-images text-muted display-1 mb-3"></i>
                    <h4 class="text-secondary fw-bold">Nincs megjeleníthető frizura igény</h4>
                    <p class="text-muted mb-0">Jelenleg egyetlen érkező vendéged sem küldött be frizuraötletet az AI modulból.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.html'; ?>
</body>
</html>