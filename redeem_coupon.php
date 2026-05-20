<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['coupon_code'], $_POST['appointment_id'])) {
    $code = trim($_POST['coupon_code']);
    $app_id = $_POST['appointment_id'];

    // 1. Kupon és a hozzá tartozó szolgáltatás árának lekérése
    $stmt = $pdo->prepare("
        SELECT c.*, s.price 
        FROM coupons c
        CROSS JOIN appointments a 
        JOIN services s ON a.service_id = s.service_id
        WHERE c.code = ? AND c.is_used = 0 AND a.appointment_id = ?
    ");
    $stmt->execute([$code, $app_id]);
    $data = $stmt->fetch();

    if ($data) {
        $original_price = $data['price'];
        $discount_percent = $data['discount_amount']; // pl. 20
        // Kedvezményes ár kiszámítása (pl. 1000 - 20% = 800)
        $final_price = $original_price * (1 - ($discount_percent / 100));

        $pdo->beginTransaction();
        try {
            // 2. Kupon lezárása
            $pdo->prepare("UPDATE coupons SET is_used = 1, used_at = NOW() WHERE coupon_id = ?")
                ->execute([$data['coupon_id']]);

            // 3. Foglalás frissítése: kupon ID + a kedvezményes ár elmentése
            $pdo->prepare("UPDATE appointments SET coupon_id = ?, final_price = ? WHERE appointment_id = ?")
                ->execute([$data['coupon_id'], $final_price, $app_id]);

            $pdo->commit();
            header("Location: owner_appointments.php?type=success&coupon_msg=Kupon beváltva! Fizetendő: $final_price din.");
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: owner_appointments.php?type=danger&coupon_msg=Hiba történt.");
        }
    } else {
        header("Location: owner_appointments.php?type=danger&coupon_msg=Érvénytelen kód!");
    }
    exit;
}