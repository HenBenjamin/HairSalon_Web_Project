<?php
session_start();
require_once "config.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Biztonsági ellenőrzés
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// 2. Új szalontulajdonos létrehozása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_owner'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $username = explode('@', $email)[0];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // E-mail egyediségének ellenőrzése beszúrás előtt
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetchColumn() > 0) {
        $error = "Hiba: Ezzel az e-mail címmel ('$email') már regisztráltak felhasználót!";
    } else {
        $activation_token = bin2hex(random_bytes(16));

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_active, activation_token) VALUES (?, ?, ?, 'owner', 1, ?)");

            if ($stmt->execute([$username, $email, $hashed_password, $activation_token])) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'sandbox.smtp.mailtrap.io';
                    $mail->SMTPAuth = true;
                    $mail->Port = 587;
                    $mail->Username = 'cdea5d5a5f0a76';
                    $mail->Password = '5e6b961416d809';
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('admin@hairsalon.hu', 'HairSalon Admin');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Szalontulajdonosi hozzaferes';
                    $mail->Body    = "Udvozoljuk!<br><br>On szalontulajdonosi jogot kapott.<br>Email: $email<br>Jelszo: $password";

                    $mail->send();
                    $success = "Tulajdonos sikeresen létrehozva és az e-mail elküldve.";
                } catch (Exception $e) {
                    $success = "Tulajdonos létrehozva az adatbázisban, de az e-mail küldése sikertelen. Hiba: " . $mail->ErrorInfo;
                }
            }
        } catch (PDOException $e) {
            $error = "Hiba az adatbázisban: " . $e->getMessage();
        }
    }
}

// 3. Felhasználó törlése, aktiválása és blokkolása
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
        $stmt->execute([$delete_id]);
        $success = "Felhasználó törölve.";
    }
}

if (isset($_GET['activate'])) {
    $activate_id = (int)$_GET['activate'];
    $pdo->prepare("UPDATE users SET is_active = 1, activation_token = '' WHERE user_id = ?")->execute([$activate_id]);
    $success = "Felhasználó aktiválva.";
}

if (isset($_GET['toggle_block'])) {
    $target_id = (int)$_GET['toggle_block'];
    $new_status = (int)$_GET['status'];
    
    if ($target_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_blocked = ? WHERE user_id = ? AND role != 'admin'");
        $stmt->execute([$new_status, $target_id]);
        $success = $new_status ? "Felhasználó letiltva." : "Felhasználó feloldva.";
    }
}

// 4. Adatok lekérése
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$logs = $pdo->query("SELECT l.*, u.username FROM visitor_logs l LEFT JOIN users u ON l.user_id = u.user_id ORDER BY l.login_time DESC")->fetchAll();
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HairSalon Admin - Vezérlőpult</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

<div class="container py-4">

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                <i class="fas fa-users me-1"></i> Felhasználók
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#registerOwner" type="button" role="tab">
                <i class="fa-solid fa-user-tie me-1"></i> Új szalontulajdonos
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                <i class="fas fa-history me-1"></i> Belépési napló (IP detektálás)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="table-container">
                <h4 class="mb-4"><i class="fas fa-users-config me-2"></i>Rendszer felhasználói</h4>
                <table id="usersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Név / Email</th>
                            <th>Szerepkör</th>
                            <th>Állapot</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($u['username']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?= strtoupper($u['role']) ?></span></td>
                            <td>
                                <?php if ($u['is_blocked']): ?>
                                    <span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Tiltva</span>
                                <?php else: ?>
                                    <?= $u['is_active'] ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Aktív</span>' : '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Inaktív</span>' ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$u['is_active'] && !$u['is_blocked']): ?>
                                    <a href="admin_users.php?activate=<?= $u['user_id'] ?>" class="btn btn-sm btn-success me-1">
                                        <i class="fas fa-user-check"></i> Aktiválás
                                    </a>
                                <?php endif; ?>

                                <?php if ($u['role'] !== 'admin'): ?>
                                    <?php if ($u['is_blocked']): ?>
                                        <a href="admin_users.php?toggle_block=<?= $u['user_id'] ?>&status=0" class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-unlock"></i> Feloldás
                                        </a>
                                    <?php else: ?>
                                        <a href="admin_users.php?toggle_block=<?= $u['user_id'] ?>&status=1" class="btn btn-sm btn-outline-danger me-1">
                                            <i class="fas fa-user-slash"></i> Letiltás
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($u['role'] !== 'admin'): ?>
                                    <a href="admin_users.php?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Biztosan törölni szeretné ezt a felhasználót?')">
                                        <i class="fas fa-trash-alt"></i> Törlés
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="registerOwner" role="tabpanel">
            <div class="table-container">
                <h4 class="mb-4"><i class="fa-solid fa-user-tie me-2"></i>Új szalontulajdonos regisztrálása</h4>
                <form method="POST" class="row g-3">
                    <div class="col-md-5">
                        <label for="email" class="form-label"><i class="fa-regular fa-envelope"></i> E-mail cím</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                         <label for="password" class="form-label"><i class="fa-solid fa-lock"></i> Ideiglenes Jelszó</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                         <button type="submit" name="add_owner" class="btn btn-primary w-100">Létrehozás</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-pane fade" id="logs" role="tabpanel">
            <div class="table-container">
                <h4 class="mb-4"><i class="fas fa-history me-2"></i>Biztonsági Belépési Napló</h4>
                <table id="logsTable" class="table table-sm table-hover w-100">
                    <thead class="table-dark">
                    <tr>
                        <th>Felhasználó</th>
                        <th>IP Cím</th>
                        <th>Város</th>
                        <th>Eszköz</th>
                        <th>Időpont</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($l['username'] ?? 'Ismeretlen') ?></strong></td>
                            <td><code><?= $l['ip_address'] ?></code></td>
                            <td><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($l['city']) ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-<?= $l['device_type'] == 'pc' ? 'desktop' : ($l['device_type'] == 'mobile' ? 'mobile-alt' : 'tablet-alt') ?> me-1"></i>
                                    <?= strtoupper($l['device_type']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($l['login_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/hu.json" },
            "pageLength": 10
        });

        $('#logsTable').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/hu.json" },
            "order": [[4, "desc"]],
            "pageLength": 15
        });
    });
</script>

</body>
</html>