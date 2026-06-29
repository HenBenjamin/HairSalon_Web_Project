<?php
session_start();
require_once "config.php";

$user_id = $_SESSION['user_id'] ?? null;
$shape = $_GET['shape'] ?? $_SESSION['last_detected_shape'] ?? '';
$gender = $_SESSION['user_gender'] ?? 'ferfi';
$user_image = $_SESSION['last_uploaded_image'] ?? './images/default_face.jpg';

$face_ratio = $_SESSION['face_ratio'] ?? '0.00';
$jaw_ratio = $_SESSION['jaw_ratio'] ?? '0.00';

if (empty($shape)) {
    header("Location: arc_elemzes.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM hair_recommendations WHERE face_shape = ? AND gender = ?");
$stmt->execute([$shape, $gender]);
$recommendations = $stmt->fetchAll();

$active_appointment_id = null;
if ($user_id) {
    $app_stmt = $pdo->prepare("
        SELECT appointment_id 
        FROM appointments 
        WHERE user_id = ? 
          AND status = 'booked' 
          AND appointment_date >= CURDATE() 
        ORDER BY appointment_date ASC, appointment_time ASC 
        LIMIT 1
    ");
    $app_stmt->execute([$user_id]);
    $active_app = $app_stmt->fetch();
    
    if ($active_app) {
        $active_appointment_id = $active_app['appointment_id'];
    }
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Személyre szabott ajánlatok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav><?php include 'navbar.php'; ?></nav>

<div class="container mt-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-4 text-center">
            <img src="<?= htmlspecialchars($user_image) ?>" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #fff;">
            <h4 class="mt-3">A te arcformád: <span class="text-primary"><?= ucfirst(htmlspecialchars($shape)) ?></span></h4>
            <p class="text-muted">Nemed: <?= ucfirst(htmlspecialchars($gender)) ?></p>
        </div>
        
        <div class="col-md-8">
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <h2 class="alert-heading fw-bold">AI Elemzés Kész!</h2>
                <p class="mb-0">Az OpenCV és MediaPipe alapú modulunk kielemezte az arcvonalasidat. Az alábbi frizurák segítenek a legjobban kiemelni az előnyös vonásaidat.</p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4 fw-bold text-dark">Kalkulált Biometrikus Értékek</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="text-muted fs-7 text-uppercase" style="letter-spacing: 0.5px;">
                                    <th scope="col" class="ps-0 pb-3">Metrika megnevezése</th>
                                    <th scope="col" class="pb-3">Leírás</th>
                                    <th scope="col" class="text-end pe-0 pb-3">Számított érték</th>
                                </tr>
                            </thead>
                            <tbody class="border-top">
                                <tr>
                                    <td class="ps-0 py-3 fw-semibold text-dark">Arcarány (Ratio)</td>
                                    <td class="text-muted py-3">Magasság / Szélesség aránya</td>
                                    <td class="text-end pe-0 py-3 fw-bold text-primary fs-5">
                                        <?= number_format((float)$face_ratio, 2, '.', '') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-3 fw-semibold text-dark">Állkapocs arány (Jaw Ratio)</td>
                                    <td class="text-muted py-3">Állkapocs / Arccsont aránya</td>
                                    <td class="text-end pe-0 py-3 fw-bold text-primary fs-5">
                                        <?= number_format((float)$jaw_ratio, 2, '.', '') ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-4 fw-bold">Ajánlott stílusok neked:</h3>
    <div class="row">
        <?php if (count($recommendations) > 0): ?>
            <?php foreach ($recommendations as $hair): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="styles/<?= htmlspecialchars($hair['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($hair['style_name']) ?>" style="height: 400px; object-fit: cover; object-position: top;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($hair['style_name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($hair['description']) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($active_appointment_id): ?>
                                    <button class="btn btn-success btn-sm w-100 mb-2 save-style-btn" 
                                            data-style-id="<?= $hair['id'] ?>" 
                                            data-appointment-id="<?= $active_appointment_id ?>">
                                        Ez tetszik, küldöm a fodrásznak
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2 px-3 text-center mb-2 fs-7" style="font-size: 0.85rem;">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Kép küldéséhez aktív foglalás szükséges!
                                    </div>
                                    <a href="szalonok.php" class="btn btn-primary btn-sm w-100 mb-2">
                                        <i class="bi bi-calendar-plus"></i> Időpontot foglalok
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-secondary btn-sm w-100 mb-2">Jelentkezz be a mentéshez</a>
                            <?php endif; ?>
                            
                            <a href="arc_elemzes.php" class="btn btn-outline-primary btn-sm w-100">Vissza az elemzéshez</a>
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

<?php include 'footer.html'; ?>

<script>
document.querySelectorAll('.save-style-btn').forEach(button => {
    button.addEventListener('click', function() {
        const styleId = this.getAttribute('data-style-id');
        const appointmentId = this.getAttribute('data-appointment-id');
        const currentBtn = this;

        currentBtn.disabled = true;
        currentBtn.innerHTML = 'Küldés...';

        fetch('./api/api_save_favored_style.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                recommendation_id: styleId,
                appointment_id: appointmentId
            })
        })
        .then(async res => {
            const rawText = await res.text();
            try {
                return JSON.parse(rawText);
            } catch(e) {
                throw new Error("Szerver hiba történt!\n\nA PHP ezt a hibát küldte:\n" + rawText);
            }
        })
        .then(data => {
            if (data.status === 'success') {
                currentBtn.classList.remove('btn-success');
                currentBtn.classList.add('btn-secondary');
                currentBtn.innerHTML = 'Elküldve a fodrásznak!';
            } else {
                alert(data.message || 'Hiba történt.');
                currentBtn.disabled = false;
                currentBtn.innerHTML = 'Ez tetszik, küldöm a fodrásznak';
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message);
            currentBtn.disabled = false;
            currentBtn.innerHTML = 'Ez tetszik, küldöm a fodrásznak';
        });
    });
});
</script>
</body>
</html>