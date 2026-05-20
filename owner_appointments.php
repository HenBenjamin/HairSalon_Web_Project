<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php"); exit;
}

$owner_id = $_SESSION['user_id'];

// --- ÚJ RÉSZ: FOGLALÁS LEZÁRÁSA ÉS PONTGYŰJTÉS ---
// --- MÓDOSÍTOTT RÉSZ: FOGLALÁS LEZÁRÁSA ÉS ALAPÁR MENTÉSE ---
if (isset($_GET['complete_id'])) {
    $app_id = $_GET['complete_id'];

    // 1. Megkeressük a foglalást és a szolgáltatás ALAPÁRÁT is
    $stmt = $pdo->prepare("
        SELECT a.user_id, a.final_price, s.points, s.price 
        FROM appointments a 
        JOIN services s ON a.service_id = s.service_id 
        WHERE a.appointment_id = ? AND a.status = 'booked'
    ");
    $stmt->execute([$app_id]);
    $data = $stmt->fetch();

    if ($data) {
        $u_id = $data['user_id'];
        $earned_points = $data['points'];
        // HA már van final_price (kupon beváltva), megtartjuk. 
        // HA nincs, akkor az alapárat használjuk.
        $price_to_save = (!empty($data['final_price'])) ? $data['final_price'] : $data['price'];

        $pdo->beginTransaction();
        try {
            // A. Státusz átírása és a megfelelő ár rögzítése
            $updateStatus = $pdo->prepare("
                UPDATE appointments 
                SET status = 'completed', final_price = ? 
                WHERE appointment_id = ?
            ");
            $updateStatus->execute([$price_to_save, $app_id]);

            // B. Pontok jóváírása
            $addPoints = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE user_id = ?");
            $addPoints->execute([$earned_points, $u_id]);

            // C. Új kupon ellenőrzése (100 pont után)
            $checkTotal = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
            $checkTotal->execute([$u_id]);
            $current_total = $checkTotal->fetchColumn();

            if ($current_total >= 100) {
                $couponCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                $insC = $pdo->prepare("INSERT INTO coupons (user_id, code, discount_amount) VALUES (?, ?, ?)");
                $insC->execute([$u_id, $couponCode, 20]);
                $pdo->prepare("UPDATE users SET total_points = total_points - 100 WHERE user_id = ?")->execute([$u_id]);
            }

            $pdo->commit();
            header("Location: owner_appointments.php?msg=completed_success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Hiba: " . $e->getMessage());
        }
    }
}
// --- ÚJ RÉSZ VÉGE ---

// Szalon azonosítása (eredeti kódod folytatódik)
$stmt = $pdo->prepare("SELECT salon_id, name FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();

if (!$salon) {
    die("Hiba: Önhöz nincs szalon rendelve!");
}

$salon_id = $salon['salon_id'];

// ... (többi lekérdezésed változatlanul hagyva) ...
$filter_date = isset($_GET['today']) ? date('Y-m-d') : null;
$sql = "SELECT a.*, u.username, s.name AS service_name, s.price 
        FROM appointments a
        JOIN users u ON a.user_id = u.user_id
        JOIN services s ON a.service_id = s.service_id
        WHERE a.salon_id = ?";
$params = [$salon_id];
if ($filter_date) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $filter_date;
}
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Statisztika (eredeti kódod...)
$statStmt = $pdo->prepare("
    SELECT s.name, COUNT(a.appointment_id) as db 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.salon_id = ? AND a.status != 'cancelled'
    GROUP BY s.service_id
");
$statStmt->execute([$salon_id]);
$stats = $statStmt->fetchAll();
$labels = []; $counts = [];
foreach ($stats as $row) { $labels[] = $row['name']; $counts[] = $row['db']; }

$revenueStmt = $pdo->prepare("
    SELECT DATE_FORMAT(appointment_date, '%Y-%m') as honap, SUM(final_price) as osszeg 
    FROM appointments 
    WHERE salon_id = ? AND status = 'completed'
    GROUP BY honap 
    ORDER BY honap ASC 
    LIMIT 6
");
$revenueStmt->execute([$salon_id]);
$revenueStats = $revenueStmt->fetchAll();

$revLabels = []; $revData = [];
foreach ($revenueStats as $row) { 
    $revLabels[] = $row['honap']; 
    $revData[] = $row['osszeg']; 
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Kezelőfelület</title>
</head>
<body class="bg-light">

<!-- <?php include "navbar.php"; ?> <div class="container mt-5"> -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Szalon Kezelés</a>
            <div class="navbar-nav">
                <a class="nav-link" href="services.php">Szolgáltatások</a>
                <a class="nav-link" href="working_hours.php">Nyitvatartás</a>
                <a class="nav-link" href="owner_appointments.php">Időpontok kezelése</a>
                <a class="nav-link text-danger" href="logout.php">Kijelentkezés</a>
            </div>
        </div>
    </nav>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-calendar-check"></i> Foglalások kezelése</h2>
            <p class="text-muted"><?php echo htmlspecialchars($salon['name']); ?></p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="owner_appointments.php?today=1" class="btn btn-outline-info me-2">Mai</a>
            <a href="owner_appointments.php" class="btn btn-outline-secondary">Összes</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
                if($_GET['msg'] == 'completed_success') echo "Sikeres lezárás! A pontok jóváírva.";
                else echo "Művelet sikeres!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <!-- Ez meg nem biztos hogy kell ide, de majd meglátjuk... :) -->

<?php if (isset($_GET['coupon_msg'])): ?>
    <div class="alert alert-<?php echo ($_GET['type'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi <?php echo ($_GET['type'] == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
        <?php echo htmlspecialchars($_GET['coupon_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


 <div class="card shadow-sm border-primary">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-ticket-perforated"></i> Kupon beváltása</h5>
        <form action="redeem_coupon.php" method="POST">
            <div class="mb-2">
                <select name="appointment_id" class="form-select" required>
                    <option value="">Válassz vendéget/időpontot</option>
                    <?php foreach ($appointments as $app): ?>
                        <?php if ($app['status'] == 'booked'): ?>
                            <option value="<?php echo $app['appointment_id']; ?>">
                                <?php echo $app['appointment_date'] . " " . $app['appointment_time'] . " - " . htmlspecialchars($app['username']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-2">
                <input type="text" name="coupon_code" class="form-control" placeholder="Kuponkód.." required>
                <button type="submit" class="btn btn-primary">Beváltás</button>
            </div>
        </form>
    </div>
</div>

<!-- Itt a vege -->

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Dátum</th>
                        <th>Időpont</th>
                        <th>Vendég</th>
                        <th>Szolgáltatás</th>
                        <th>Ár</th>
                        <th>Állapot</th>
                        <th class="text-end">Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td><?php echo $app['appointment_date']; ?></td>
                        <td><strong><?php echo date('H:i', strtotime($app['appointment_time'])); ?></strong></td>
                        <td><?php echo htmlspecialchars($app['username']); ?></td>
                        <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                        <td>
                            <div class="fw-bold">
                                <?php 
                                if (!empty($app['final_price']) && $app['final_price'] < $app['price']): 
                                ?>
                                    <span class="text-danger text-decoration-line-through small me-1">
                                        <?php echo $app['price']; ?> din
                                    </span> 
                                    <span class="text-success">
                                        <?php echo $app['final_price']; ?> din
                                    </span>
                                    <i class="bi bi-tag-fill text-success ms-1" title="Kupon felhasználva"></i>

                                <?php else: ?>
                                    <span class="text-dark">
                                        <?php echo $app['price']; ?> din
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $badgeClass = 'bg-primary'; // booked
                            if ($app['status'] == 'completed') $badgeClass = 'bg-success';
                            if ($app['status'] == 'cancelled') $badgeClass = 'bg-danger';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $app['status']; ?></span>
                        </td>
                        <td class="text-end pe-3">
                            <?php if ($app['status'] == 'booked'): ?>
                                <a href="owner_appointments.php?complete_id=<?php echo $app['appointment_id']; ?>" 
                                   class="btn btn-sm btn-success" 
                                   onclick="return confirm('Vendég megérkezett? Pontok jóváírása és lezárás...')"
                                   title="Lezárás és pontadás">
                                    <i class="bi bi-check-lg"></i> Kész
                                </a>
                            <?php endif; ?>

                            <a href="edit_appointment.php?id=<?php echo $app['appointment_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            <?php if ($app['status'] !== 'cancelled'): ?>
                                <a href="lemondas.php?id=<?php echo $app['appointment_id']; ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    </div>


    <div class="row mt-5 mb-5">
    <div class="col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white fw-bold">Népszerű szolgáltatások (arány)</div>
            <div class="card-body">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white fw-bold">Havi bevétel (din)</div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. Szolgáltatás népszerűségi grafikon
    const ctxServices = document.getElementById('servicesChart').getContext('2d');
    new Chart(ctxServices, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: ['#0d6efd', '#ec23d5', '#ffc107', '#198754', '#0dcaf0']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. Havi bevételi grafikon
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($revLabels); ?>,
            datasets: [{
                label: 'Bevétel (din)',
                data: <?php echo json_encode($revData); ?>,
                backgroundColor: '#198754'
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
</body>
</html>