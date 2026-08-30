<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";
$message_type = "success";

if (isset($_GET['toggle_active'])) {
    $salon_id = (int)$_GET['toggle_active'];
    $current_status = (int)$_GET['status'];
    $new_status = ($current_status === 1) ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE salons SET active = ? WHERE salon_id = ?");
    $stmt->execute([$new_status, $salon_id]);
    header("Location: admin_salons.php?msg=status_updated");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_salon'])) {
    $name = trim($_POST['salon_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $owner_id = $_POST['owner_id'];
    
    $image_name = "default_salon.jpg"; 

    if (isset($_FILES['salon_image']) && $_FILES['salon_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['salon_image']['tmp_name'];
        $original_name = basename($_FILES['salon_image']['name']);
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // Biztonsági ellenőrzés: csak engedélyezett képformátumok
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            
            $target_dir = "images/"; 
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $target_file = $target_dir . $image_name;
            
            if (!move_uploaded_file($file_tmp, $target_file)) {
                $message = "Hiba történt a kép szerverre mentése során.";
                $message_type = "danger";
                $image_name = "default_salon.jpg";
            }
        } else {
            $message = "Nem megfelelő fájlformátum! Csak JPG, JPEG, PNG és WEBP megengedett.";
            $message_type = "danger";
        }
    }

    if (!empty($name) && !empty($owner_id) && $message_type !== "danger") {
        $stmt = $pdo->prepare("INSERT INTO salons (name, owner_id, address, city, phone, active, image_url) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([$name, $owner_id, $address, $city, $phone, $image_name]);

        // Tulajdonos rang beállítása
        $update = $pdo->prepare("UPDATE users SET role = 'owner' WHERE user_id = ?");
        $update->execute([$owner_id]);

        $message = "Szalon sikeresen létrehozva képpel együtt!";
        $message_type = "success";
    }
}

$salons = $pdo->query("SELECT s.*, u.username FROM salons s JOIN users u ON s.owner_id = u.user_id ORDER BY s.name ASC")->fetchAll();

$potential_owners = $pdo->query("SELECT user_id, username FROM users WHERE role = 'owner'")->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Admin - Szalonok Kezelése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2 family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">Új szalon regisztrálása</div>
                <div class="card-body">
                    <?php if($message) echo "<div class='alert alert-$message_type small'>$message</div>"; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="small fw-bold">Szalon neve</label>
                            <input type="text" name="salon_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Város</label>
                            <input type="text" name="city" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Cím</label>
                            <input type="text" name="address" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Telefon</label>
                            <input type="text" name="phone" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Szalon képe</label>
                            <input type="file" name="salon_image" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Tulajdonos hozzárendelése</label>
                            <select name="owner_id" class="form-select form-select-sm">
                                <?php foreach($potential_owners as $po): ?>
                                    <option value="<?= $po['user_id'] ?>"><?= htmlspecialchars($po['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="create_salon" class="btn btn-primary btn-sm w-100">Létrehozás</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between">
                    <span>Regisztrált szalonok</span>
                    <small>Összesen: <?= count($salons) ?></small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0"> <thead class="table-light">
                        <tr>
                            <th>Kép / Név / Város</th>
                            <th>Tulajdonos</th>
                            <th class="text-center">Állapot</th>
                            <th class="text-end px-3">Művelet</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($salons as $s): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="images/<?= !empty($s['image_url']) ? htmlspecialchars($s['image_url']) : 'default_salon.jpg' ?>" 
                                             alt="szalon" 
                                             class="rounded me-3 shadow-sm" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($s['city']) ?>, <?= htmlspecialchars($s['address']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($s['username']) ?></td>
                                <td class="text-center">
                                    <?php if ($s['active']): ?>
                                        <span class="badge bg-success">Megjelenik</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejtett</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-3">
                                    <a href="admin_salons.php?toggle_active=<?= $s['salon_id'] ?>&status=<?= $s['active'] ?>"
                                       class="btn btn-sm <?= $s['active'] ? 'btn-outline-danger' : 'btn-success' ?>">
                                         <?= $s['active'] ? 'Deaktiválás' : 'Aktiválás' ?>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>