<?php
// Ezt a fájlt include-olni kell minden admin oldal tetején!

// Ellenőrizzük, hogy a config már be van-e töltve
if (!isset($pdo)) {
    require_once 'config.php';
}

// Admin ellenőrzés
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Ha nincs bejelentkezve, átirányítjuk a login oldalra
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Opcionális: naplózzuk az admin tevékenységeket
function log_admin_action($pdo, $action, $details = '') {
    // Csak akkor naplózunk, ha létezik az admin_logs tábla
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['admin_id'] ?? null,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (Exception $e) {
        // Ha nincs ilyen tábla, csendben figyelmen kívül hagyjuk
    }
}
?>