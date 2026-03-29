<?php
$host = 'localhost';
$dbname = 'szalka_hotel';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Adatbázis kapcsolódási hiba: " . $e->getMessage());
}

date_default_timezone_set('Europe/Budapest');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>