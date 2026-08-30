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
            $stmt = $pdo->prepare("SELECT user_id, username, email, password, role, is_active, is_blocked FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user["password"])) {
                
                if ($user['is_active'] == 0) {
                    $error = "A fiókja még nincs aktiválva! Ellenőrizze az e-mailjeit.";
                } 
                elseif ($user['is_blocked'] == 1) {
                    $error = "Fiókod le van tiltva. Kérjük, vedd fel a kapcsolatot az adminisztrátorral.";
                } 
                else {
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["role"] = $user["role"];

                    if ($user["role"] === 'admin') {
                        header("Location: admin_users.php");
                    } 
                    elseif ($user["role"] === 'owner') {
                        $stmtSalon = $pdo->prepare("SELECT salon_id FROM salons WHERE owner_id = ?");
                        $stmtSalon->execute([$user["user_id"]]);
                        $salon = $stmtSalon->fetch();
                        $_SESSION["salon_id"] = $salon ? $salon["salon_id"] : 1;
                        header("Location: services.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

<div class="container d-flex justify-content-center">
    <div class="login-container w-100">
        <div class="card card-login p-4 shadow">
            <h2 class="text-center fw-bold mb-4">Bejelentkezés</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="post" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fa-regular fa-envelope"></i> E-mail cím</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fa-solid fa-lock"></i> Jelszó</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-szalon-foglalas btn-primary w-100 py-2">Belépés</button>
            </form>

            <div class="mt-3 text-center">
            <p class="mt-3 text-center">Nincs még fiókja? <a href="register.php" class="link-primary">Regisztráljon itt</a></p>
            <p class="mt-3 text-center">Elfelejtette jelszavát? <a href="request_reset.php" class="link-primary">Kattintson ide</a></p>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script>
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