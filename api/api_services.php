<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

// 1. OPTIONS kezelés
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 2. Metódus szűrése
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak GET metodus engedelyezett!"]);
    exit;
}

// 3. Paraméter beolvasása az URL-ből ($_GET)
$salon_id = $_GET['salon_id'] ?? null;

if ($salon_id) {
    try{
        $stmt = $pdo->prepare("SELECT service_id, name, price, duration FROM services WHERE salon_id = ?");
        $stmt->execute([$salon_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $services
        ]);
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Adatbazis hiba!"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Hianyzik a salon_id!"]);
}