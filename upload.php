<?php
require_once 'config.php';
require_once 'admin_auth.php';

// Csak adminok tölthetnek fel (ha van bejelentkezés)
// session_start();
// if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
//     die('Nincs jogosultságod a fájlok feltöltéséhez!');
// }

// Szobatípusok listájának lekérése a lenyíló menühöz
$roomTypes = $pdo->query("SELECT id, type_name FROM room_types ORDER BY type_name")->fetchAll();

$message = '';
$error = '';

// Feltöltés feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type_id = $_POST['room_type_id'] ?? 0;
    $is_main = isset($_POST['is_main']) ? 1 : 0;
    
    if (!$room_type_id) {
        $error = 'Válassz ki egy szobatípust!';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Hiba a fájl feltöltésekor!';
    } else {
        $file = $_FILES['image'];
        
        // Engedélyezett fájltípusok
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        // Ellenőrzések
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Csak JPEG, PNG, GIF és WEBP képek tölthetők fel!';
        } elseif ($file['size'] > $max_size) {
            $error = 'A fájl túl nagy! Maximum 5 MB lehet.';
        } else {
            // Feltöltési mappa létrehozása, ha nem létezik
            $upload_dir = 'uploads/room_types/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Egyedi fájlnév generálás
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'roomtype_' . $room_type_id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Kép áthelyezése
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Relatív URL a fájlhoz
                $image_url = $filepath;
                
                // Ha ez a fő kép, akkor a többi fő kép státuszának levétele ennél a típusnál
                if ($is_main) {
                    $stmt = $pdo->prepare("UPDATE room_type_images SET is_main = 0 WHERE room_type_id = ?");
                    $stmt->execute([$room_type_id]);
                }
                
                // Beszúrás az adatbázisba
                $stmt = $pdo->prepare("INSERT INTO room_type_images (room_type_id, image_url, is_main) VALUES (?, ?, ?)");
                if ($stmt->execute([$room_type_id, $image_url, $is_main])) {
                    $message = 'Kép sikeresen feltöltve a szobatípushoz!';
                } else {
                    $error = 'Adatbázis hiba!';
                    // Ha az adatbázisba nem sikerült, töröljük a feltöltött fájlt
                    unlink($filepath);
                }
            } else {
                $error = 'Hiba a fájl mentésekor!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szobatípus képek feltöltése</title>
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: var(--cream);
            border: 1px solid var(--gold);
        }
        
        .upload-container h1 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            margin-bottom: 10px;
            text-align: center;
        }
        
        .upload-container .subtitle {
            text-align: center;
            color: var(--gold);
            margin-bottom: 30px;
            font-size: 14px;
            letter-spacing: 2px;
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
        
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gold);
            background: var(--white);
        }
        
        .form-group.checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group.checkbox input {
            width: auto;
        }
        
        .btn-upload {
            background: var(--gold);
            color: var(--dark-blue);
            padding: 12px 30px;
            border: none;
            font-weight: 600;
            letter-spacing: 2px;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
        }
        
        .btn-upload:hover {
            background: var(--dark-blue);
            color: var(--white);
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            padding: 10px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--gold);
        }
        
        .info-box {
            background: var(--white);
            border-left: 4px solid var(--gold);
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h1>Szobatípus kép feltöltése</h1>
        <div class="subtitle">EGY FELTÖLTÉS, MINDEN SZOBA HASZNÁLJA</div>
        
        <div class="info-box">
            <strong>💡 Információ:</strong> A képek szobatípushoz tartoznak. Ha feltöltesz egy képet a "Classic szoba" típushoz, akkor az összes Classic szoba (101, 102, stb.) automatikusan megkapja ezt a képet. Nem kell minden szobához külön feltölteni!
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="room_type_id">Válassz szobatípust:</label>
                <select name="room_type_id" id="room_type_id" required>
                    <option value="">-- Válassz szobatípust --</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Kép kiválasztása:</label>
                <input type="file" name="image" id="image" accept="image/*" required onchange="previewImage(this)">
                <img id="preview" class="preview" alt="Előnézet">
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" name="is_main" id="is_main">
                <label for="is_main">Főkép (ez lesz a szobatípus fő képe)</label>
            </div>
            
            <button type="submit" class="btn-upload">KÉP FELTÖLTÉSE</button>
        </form>
        
        <a href="index.php" class="back-link">← Vissza a főoldalra</a>
    </div>
    
    <script>
        function previewImage(input) {
            var preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>