<?php
// Hibakeresés bekapcsolva, de pufferelve, hogy ne rontsa el a JSON-t
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

$method = $_SERVER['REQUEST_METHOD'];

// 1. GET ág: Képek lekérése
if ($method === 'GET') {
    $salon_id = isset($_GET['salon_id']) ? intval($_GET['salon_id']) : 0;

    if ($salon_id === 0) {
        ob_clean();
        http_response_code(400);
        echo json_encode([]); // Üres lista, ha nincs salon_id
        exit;
    }

    $stmt = $pdo->prepare("SELECT g.*, u.username FROM gallery g JOIN users u ON g.user_id = u.user_id WHERE g.salon_id = ? ORDER BY g.uploaded_at DESC");
    $stmt->execute([$salon_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    http_response_code(200);
    echo json_encode($images);
    exit;
}

// 2. POST ág: Kép feltöltése
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['image']) || !isset($input['salon_id'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Hianyzo adatok"]);
        exit;
    }

    $user_id = $input['user_id'];
    $salon_id = $input['salon_id'];
    $image_base64 = $input['image'];

    // Útvonal ellenőrzése
    $target_dir = dirname(__FILE__) . "/../gallery/";
    $filename = "img_" . time() . "_" . uniqid() . ".jpg";
    $path = $target_dir . $filename;

    $img_data = base64_decode($image_base64);

    if (file_put_contents($path, $img_data)) {
        $stmt = $pdo->prepare("INSERT INTO gallery (salon_id, user_id, image_path) VALUES (?, ?, ?)");
        if ($stmt->execute([$salon_id, $user_id, $filename])) {
            ob_clean();
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Sikeres mentes"]);
            exit;
        } else {
            ob_clean();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Adatbazis hiba"]);
            exit;
        }
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Fajl mentese sikertelen. Ellenorizd a gallery mappat!"]);
        exit;
    }
}

// Ha nem GET és nem POST
http_response_code(405); // Method Not Allowed
echo json_encode(["status" => "error", "message" => "Nem tamogatott metodus"]);