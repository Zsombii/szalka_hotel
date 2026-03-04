<?php
require_once 'config.php';

// Ha már be van jelentkezve, átirányítjuk az admin oldalra
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
        // Felhasználó lekérése az adatbázisból
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Jelszó ellenőrzése
        if ($user && password_verify($password, $user['password'])) {
            // Sikeres bejelentkezés
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Utolsó bejelentkezés frissítése
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
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: var(--cream);
            border: 2px solid var(--gold);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .login-container h1 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .login-container .subtitle {
            text-align: center;
            color: var(--gold);
            margin-bottom: 30px;
            font-size: 14px;
            letter-spacing: 2px;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-blue);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gold);
            background: var(--white);
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--dark-blue);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: var(--dark-blue);
            border: 2px solid var(--gold);
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }
        
        .btn-login:hover {
            background: var(--dark-blue);
            color: var(--white);
            border-color: var(--dark-blue);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--gold);
            text-decoration: none;
            font-size: 13px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .hotel-icon {
            text-align: center;
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 10px;
        }
        
        .info-box {
            background: var(--white);
            border-left: 4px solid var(--gold);
            padding: 12px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
    </style>
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
        
        <a href="index.php" class="back-link">← Vissza a főoldalra</a>
        
    </div>
</body>
</html>