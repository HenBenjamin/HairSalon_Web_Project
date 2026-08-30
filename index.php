<?php
require_once "config.php";
session_start();
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HairSalon - Időpontfoglalás</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <?php include 'navbar.php'; ?>
    </nav>

<header class="hero-section d-flex align-items-center py-5">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-md-6 text-black mb-4 mb-md-0">
                <h1 class="display-4 fw-bold mb-3">Találd meg a stílusod <span class="szabadka">Szabadkán!</span></h1>
                <p class="lead mb-4 text-dark-50">Foglalj időpontot a legjobb helyi fodrászokhoz egyszerűen, online.</p>
                
                <div class="mb-2">
                    <a href="szalonok.php" class="btn btn-primary btn-lg custom-hero-btn me-2 mb-2">Foglalok most</a>
                    <a href="#about" class="btn btn-info btn-lg custom-tudas-btn mb-2">Tudj meg többet</a>
                </div>
            </div>
            
            <div class="col-md-6 text-center">
                <img src="./images/hair.jpg" alt="Fodrászat Szabadka" class="img-fluid hero-img rounded">
            </div>

        </div>
    </div>
</header>

<div class="container my-5" id="about">
    <div class="row text-center">
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card p-4 h-100 border-0 shadow-sm">
                <div class="mb-3 text-primary" style="font-size: 2rem;"><i class="fa-solid fa-location-dot"></i></div>
                <h3>Könnyű keresés</h3>
                <p class="text-muted">Válogass a szalonok között ár, helyszín és vélemények alapján.</p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card p-4 h-100 border-0 shadow-sm">
                <div class="mb-3 text-primary" style="font-size: 2rem;"><i class="fa-solid fa-clock"></i></div>
                <h3>Gyors foglalás</h3>
                <p class="text-muted">Nincs több telefonálás. Pár kattintás, és már meg is van az időpontod.</p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card p-4 h-100 border-0 shadow-sm">
                <div class="mb-3 text-primary" style="font-size: 2rem;"><i class="fa-solid fa-mobile-screen"></i></div>
                <h3>Bárhonnan</h3>
                <p class="text-muted">Foglald le következő hajvágásodat mobilról vagy asztali gépről.</p>
            </div>
        </div>
        
    </div>
</div>

<footer class="bg-dark text-white py-2 mt-auto">
    <?php include 'footer.html'; ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>