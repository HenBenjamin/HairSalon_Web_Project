<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment_id'])) {
    $cancel_id = $_POST['cancel_appointment_id'];

    // Biztonsági ellenőrzés: Csak a saját, jövőbeli és 'booked' státuszú időpontokat lehet lemondani
    $cancelStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'cancelled' 
        WHERE appointment_id = ? 
          AND user_id = ? 
          AND status = 'booked'
          AND (appointment_date > CURDATE() 
          OR (appointment_date = CURDATE() AND appointment_time > CURTIME()))
    ");
    $cancelStmt->execute([$cancel_id, $user_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.appointment_id, 
        a.appointment_date, 
        a.appointment_time, 
        a.status, 
        a.final_price,
        s.name AS salon_name,
        ser.name AS service_name,
        ser.price AS original_price
    FROM appointments a
    JOIN salons s ON a.salon_id = s.salon_id
    JOIN services ser ON a.service_id = ser.service_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();

$current_date = date('Y-m-d');
$current_time = date('H:i:s');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Foglalasaim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<?php include 'navbar.php'; ?>
<main class="container my-5 flex-grow-1">
    <h2 class="mb-4">Időpont foglalási archívum</h2>

    <?php if (count($appointments) > 0): ?>
        <div class="table-responsive bg-white p-3 shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th><i class="fa-regular fa-building"></i> Szalon</th>
                    <th><i class="fa-solid fa-scissors"></i> Szolgáltatás</th>
                    <th><i class="fa-regular fa-calendar"></i> Dátum és Idő</th>
                    <th><i class="fa-solid fa-dollar-sign"></i> Ár</th>
                    <th><i class="fa-solid fa-list"></i> Állapot</th>
                    <th><i class="fa-solid fa-gear"></i> Művelet</th>
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
                        <td>
                            <?php if (!empty($a['final_price']) && $a['final_price'] < $a['original_price']): ?>
                                <span class="text-danger text-decoration-line-through small me-1">
                                    <?php echo number_format($a['original_price'], 0, ',', ' '); ?> RSD
                                </span>
                                <span class="text-success fw-bold">
                                    <?php echo number_format($a['final_price'], 0, ',', ' '); ?> RSD
                                </span>
                                <i class="bi bi-tag-fill text-success ms-1" title="Kupon felhasználva"></i>
                            <?php else: ?>
                                <span class="text-dark">
                                    <?php echo number_format($a['original_price'], 0, ',', ' '); ?> RSD
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
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
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?> p-2">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $is_future = ($a['appointment_date'] > $current_date) || 
                                         ($a['appointment_date'] == $current_date && $a['appointment_time'] > $current_time);
                            
                            if ($a['status'] === 'booked' && $is_future): 
                            ?>
                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm open-cancel-modal" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cancelModal" 
                                        data-id="<?php echo $a['appointment_id']; ?>">
                                    <i class="fa-solid fa-trash-can"></i> Lemondás
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
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

<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="cancelModalLabel"><i class="fa-solid fa-triangle-exclamation"></i> Időpont lemondása</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Biztosan le szeretnéd mondani ezt az időpontot? Ez a folyamat nem vonható vissza.
            </div>
            <div class="modal-footer">
                <form action="" method="POST" id="cancelForm">
                    <input type="hidden" name="cancel_appointment_id" id="modal_appointment_id" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="submit" class="btn btn-danger">Igen, lemondom</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cancelButtons = document.querySelectorAll('.open-cancel-modal');
    const modalInput = document.getElementById('modal_appointment_id');

    cancelButtons.forEach(button => {
        button.addEventListener('click', function () {
            const appointmentId = this.getAttribute('data-id');
            modalInput.value = appointmentId;
        });
    });
});
</script>
</body>
</html>