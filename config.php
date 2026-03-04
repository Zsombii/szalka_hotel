<?php
// Adatbázis kapcsolati beállítások
$host = 'localhost';
$dbname = 'szalka_hotel';
$username = 'root';
$password = '';

try {
    // PDO kapcsolat létrehozása
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Hibakezelés beállítása
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Alapértelmezett lekérési mód asszociatív tömb
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Hiba esetén üzenet és script leállítás
    die("Adatbázis kapcsolódási hiba: " . $e->getMessage());
}

// Időzóna beállítása
date_default_timezone_set('Europe/Budapest');

// Session indítása, ha még nem fut
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Alap URL (ha később kell)
define('BASE_URL', 'http://localhost/szalka_hotel/');

// Weboldal neve
define('SITE_NAME', 'Hotel Szalka - Siófok *****');

// Admin email cím (pl. hírlevélhez)
define('ADMIN_EMAIL', 'info@szalkahotel.hu');
?>