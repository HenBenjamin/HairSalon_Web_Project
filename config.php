<?php
date_default_timezone_set('Europe/Belgrade');
require_once __DIR__ . '/vendor/autoload.php';

const PARAMS = [
    "HOST" => 'localhost',
    "USER" => 'hh',
    "PASSWORD" => '',
    "DB" => 'hh',
    "CHARSET" => 'utf8mb4'
];

$dsn = "mysql:host=" . PARAMS['HOST'] . ";dbname=" . PARAMS['DB'] . ";charset=" . PARAMS['CHARSET'];

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASSWORD'], $pdoOptions);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


define('TWILIO_SID', $_ENV['TWILIO_SID']);
define('TWILIO_TOKEN', $_ENV['TWILIO_TOKEN']);
define('TWILIO_PHONE', $_ENV['TWILIO_PHONE']);
define('MY_PHONE', $_ENV['MY_PHONE']);