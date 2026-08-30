<?php
require_once "config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$successMessage = "";
$errorMessage = "";

$stmt = $pdo->prepare("SELECT username, profile_pic, mobilenumber FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Felhasználó nem található!");
}

// 2. űrlapok feldolgozása
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- a) profil adatok mentése (jelszó nélkül) ---
    if (isset($_POST['action']) && $_POST['action'] === 'save_profile') {
        $username = trim($_POST['username']);
        $mobilenumber = trim($_POST['mobilenumber']);

        if (empty($username) || empty($mobilenumber)) {
            $errorMessage = "A felhasználónév és a mobiltelefonszám kitöltése kötelező!";
        } else {
            // Profilkép feltöltés kezelése
            $imageName = $user['profile_pic'];
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                $fileName = $_FILES['profile_pic']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = "avatar_" . $userId . "_" . time() . "." . $fileExtension;
                    $uploadFileDir = './images/profile_pics/';

                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                        if (!empty($user['profile_pic']) && file_exists($uploadFileDir . $user['profile_pic'])) {
                            unlink($uploadFileDir . $user['profile_pic']);
                        }
                        $imageName = $newFileName;
                        $_SESSION['profile_pic'] = $newFileName;
                    } else {
                        $errorMessage = "Hiba történt a kép mentése során.";
                    }
                } else {
                    $errorMessage = "Csak JPG, JPEG, PNG és WEBP formátumú képek engedélyezettek!";
                }
            }

            // Frissítés az adatbázisban
            if (empty($errorMessage)) {
                try {
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, mobilenumber = ?, profile_pic = ? WHERE user_id = ?");
                    if ($updateStmt->execute([$username, $mobilenumber, $imageName, $userId])) {
                        $_SESSION['username'] = $username;
                        $user['username'] = $username;
                        $user['mobilenumber'] = $mobilenumber;
                        $user['profile_pic'] = $imageName;
                        $successMessage = "A profiladatok sikeresen frissültek!";
                    }
                } catch (PDOException $e) {
                    $errorMessage = "Adatbázis hiba történt a mentés során.";
                }
            }
        }
    }

    // --- b) jelszó módosítás ---
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = "Minden jelszómező kitöltése kötelező!";
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = "An új jelszó és a megerősítés nem egyezik!";
        } elseif (strlen($newPassword) < 6) {
            $errorMessage = "Az új jelszónak legalább 6 karakterből kell állnia!";
        } else {
            // Jelenlegi jelszó ellenőrzése
            $pwdStmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $pwdStmt->execute([$userId]);
            $passwordHash = $pwdStmt->fetchColumn();

            if (!password_verify($currentPassword, $passwordHash)) {
                $errorMessage = "A megadott jelenlegi jelszó hibás!";
            } else {
                // Új jelszó mentése
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePwdStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($updatePwdStmt->execute([$newHash, $userId])) {
                    $successMessage = "A jelszavad sikeresen megváltozott!";
                } else {
                    $errorMessage = "Nem sikerült frissíteni a jelszót.";
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Szerkesztése - HairSalon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <div class="container my-5 flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="card shadow border-0 p-4" style="max-width: 500px; width: 100%;">
            <div class="card-body">
                
                <h2 class="card-title text-center fw-bold mb-4"><i class="fa-solid fa-user"></i> Profil Beállítások</h2>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $successMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $errorMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="profil_szerkesztes.php" method="POST" enctype="multipart/form-data" novalidate class="mb-4">
                    <input type="hidden" name="action" value="save_profile">
                    
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 100px; height: 100px; overflow: hidden;">
                            <?php if (!empty($user['profile_pic']) && file_exists('./images/profile_pics/' . $user['profile_pic'])): ?>
                                <img src="./images/profile_pics/<?php echo $user['profile_pic']; ?>" id="avatarPreview" alt="Profilkép" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fa-solid fa-user text-white" id="avatarIcon" style="font-size: 3.5rem;"></i>
                                <img src="" id="avatarPreview" alt="Profilkép" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            <?php endif; ?>
                        </div>
                        <div class="mt-1">
                            <label for="profile_pic" class="btn btn-sm btn-outline-secondary">Új fotó feltöltése</label>
                            <input type="file" name="profile_pic" id="profile_pic" class="form-control d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Felhasználónév</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-regular fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Telefonszám</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-mobile-screen-button"></i></span>
                            <input type="tel" class="form-control" id="phone" name="mobilenumber" value="<?php echo htmlspecialchars($user['mobilenumber'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-szalon-foglalas btn-lg">Adatok mentése</button>
                    </div>
                </form>

                <hr class="my-4 text-muted">

                <form action="profil_szerkesztes.php" method="POST" novalidate>
                    <input type="hidden" name="action" value="change_password">
                    
                    <h4 class="fw-bold mb-3 text-muted" style="font-size: 1.1rem;"><i class="fa-solid fa-key me-2"></i>Jelszó megváltoztatása</h4>

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Jelenlegi jelszó</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">Új jelszó</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-lock-open"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Új jelszó újra</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-check-double"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-danger btn-lg">Jelszó frissítése</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-2 mt-auto">
        <?php include 'footer.html'; ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('avatarPreview');
                var icon = document.getElementById('avatarIcon');
                
                if(preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if(icon) {
                    icon.style.display = 'none';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>