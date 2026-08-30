<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Csak POST metodus engedelyezett!"]);
    exit;
}

try {
    $token = null;
    if (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $token = trim($_SERVER["HTTP_X_AUTHORIZATION"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $token = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (isset($_SERVER['Authorization'])) {
        $token = trim($_SERVER["Authorization"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['X-Authorization'])) {
            $token = trim($requestHeaders['X-Authorization']);
        } elseif (isset($requestHeaders['Authorization'])) {
            $token = trim($requestHeaders['Authorization']);
        }
    }

    if (!empty($token) && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
        $token = $matches[1];
    }

    if (!$token) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Hiányzó token."]);
        exit;
    }

    $decoded = \Firebase\JWT\JWT::decode($token, new Key(JWT_SECRET, JWT_ALG));
    $userId = $decoded->user->id ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Érvénytelen munkamenet."]);
        exit;
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : null;
    $mobilenumber = isset($_POST['mobilenumber']) ? trim($_POST['mobilenumber']) : null;

    if (empty($username)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "A felhasználónév nem lehet üres!"]);
        exit;
    }

    $uploadedFileName = null;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $uploadedFileName = $newFileName;

                $oldPicStmt = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
                $oldPicStmt->execute([$userId]);
                $oldPic = $oldPicStmt->fetchColumn();
                if ($oldPic && file_exists($uploadFileDir . $oldPic)) {
                    @unlink($uploadFileDir . $oldPic);
                }
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Hiba történt a fájl mentése közben."]);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Csak JPG, PNG és WEBP képek engedélyezettek."]);
            exit;
        }
    }

    if ($uploadedFileName) {
        $sql = "UPDATE users SET username = ?, mobilenumber = ?, profile_pic = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $mobilenumber, $uploadedFileName, $userId]);
    } else {
        $sql = "UPDATE users SET username = ?, mobilenumber = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $mobilenumber, $userId]);
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Profil sikeresen frissítve!",
        "user" => [
            "username" => $username,
            "mobilenumber" => $mobilenumber,
            "profile_picture" => $uploadedFileName
        ]
    ]);
    exit;

} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Hiba történt a feldolgozás során.",
        "error_details" => $e->getMessage()
    ]);
    exit;
}