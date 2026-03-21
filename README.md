# Techoázis 2025-2026 vizsgaremek

A **Techoázis** egy komplex, közösségi alapú tech platform, amely ötvözi a piacteret, a fórumot és az tartalomgyártó cikkrendszert egy modern, felhasználóbarát környezetben.

---

## 🌍 Elérhetőség

🔗 **Elérhető oldal:** [https://techoazis.hu](https://techoazis.hu)

---

## 🚀 Fő funkciók

### 🛒 Piactér - Vásárlás

* Hardverek és technológiai eszközök adás-vétele
* Biztonságos hirdetéskezelés
* Képfeltöltés és megjelenítés
* Termékek részletes adatlapokkal

### 💬 Közösségi rendszer - Fórum

* Fórum és kommentrendszer
* Csoportokra való bontás témánként
* Valós idejű frissítés (AJAX polling)
* Felhasználók közötti interakció

### 📰 Tartalom és cikkek - Tudástár

* Tech cikkek publikálása
* Közösségi tartalomgyártás
* Admin általi moderáció és jóváhagyás

### 👥 Felhasználói rendszer

* Regisztráció és bejelentkezés
* "Emlékezz rám" funkció
* Elfelejtett jelszó
* Jogosultsági szintek:

  * Admin
  * Felhasználó

---

## 🛠️ Technológiák

* **Backend:** PHP
* **Frontend:** HTML, CSS, JS
* **Adatbázis:** MySQL
* **Szerver:** XAMPP (lokális fejlesztéshez)
* **Kommunikáció:** AJAX

---

## 📂 Projekt struktúra

A projekt több mint **60+ PHP fájlból** áll, különválasztva:

* Megjelenítés (HTML + PHP)
* Backend logika
* Adatbázis műveletek

Ez a struktúra segíti a skálázhatóságot és az átláthatóságot.

---

## 🧠 Kiemelt megoldások

* **AJAX polling** a dinamikus frissítésekhez
* Optimalizált képfeldolgozás (WebP konverzió, Image cropping)
* Egységes útvonalak a fájloknak globális változókkal
* Biztonságos bejelentkezési rendszer token alapú "remember me" funkcióval

---

## ⚙️ Telepítés

1. Klónozd a repository-t:

```
git clone https://github.com/<your-username>/techoazis.git
```

2. Helyezd a projektet a `htdocs` mappába (XAMPP esetén)

3. Importáld az adatbázist phpMyAdmin segítségével

4. Állítsd be az adatbázis kapcsolatot (`config.php` és `db.php`)

5. Indítsd el az Apache és MySQL szolgáltatásokat

6. Nyisd meg a böngészőben:

```
http://localhost/techoazis
```

---

## 🔒 Biztonság

* Jelszavak hash-elve tárolva
* Token alapú hitelesítés
* Input validáció és védelem alapvető támadások ellen
* reCaptcha v3 integrálása

---

## 📈 Jövőbeli tervek

* Valós idejű értesítési rendszer
* WebSocket alapú chat
* Saját kódfuttató (Compiler)
* Egyéb promóciós lehetőségek

---

## 👨‍💻 Fejlesztés

A projekt több hónapos folyamatos fejlesztés eredménye, amely során a fő fókusz a:

* skálázhatóság
* felhasználói élmény
* felhasználhatóság

---

## 📄 Licenc

Ez a projekt jelenleg nem rendelkezik publikus licenccel.

---

## 💡 Megjegyzés

A Techoázis célja, hogy egy olyan közösségi tech platform legyen, ahol a felhasználók nemcsak vásárolhatnak és eladhatnak, hanem tanulhatnak, kommunikálhatnak és tartalmat is létrehozhatnak egy helyen.
