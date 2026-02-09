<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php"); exit;
}

$owner_id = $_SESSION['user_id'];

// Szalon azonosítása
$stmt = $pdo->prepare("SELECT salon_id, name FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();

if (!$salon) {
    die("Hiba: Önhöz nincs szalon rendelve!");
}

$salon_id = $salon['salon_id'];


$filter_date = isset($_GET['today']) ? date('Y-m-d') : null;

$sql = "SELECT a.*, u.username, s.name AS service_name 
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



// Statisztika: Szolgáltatások népszerűsége
$statStmt = $pdo->prepare("
    SELECT s.name, COUNT(a.appointment_id) as db 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.salon_id = ? AND a.status != 'cancelled'
    GROUP BY s.service_id
");
$statStmt->execute([$salon_id]);
$stats = $statStmt->fetchAll();

// Átalakítás JavaScript formátumba
$labels = [];
$counts = [];
foreach ($stats as $row) {
    $labels[] = $row['name'];
    $counts[] = $row['db'];
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Tulajdonos Kezelőfelület - <?php echo htmlspecialchars($salon['name']); ?></title>
</head>
<body class="bg-light">

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
            <a href="owner_appointments.php?today=1" class="btn btn-outline-info me-2">
                <i class="bi bi-filter"></i> Mai vendégek
            </a>
            <a href="owner_appointments.php" class="btn btn-outline-secondary me-2">
                Összes
            </a>
            <a href="manual_booking.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Új foglalás rögzítése
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            if($_GET['msg'] == 'cancelled_success') echo "Foglalás sikeresen törölve és a várólista értesítve!";
            else echo "Művelet sikeresen végrehajtva!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Dátum</th>
                        <th>Időpont</th>
                        <th>Vendég</th>
                        <th>Szolgáltatás</th>
                        <th>Állapot</th>
                        <th class="text-end pe-3">Műveletek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Nincs talált foglalás.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td class="ps-3"><?php echo $app['appointment_date']; ?></td>
                            <td><strong><?php echo date('H:i', strtotime($app['appointment_time'])); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['username']); ?></td>
                            <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                            <td>
                                <?php
                                $badgeClass = 'bg-primary';
                                if ($app['status'] == 'completed') $badgeClass = 'bg-success';
                                if ($app['status'] == 'cancelled') $badgeClass = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo $app['status']; ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="edit_appointment.php?id=<?php echo $app['appointment_id']; ?>" class="btn btn-sm btn-warning" title="Szerkesztés">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($app['status'] !== 'cancelled'): ?>
                                    <a href="lemondas.php?id=<?php echo $app['appointment_id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Biztosan törölni szeretnéd ezt a foglalást és értesíteni a várólistát?')">
                                        <i class="bi bi-trash"></i> Törlés
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <br>
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <div class="card shadow border-0">
                        <div class="card-header bg-white fw-bold">Népszerű szolgáltatások (arány)</div>
                        <div class="card-body">
                            <canvas id="servicesChart" style="max-height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('servicesChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie', // Lehetne 'bar' (oszlop) is
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: [
                    '#36A2EB', '#FF6384', '#FFCE56', '#31572c', '#9966FF', '#FF9F40'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
</body>
</html>