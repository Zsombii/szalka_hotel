<?php
require_once 'config.php';

// Admin munkamenet törlése
unset($_SESSION['admin']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Teljes session törlés (opcionális)
session_destroy();

// Átirányítás a bejelentkező oldalra
header('Location: login.php?logged_out=1');
exit;
?>