<?php
// A pontszám lekérdezése a bejelentkezett felhasználóknál
$userPoints = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT total_points, profile_pic FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $userPoints = $userData['total_points'] ?: 0;
    $_SESSION['profile_pic'] = $userData['profile_pic'];
}

// Dinamikus márkajelzés és logó beállítása a rang alapján
$navbarBrandText = "SzalonFoglaló";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'owner') {
        $navbarBrandText = "Szalon Kezelés";
    } elseif ($_SESSION['role'] == 'admin') {
        $navbarBrandText = "HairSalon Admin";
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-scissors me-2"></i><?= $navbarBrandText ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] == 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="szalonok.php">Szalonok</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="foglalasaim.php">Foglalásaim</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="admin_users.php">Felhasználók kezelése</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="admin_salons.php">Szalonok kezelése</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-semibold" href="logout.php">Kijelentkezés</a>
                        </li>

                    <?php elseif ($_SESSION['role'] == 'owner'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="services.php">Szolgáltatások</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="working_hours.php">Nyitvatartás</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="owner_appointments.php">Időpontok kezelése</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="owner_gallery.php">Galéria</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white d-flex align-items-center gap-2" href="profil_szerkesztes.php">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; overflow: hidden;">
                                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                                        <img src="./images/profile_pics/<?= $_SESSION['profile_pic'] ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill text-white" style="font-size: 1.2rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-semibold" href="logout.php">Kijelentkezés</a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item">
                            <a href="husegprogram.php" class="text-decoration-none">
                                <span class="badge rounded-pill bg-warning text-dark px-3 py-2 shadow-sm fw-bold">
                                    <i class="bi bi-star-fill me-1"></i> <?= $userPoints ?> pont
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white d-flex align-items-center gap-2" href="profil_szerkesztes.php">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; overflow: hidden;">
                                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                                        <img src="./images/profile_pics/<?= $_SESSION['profile_pic'] ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill text-white" style="font-size: 1.2rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-semibold" href="logout.php">Kijelentkezés</a>
                        </li>
                    <?php endif; ?>

                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link text-white-50">Bejelentkezés</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link text-white-50">Regisztráció</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>