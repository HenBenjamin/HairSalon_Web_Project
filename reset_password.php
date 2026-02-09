<?php
session_start();
require_once "config.php";

$message = "";
$messageClass = "";
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Ellenőrizzük a tokent és a lejárati időt (most < lejárati idő)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $showForm = true; // Ha érvényes a token, mutathatjuk az űrlapot

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Jelszó frissítése, token törlése
                $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE user_id = ?");
                if ($update->execute([$hashedPassword, $user['user_id']])) {
                    $message = "Sikeres jelszómódosítás! Most már bejelentkezhetsz.";
                    $messageClass = "alert-success";
                    $showForm = false; // Sikeres mentés után elrejtjük a formot
                }
            } else {
                $message = "A két jelszó nem egyezik meg!";
                $messageClass = "alert-danger";
            }
        }
    } else {
        $message = "A link érvénytelen vagy lejárt.";
        $messageClass = "alert-danger";
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Új jelszó megadása</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow p-4">
                <h3 class="text-center mb-4">Új jelszó beállítása</h3>

                <?php if ($message): ?>
                    <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($showForm): ?>
                    <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Új jelszó:</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Új jelszó megerősítése:</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Jelszó mentése</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">Vissza a bejelentkezéshez</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>