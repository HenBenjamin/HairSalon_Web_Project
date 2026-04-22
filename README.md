# HairSalon - Időpontfoglaló Webalkalmazás

Ez egy webes időpontfoglaló rendszer fodrászszalonok számára, amely PHP (PDO), MySQL és Bootstrap technológiák felhasználásával készült. A projekt az iskolai vizsga részeként valósult meg.

## Főbb funkciók
* **Négy hozzáférési szint:** Vendég, Regisztrált felhasználó, Szalon tulajdonosa és Adminisztrátor.
* **Biztonságos regisztráció:** Jelszókezelés `password_hash` használatával és e-mailes aktiválás.
* **Admin vezérlőpult:** Felhasználók kezelése, biztonsági belépési napló IP-alapú detektálással és eszközfelismeréssel.
* **E-mail küldés:** SMTP integráció PHPMailer és Mailtrap használatával.

* **Projekt Dokumentáció:** A részletes projektleírás, piackutatás és technikai specifikáció a `docs/` mappában található PDF formátumban.
* **API Tesztelés:** A  `Hairsalon.postman_collection.json` fájl a 'postman_test/' mappában található, importálható Postmanbe az API végpontok teszteléséhez.
