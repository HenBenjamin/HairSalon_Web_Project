<?php
session_start();
require_once "config.php";
require_once "classes/CouponManager.php";

// Biztonsági ellenőrzés (csak a szalon tulajdonosa vagy az adminisztrátor léphet be)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['owner', 'admin'])) {
    header("Location: login.php");
    exit;
}

// Űrlap feldolgozása
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['coupon_code'], $_POST['appointment_id'])) {
    $code = trim($_POST['coupon_code']);
    $app_id = (int)$_POST['appointment_id'];

    // OOP Példányosítás az adatbázis-kapcsolat átadásával
    $couponManager = new CouponManager($pdo);

    // 1. Kupon és szolgáltatás ár adatainak lekérése az objektumon keresztül
    $data = $couponManager->getCouponWithServicePrice($code, $app_id);

    if ($data) {
        // 2. Beváltás indítása
        $final_price = $couponManager->redeem($app_id, $data);

        if ($final_price !== false) {
            // Kerekítjük az összeget
            $rounded_price = round($final_price);
            
            // Sikeres beváltás üzenet a kiszámolt végső árral
            header("Location: owner_appointments.php?type=success&coupon_msg=Kupon beváltva! Fizetendő: " . $rounded_price . " din.");
        } else {
            // Ha a tranzakció hiba történt az adatbázisban
            header("Location: owner_appointments.php?type=danger&coupon_msg=Hiba történt a tranzakció során.");
        }
    } else {
        // Ha a kupon kód nem létezik, vagy az adott időponthoz nem érvényesíthető
        header("Location: owner_appointments.php?type=danger&coupon_msg=Érvénytelen vagy már felhasznált kód!");
    }
    exit;
} else {
    header("Location: owner_appointments.php");
    exit;
}