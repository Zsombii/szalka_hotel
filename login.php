<?php
require_once 'config.php';

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Felhasználónév és jelszó megadása kötelező!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Hibás felhasználónév vagy jelszó!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin bejelentkezés - Hotel Szalka</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body style="background: linear-gradient(135deg, var(--dark-blue), #0A1E3C); min-height: 100vh; display: flex; align-items: center;">
    <div class="login-container">
        <div class="hotel-icon">🏨</div>
        <h1>HOTEL SZALKA</h1>
        <div class="subtitle">ADMIN FELÜLET</div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Felhasználónév</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Jelszó</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Belépés</button>
        </form>
        
        <a href="index.php" class="back-link">Vissza a főoldalra</a>
        
    </div>
</body>
</html>