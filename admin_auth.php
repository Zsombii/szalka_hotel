<?php

if (!isset($pdo)) {
    require_once 'config.php';
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>