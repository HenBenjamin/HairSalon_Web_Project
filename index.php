<?php
session_start();
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HairSalon - Időpontfoglalás</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .hero-section {
            background: linear-gradient(rgba(1, 11, 120, 0.8), rgba(18, 31, 46, 0.9)), url('images/hair.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 20px;
        }
        .feature-card {
            background: white;
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .navbar {
            background-color: #010B78 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .btn-primary {
            background-color: #010B78;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

<!--<nav class="navbar navbar-expand-lg navbar-dark sticky-top">-->
<!--    <div class="container">-->
<!--        <a class="navbar-brand fw-bold" href="#">HAIRSALON</a>-->
<!--        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">-->
<!--            <span class="navbar-toggler-icon"></span>-->
<!--        </button>-->
<!--        <div class="collapse navbar-collapse" id="navbarNav">-->
<!--            <ul class="navbar-nav ms-auto">-->
<!--                <li class="nav-item"><a class="nav-link active" href="#">Kezdőoldal</a></li>-->
<!--                <li class="nav-item"><a class="nav-link" href="szalonok.php">Szalonok</a></li>-->
<!--                --><?php //if(isset($_SESSION['user_id'])): ?>
<!--                    <li class="nav-item"><a class="nav-link btn btn-outline-light ms-lg-3" href="logout.php">Kijelentkezés</a></li>-->
<!--                --><?php //else: ?>
<!--                    <li class="nav-item"><a class="nav-link" href="login.php">Bejelentkezés</a></li>-->
<!--                    <li class="nav-item"><a class="nav-link btn btn-light text-primary ms-lg-3" href="register.php">Regisztráció</a></li>-->
<!--                --><?php //endif; ?>
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<!--</nav>-->

<header class="hero-section">
    <div class="container">
        <h1 class="display-3">Találd meg a stílusod Szabadkán!</h1>
        <p class="lead mb-4 text-muted">Foglalj időpontot a legjobb helyi fodrászokhoz egyszerűen, online.</p>
        <a href="#about" class="btn btn-light btn-lg text-primary me-2">Tudj meg többet</a>
        <a href="szalonok.php" class="btn btn-primary btn-lg">Foglalok most</a>
    </div>
</header>

<div class="container my-5" id="about">
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="mb-3 text-primary" style="font-size: 2rem;">📍</div>
                <h3>Könnyű keresés</h3>
                <p>Válogass a szalonok között ár, helyszín és vélemények alapján.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="mb-3 text-primary" style="font-size: 2rem;">⏰</div>
                <h3>Gyors foglalás</h3>
                <p>Nincs több telefonálás. Pár kattintás, és már meg is van az időpontod.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="mb-3 text-primary" style="font-size: 2rem;">📱</div>
                <h3>Bárhonnan</h3>
                <p>Foglald le következő hajvágásodat mobilról vagy asztali gépről.</p>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-6 text-md-start">
                <h5>HairSalon Szabadka</h5>
                <p class="small text-muted">A legmodernebb időpontfoglaló rendszer fodrászoknak és vendégeknek.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; 2025 HairSalon Projekt. Minden jog fenntartva.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>