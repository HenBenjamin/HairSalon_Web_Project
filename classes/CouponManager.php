<?php
class CouponManager {
    private $pdo;

    /**
     * Konstruktor - Átveszi az adatbázis-kapcsolatot
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lekéri a kupon adatait és a hozzá tartozó szolgáltatás eredeti árát a beváltáshoz
     * @param string $code A vizsgált kuponkód
     * @param int $appointment_id Az érintett időpont azonosítója
     * @return array|bool Visszaadja az adatokat tömbben, vagy false-t ha nem található/már használt
     */
    public function getCouponWithServicePrice(string $code, int $appointment_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, s.price 
            FROM coupons c
            JOIN appointments a ON c.user_id = a.user_id 
            JOIN services s ON a.service_id = s.service_id
            WHERE c.code = ? AND c.is_used = 0 AND a.appointment_id = ?
        ");
        $stmt->execute([$code, $appointment_id]);
        return $stmt->fetch();
    }

    /**
     * Végrehajtja a kupon beváltását: kiszámolja a kedvezményes árat dinárban,
     * lezárja a kupont és frissíti az időpontot egy biztonságos adatbázis-tranzakcióban.
     * @param int $appointment_id Az időpont azonosítója
     * @param array $coupon_data A getCouponWithServicePrice() által visszaadott adathalmaz
     * @return float|bool A kiszámolt kedvezményes ár (dinárban), vagy false hiba esetén
     */
    public function redeem(int $appointment_id, array $coupon_data): float|bool {
        $original_price = $coupon_data['price'];
        $discount_percent = $coupon_data['discount_amount']; // Pl. 20 (ami 20%-ot jelent)
        
        // Kedvezményes ár kiszámítása (Pl. 1000 din - 20% = 800 din)
        $final_price = $original_price * (1 - ($discount_percent / 100));

        // Megnézzük, hogy a hívó környezetben fut-e már tranzakció
        $wasInTransaction = $this->pdo->inTransaction();

        try {
            if (!$wasInTransaction) {
                $this->pdo->beginTransaction();
            }

            // 1. Kupon megjelölése használtként és a dátum mentése
            $stmt1 = $this->pdo->prepare("UPDATE coupons SET is_used = 1, used_at = NOW() WHERE coupon_id = ?");
            $stmt1->execute([$coupon_data['coupon_id']]);

            // 2. Az időpont frissítése: kupon ID és a végső, kedvezményes ár elmentése
            $stmt2 = $this->pdo->prepare("UPDATE appointments SET coupon_id = ?, final_price = ? WHERE appointment_id = ?");
            $stmt2->execute([$coupon_data['coupon_id'], $final_price, $appointment_id]);

            // Csak akkor commitolunk, ha ezt a tranzakciót ez a metódus indította
            if (!$wasInTransaction) {
                $this->pdo->commit();
            }
            
            return $final_price;
        } catch (Exception $e) {
            // Csak akkor rollbackelünk, ha mi indítottuk, különben a külső kód kezeli
            if (!$wasInTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * HŰSÉGPONT JÓVÁÍRÁS ÉS AUTOMATIKUS KUPON GENERÁLÁS
     * Ellenőrzi a pontokat, és ha az új érték eléri a 100-at, generál egy egyedi 
     * egyszer használatos kupont, a felhasználó pontszámából pedig levon 100-at.
     * @param int $user_id A vendég azonosítója
     * @param int $points_to_add A mostani látogatásért járó pontszám (alapértelmezetten 20)
     * @return string|null Visszaadja a generált kuponkódot, ha elérte a 100 pontot, egyébként null
     */
    public function addPointsAndCheckRewards(int $user_id, int $points_to_add = 20): ?string {
        $wasInTransaction = $this->pdo->inTransaction();

        try {
            if (!$wasInTransaction) {
                $this->pdo->beginTransaction();
            }

            // 1. Lekérjük a felhasználó jelenlegi pontszámát
            $stmt = $this->pdo->prepare("SELECT total_points FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $current_points = $user['total_points'] ?? 0;

            $new_points = $current_points + $points_to_add;
            $generated_coupon = null;

            // 2. Ellenőrizzük, hogy elértük-e a 100 pontos határt
            if ($new_points >= 100) {
                // Biztonságosabb mt_rand() használata az egyedi kódhoz
                $coupon_code = 'CP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

                // Beszúrjuk az új kupont (alapértelmezett típus 'percent', érték: 20%)
                $stmt = $this->pdo->prepare("
                    INSERT INTO coupons (user_id, code, discount_amount, is_used, created_at) 
                    VALUES (?, ?, 20, 0, NOW())
                ");
                $stmt->execute([$user_id, $coupon_code]);

                // JAVÍTÁS: Nem nullázzuk le, hanem igazságosan LEVONJUK a 100 pontot, a maradék megmarad!
                $remainder_points = $new_points - 100;
                $stmt = $this->pdo->prepare("UPDATE users SET total_points = ? WHERE user_id = ?");
                $stmt->execute([$remainder_points, $user_id]);

                $generated_coupon = $coupon_code;
            } else {
                // Ha még nincs meg a 100 pont, simán frissítjük az egyenleget
                $stmt = $this->pdo->prepare("UPDATE users SET total_points = ? WHERE user_id = ?");
                $stmt->execute([$new_points, $user_id]);
            }

            if (!$wasInTransaction) {
                $this->pdo->commit();
            }
            return $generated_coupon;
        } catch (Exception $e) {
            if (!$wasInTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return null;
        }
    }
}