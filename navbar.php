<?php
// A pontszám lekérdezése csak bejelentkezett felhasználó esetén
$userPoints = 0;
if (isset($_SESSION['user_id'])) {
    // Itt fontos a PDO kapcsolat megléte (általában a config.php-ban van)
    $stmt = $pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userPoints = $stmt->fetchColumn() ?: 0;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-scissors me-2"></i>SzalonFoglaló
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="szalonok.php">Szalonok</a>
                </li>

                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'user' || $_SESSION['role'] == 'admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="foglalasaim.php">Foglalásaim</a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'owner'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="owner_appointments.php">Saját Szalonom</a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-danger" href="#" id="adminDrop" data-bs-toggle="dropdown">Admin Panel</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="admin_users.php">Felhasználók kezelése</a></li>
                            <li><a class="dropdown-item" href="admin_salons.php">Szalonok jóváhagyása</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="husegprogram.php" class="text-decoration-none me-3">
                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2 shadow-sm">
                            <i class="bi bi-star-fill me-1"></i> <?php echo $userPoints; ?> pont
                        </span>
                    </a>

                    <span class="navbar-text me-3">
                        Üdv, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>
                    
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">Kezdőoldal</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">Kijelentkezés</a>
                    </li>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Bejelentkezés</a>
                    <a href="register.php" class="nav-link">Regisztráció</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>