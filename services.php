<?php
session_start();
require_once "config.php";

// Csak a tulajdonos (owner) férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];

// Megkeressük az owner szalonját
$stmt = $pdo->prepare("SELECT salon_id, name FROM salons WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$salon = $stmt->fetch();

if (!$salon) {
    die("Hiba: Önhöz még nincs szalon rendelve! Kérje az Admin segítségét.");
}

$salon_id = $salon['salon_id'];

// --- törlés kezelése ---
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    // Biztonsági ellenőrzés: csak a saját szalonjából törölhet
    $del = $pdo->prepare("DELETE FROM services WHERE service_id = ? AND salon_id = ?");
    $del->execute([$del_id, $salon_id]);
    header("Location: services.php?msg=deleted");
    exit;
}

// Új szolgáltatás hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $duration = (int)$_POST['duration'];

    if (!empty($name) && $price > 0 && $duration > 0) {
        $ins = $pdo->prepare("INSERT INTO services (salon_id, name, price, duration) VALUES (?, ?, ?, ?)");
        $ins->execute([$salon_id, $name, $price, $duration]);
        $message = "Szolgáltatás hozzáadva!";
    }
}

// Szolgáltatások listázása
$stmt = $pdo->prepare("SELECT * FROM services WHERE salon_id = ?");
$stmt->execute([$salon_id]);
$services = $stmt->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szolgáltatások kezelése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav>
        <?php include 'navbar.php'; ?>
    </nav>
<div class="container my-5 flex-grow-1">
    <h2><?php echo htmlspecialchars($salon['name']); ?> - Szolgáltatások</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Új szolgáltatás</h5>
                <form method="post">
                    <div class="mb-2">
                        <label>Név (pl. Férfi hajvágás)</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Ár (Dinár)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Időtartam (perc)</label>
                        <input type="number" name="duration" class="form-control" required>
                    </div>
                    <button type="submit" name="add_service" class="btn btn-szalon-foglalas btn-primary w-100">Mentés</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h5>Már felvitt szolgáltatások</h5>
                <table class="table">
                    <thead>
                    <tr>
                        <th><i class="fa-solid fa-scissors"></i> Szolgáltatás</th>
                        <th><i class="fa-solid fa-dollar-sign"></i> Ár</th>
                        <th><i class="fa-regular fa-clock"></i> Idő</th>
                        <th class="text-end"><i class="fa-regular fa-pen-to-square"></i> Műveletek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($services as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo number_format($s['price'], 0, ',', ' '); ?> Din</td>
                            <td><?php echo $s['duration']; ?> perc</td>
                            <td class="text-end">
                                <a href="service_edit.php?id=<?php echo $s['service_id']; ?>"
                                   class="btn btn-sm btn-warning me-1">
                                    Szerkesztés
                                </a>
                                <a href="#" 
                                    class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal" 
                                    data-id="<?php echo $s['service_id']; ?>">
                                    Törlés
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Biztosan törlöd?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                A szolgáltatás törlése után az adatokat nem tudod visszaállítani. Biztosan folytatod?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Igen, törlöm</a>
            </div>
        </div>
    </div>
</div>
<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>
<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.href = 'services.php?delete_id=' + id;
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>