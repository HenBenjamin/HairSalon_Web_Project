<?php
session_start();
require_once "config.php";

$error = '';

// Csak akkor fut le, ha a formot beküldték
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // 1. Szerver oldali alap validáció
    if (empty($email) || empty($password)) {
        $error = "Kérjük, töltse ki mindkét mezőt!";
    } else {
        try {
            // 2. Felhasználó lekérése (is_active és role is kell!)
            $stmt = $pdo->prepare("SELECT user_id, username, email, password, role, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // 3. Aktiváció ellenőrzése
                if ($user['is_active'] == 0) {
                    $error = "A fiókja még nincs aktiválva! Ellenőrizze az e-mailjeit.";
                }
                // 4. Jelszó ellenőrzése (bcrypt)
                elseif (password_verify($password, $user["password"])) {
                    // SIKER - Munkamenet indítása
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["role"] = $user["role"];

                    if ($user["role"] === 'admin') {
                        header("Location: admin_users.php");
                    } elseif ($user["role"] === 'owner') {
                        header("Location: services.php"); // Vagy egy tulajdonosi dashboardra
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = "Hibás e-mail cím vagy jelszó.";
                }
            } else {
                $error = "Hibás e-mail cím vagy jelszó.";
            }
        } catch (PDOException $e) {
            $error = "Hiba történt a bejelentkezés során.";
        }
    }
}
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - HairSalon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #475763; color: white; }
        .login-container { max-width: 400px; margin-top: 100px; }
        .card { background-color: #121F2E; border: 1px solid #010B78; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-sm navbar-dark" style="background-color: #010B78;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">HairSalon</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php">Kezdőoldal</a></li>
            <li class="nav-item"><a class="nav-link" href="register.php">Regisztráció</a></li>
        </ul>
    </div>
</nav>

<div class="container d-flex justify-content-center">
    <div class="login-container w-100">
        <div class="card p-4 shadow">
            <h2 class="text-center fw-bold mb-4">Bejelentkezés</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="post" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail cím</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Jelszó</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Belépés</button>
            </form>

            <div class="mt-3 text-center">
            <p class="mt-3 text-center">Nincs még fiókja? <a href="register.php" class="text-info">Regisztráljon itt</a></p>
            <p class="mt-3 text-center">Elfelejtette jelszavát? <a href="request_reset.php" class="text-info">Kattintson ide</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    // Kliens oldali validáció
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (email === "" || password === "") {
            e.preventDefault();
            alert("Minden mezőt ki kell tölteni!");
        }
    });
</script>
</body>
</html>