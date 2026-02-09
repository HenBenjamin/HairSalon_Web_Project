# HairSalon - Időpontfoglaló Webalkalmazás

Ez egy webes időpontfoglaló rendszer fodrászszalonok számára, amely PHP (PDO), MySQL és Bootstrap technológiák felhasználásával készült. A projekt az iskolai vizsgaremek részeként valósult meg.

## Főbb funkciók
* **Négy hozzáférési szint:** Vendég, Regisztrált felhasználó, Szalon tulajdonosa és Adminisztrátor.
* **Biztonságos regisztráció:** Jelszókezelés `password_hash` használatával és e-mailes aktiválás.
* **Admin vezérlőpult:** Felhasználók kezelése, biztonsági belépési napló IP-alapú detektálással és eszközfelismeréssel.
* **E-mail küldés:** SMTP integráció PHPMailer és Mailtrap használatával.

## Technikai részletek
* **Backend:** PHP 8.3+ (Objektumorientált megközelítés, PDO).
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.
* **Adatbázis:** MySQL (InnoDb).
* **Függőségkezelés:** Composer.

## Telepítés és beállítás

1. **Adatbázis importálása:**
   - Hozz létre egy adatbázist a MySQL szervereden.
   - Importáld a gyökérkönyvtárban található `hh.sql` fájlt.

2. **Függőségek telepítése:**
   - Futtasd a következő parancsot a projekt mappájában:
   ```bash
   composer install
