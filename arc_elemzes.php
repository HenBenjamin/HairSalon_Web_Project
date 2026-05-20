<?php
session_start();
require_once "config.php";

// Csak bejelentkezett felhasználók használhatják
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Arcforma Elemzés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        #camera-container {
            display: none;
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            border-radius: 10px;
            transform: scaleX(-1); /* Tükrözés, hogy természetesebb legyen */
        }
        #canvas {
            display: none;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav>
    <?php include 'navbar.php'; ?>
</nav>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Frizura ajánlása AI segítségével</h2>
        <p class="text-muted">Tölts fel egy képet, vagy készíts egy szelfit az elemzéshez!</p>
    </div>

    <div class="row justify-content-center">
        <!-- OPCIÓ 1: Fájl feltöltése -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Kép feltöltése</h4>
                    <p class="small text-muted">Válassz egy éles fotót a galériádból.</p>

                    <form action="process_ai.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label d-block fw-bold mb-3">Válaszd ki a nemed:</label>
                            <div class="d-flex justify-content-center gap-3">
                                <input type="radio" class="btn-check" name="gender" id="gender_male" value="ferfi" checked autocomplete="off">
                                <label class="btn btn-outline-primary px-4 py-2" for="gender_male">
                                    <i class="bi bi-gender-male"></i> Férfi
                                </label>

                                <input type="radio" class="btn-check" name="gender" id="gender_female" value="no" autocomplete="off">
                                <label class="btn btn-outline-danger px-4 py-2" for="gender_female">
                                    <i class="bi bi-gender-female"></i> Nő
                                </label>
                            </div>
                        </div>
                        <input type="file" name="face_image" class="form-control mb-3" accept="image/*" required>
                        <button type="submit" name="upload_mode" class="btn btn-primary w-100">
                            Elemzés indítása
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- OPCIÓ 2: Webkamera -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <i class="bi bi-camera text-info" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Kamera használata</h4>
                    <p class="small text-muted">Készíts egy fotót most a webkameráddal.</p>

                    <form id="camera-form" action="process_ai.php" method="POST">

                    <div class="mb-4">
                        <label class="form-label d-block fw-bold mb-3">Válaszd ki a nemed:</label>
                        <div class="d-flex justify-content-center gap-3">
                            <input type="radio" class="btn-check" name="gender" id="gender_male_camera" value="ferfi" checked autocomplete="off">
                            <label class="btn btn-outline-primary px-4 py-2" for="gender_male_camera">
                                <i class="bi bi-gender-male"></i> Férfi
                            </label>

                            <input type="radio" class="btn-check" name="gender" id="gender_female_camera" value="no" autocomplete="off">
                            <label class="btn btn-outline-danger px-4 py-2" for="gender_female_camera">
                                <i class="bi bi-gender-female"></i> Nő
                            </label>
                        </div>
                    </div>

                    <button type="button" id="start-camera" class="btn btn-info text-white w-100">
                        Kamera megnyitása
                    </button>

                    <div id="camera-container" class="mt-3">
                        <video id="video" autoplay playsinline></video>
                        <button type="button" id="capture-btn" class="btn btn-danger mt-2 w-100">
                            <i class="bi bi-camera-fill"></i> Fotó készítése
                        </button>
                    </div>

                        <input type="hidden" name="image_data" id="image_data">
                        <input type="hidden" name="camera_mode" value="1">
                    </form>
                    <canvas id="canvas" width="640" height="480"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const startBtn = document.getElementById('start-camera');
    const captureBtn = document.getElementById('capture-btn');
    const cameraContainer = document.getElementById('camera-container');
    const imageDataInput = document.getElementById('image_data');
    const cameraForm = document.getElementById('camera-form');

    // Kamera indítása
    startBtn.addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = stream;
            cameraContainer.style.display = 'block';
            startBtn.style.display = 'none';
        } catch (err) {
            alert("Nem sikerült elérni a kamerát: " + err);
        }
    });

    // Kép készítése és küldése
captureBtn.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    context.save();
    context.translate(canvas.width, 0);
    context.scale(-1, 1);
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    context.restore();

    // A kép elmentése a rejtett mezőbe
    document.getElementById('image_data').value = canvas.toDataURL('image/jpeg');

    // Kamera leállítása
    const stream = video.srcObject;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    // Küldés - Mivel a formon belül van a rádió gomb, automatikusan küldi a nemet is!
    document.getElementById('camera-form').submit();
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>