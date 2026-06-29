<?php
session_start();
require_once "config.php";

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

    // 3. python script meghívása
    $pythonExecutable = "python";
    $pythonScript = "ai/analyze_face.py";

    // Parancs összeállítása: python ai/analyze_face.py images/uploads/kep.jpg
    $command = "$pythonExecutable $pythonScript " . escapeshellarg($targetFilePath) . " 2>&1";

    // A Python válaszának pl: szogletes, elkapasa
    $output = shell_exec($command);

    // 4. eredmény kinyerése (tisztítás a mediapipe logoktól)
    // megkeressük a "result:" kezdetű sort a kimenetben

    $detectedShape = "error";
    $faceRatio = "0.00";
    $jawRatio = "0.00";

    if (preg_match('/RESULT:([^\r\n]+)/', $output, $matches)) {
        $raw_result = trim($matches[1]); // pl. "kerek;1.12;0.82"
        
        $data_parts = explode(';', $raw_result);
        
        if (count($data_parts) === 3) {
            $detectedShape = $data_parts[0]; // pl. kerek
            $faceRatio     = $data_parts[1]; // pl. 1.12
            $jawRatio      = $data_parts[2]; // pl. 0.82
        }
    }

    $validShapes = ['kerek', 'ovalis', 'szogletes', 'hosszukas'];

    if (in_array($detectedShape, $validShapes)) {
        $_SESSION['last_detected_shape'] = $detectedShape;
        $_SESSION['last_uploaded_image'] = $targetFilePath;
        $_SESSION['face_ratio'] = $faceRatio;
        $_SESSION['jaw_ratio'] = $jawRatio;

        header("Location: ajanlasok.php?shape=" . urlencode($detectedShape));
        exit;
    } else {
        echo "<h3>Hiba az elemzés során!</h3>";
        echo "<p>A rendszer válasza: " . htmlspecialchars($output) . "</p>";
        echo "<a href='arc_elemzes.php'>Próbálja újra</a>";
    }
} else {
    header("Location: arc_elemzes.php");
    exit;
}
?>