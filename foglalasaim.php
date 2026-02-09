<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ha az időpont régebbi a mostaninál és még 'booked', állítsuk 'completed'-re
$updateSql = "UPDATE appointments 
              SET status = 'completed' 
              WHERE status = 'booked' 
              AND (appointment_date < CURDATE() 
              OR (appointment_date = CURDATE() AND appointment_time < CURTIME()))";
$pdo->query($updateSql);

// Lekérdezés az appointments, salons és services táblákból
$stmt = $pdo->prepare("
    SELECT 
        a.appointment_date, 
        a.appointment_time, 
        a.status, 
        s.name AS salon_name,
        ser.name AS service_name,
        ser.price
    FROM appointments a
    JOIN salons s ON a.salon_id = s.salon_id
    JOIN services ser ON a.service_id = ser.service_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Foglalasaim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<?php include 'navbar.php'; ?>
<main class="container mt-5 flex-grow-1">
    <h2 class="mb-4">Időpont foglalási archívum</h2>

    <?php if (count($appointments) > 0): ?>
        <div class="table-responsive bg-white p-3 shadow-sm rounded">
            <table class="table table-hover">
                <thead class="table-light">
                <tr>
                    <th>Szalon</th>
                    <th>Szolgáltatás</th>
                    <th>Dátum és Idő</th>
                    <th>Ár</th>
                    <th>Állapot</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($a['salon_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($a['service_name']); ?></td>
                        <td>
                            <?php
                            echo date("Y.m.d", strtotime($a['appointment_date'])) . " " .
                                date("H:i", strtotime($a['appointment_time']));
                            ?>
                        </td>
                        <td><?php echo number_format($a['price'], 0, ',', ' '); ?> RSD</td>
                        <td>
                            <?php
                            // Szín és szöveg meghatározása
                            switch($a['status']) {
                                case 'booked':
                                    $badgeClass = 'bg-primary';
                                    $statusText = 'Lefoglalva';
                                    break;
                                case 'completed':
                                    $badgeClass = 'bg-success';
                                    $statusText = 'Teljesítve';
                                    break;
                                case 'cancelled':
                                    $badgeClass = 'bg-danger';
                                    $statusText = 'Lemondva';
                                    break;
                                default:
                                    $badgeClass = 'bg-secondary';
                                    $statusText = $a['status'];
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?> p-2">
                <?php echo $statusText; ?>
            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Még nincs rögzített időpontod.</div>
    <?php endif; ?>
</main>
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
