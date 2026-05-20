<?php
session_start();
require_once "config.php"; // Az adatbázis kapcsolódáshoz (PDO)

// Csak bejelentkezett felhasználók használhatják a funkciót
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nem mentése
    $_SESSION['user_gender'] = $_POST['gender'] ?? 'ferfi'; // alapértelmezett a férfi, ha nincs megadva

    // 1. Mappa beállítása a feltöltött képeknek
    $uploadDir = "images/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Egyedi fájlnév generálása a felülírás elkerülése végett
    $fileName = "face_" . $_SESSION['user_id'] . "_" . time() . ".jpg";
    $targetFilePath = $uploadDir . $fileName;

    // 2. Kép feldolgozása forrástól függően
    if (isset($_POST['camera_mode']) && !empty($_POST['image_data'])) {
        // WEBKAMERA: Base64 string dekódolása
        $img = $_POST['image_data'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        if (!file_put_contents($targetFilePath, $data)) {
            die("Hiba: Nem sikerült menteni a kamera képét.");
        }
    } elseif (isset($_FILES["face_image"]) && $_FILES["face_image"]["error"] == 0) {
        // FELTÖLTÉS: Fájl mozgatása az ideiglenes mappából
        if (!move_uploaded_file($_FILES["face_image"]["tmp_name"], $targetFilePath)) {
            die("Hiba: Nem sikerült a fájlfeltöltés.");
        }
    } else {
        die("Hiba: Nem érkezett érvényes kép adat.");
    }

    // 3. PYTHON SCRIPT MEGHÍVÁSA (Az adatcsere kulcspontja)
    // Megjegyzés: Windows alatt csak 'python', Linuxon 'python3' lehet a parancs
    $pythonExecutable = "python";
    $pythonScript = "ai/analyze_face.py";

    // Parancs összeállítása: python ai/analyze_face.py images/uploads/kep.jpg
    // Az escapeshellarg biztonsági okokból kötelező!
    $command = "$pythonExecutable $pythonScript " . escapeshellarg($targetFilePath) . " 2>&1";

    // A Python válaszának (pl. "szogletes") elkapása
    $output = shell_exec($command);

    // $detectedShape = trim($output);
    // 4. EREDMÉNY KINYERÉSE (Tisztítás a MediaPipe logoktól)
    // Megkeressük a "RESULT:" kezdetű sort a kimenetben

    $detectedShape = "error";
    if (preg_match('/RESULT:([a-z]+)/', $output, $matches)) {
        $detectedShape = $matches[1]; // Csak a forma (pl. szogletes, kerek)
    }

    // Érvényes formák listája (legyen összhangban a Python kódoddal és az SQL-el)
    $validShapes = ['kerek', 'ovalis', 'szogletes', 'hosszukas'];

    // 4. HIBAKEZELÉS ÉS IRÁNYÍTÁS

    if (in_array($detectedShape, $validShapes)) {
        // Sikeres elemzés -> mentjük a sessionbe az eredményt és irányítunk tovább
        $_SESSION['last_detected_shape'] = $detectedShape;
        $_SESSION['last_uploaded_image'] = $targetFilePath;

        header("Location: ajanlasok.php?shape=" . urlencode($detectedShape));
        exit;
    } else {
        // Valami hiba történt a Python oldalon (pl. nem talált arcot)
        echo "<h3>Hiba az elemzés során!</h3>";
        echo "<p>A rendszer válasza: " . htmlspecialchars($output) . "</p>";
        echo "<a href='arc_elemzes.php'>Próbálja újra</a>";
    }
} else {
    // Ha valaki közvetlenül akarná megnyitni a fájlt
    header("Location: arc_elemzes.php");
    exit;
}
?>