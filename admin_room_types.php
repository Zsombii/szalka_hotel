<?php
require_once 'config.php';
require_once 'admin_auth.php';

// Egyszerű admin ellenőrzés (később bővítheted)
// Ideiglenesen kikommentezve, hogy működjön teszteléskor
// if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
//     header('Location: login.php');
//     exit;
// }

$message = '';
$error = '';

// Szobatípusok lekérése
$roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();

// Ha van ID a URL-ben, akkor azt a típust szerkesztjük
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editType = null;
$editFeatures = [];

if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
    $stmt->execute([$editId]);
    $editType = $stmt->fetch();
    
    if ($editType) {
        // Jellemzők lekérése
        $stmt = $pdo->prepare("SELECT * FROM room_type_features WHERE room_type_id = ? ORDER BY id");
        $stmt->execute([$editId]);
        $editFeatures = $stmt->fetchAll();
    }
}

// Mentés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_type'])) {
    $typeId = $_POST['type_id'] ?? 0;
    $typeName = $_POST['type_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $detailedDescription = $_POST['detailed_description'] ?? '';
    $basePrice = (int)($_POST['base_price'] ?? 0);
    $maxGuests = (int)($_POST['max_guests'] ?? 2);
    $sizeSqm = !empty($_POST['size_sqm']) ? (int)$_POST['size_sqm'] : null;
    
    if (empty($typeName)) {
        $error = 'A szobatípus neve kötelező!';
    } elseif ($basePrice <= 0) {
        $error = 'Az alapár nem lehet nulla vagy negatív!';
    } else {
        if ($typeId) {
            // Módosítás
            $stmt = $pdo->prepare("UPDATE room_types SET type_name = ?, description = ?, detailed_description = ?, base_price = ?, max_guests = ?, size_sqm = ? WHERE id = ?");
            $success = $stmt->execute([$typeName, $description, $detailedDescription, $basePrice, $maxGuests, $sizeSqm, $typeId]);
            if ($success) {
                $message = 'Szobatípus sikeresen módosítva!';
                log_admin_action($pdo, 'room_type_edit', "Szobatípus módosítva: $typeName");
                
                // Frissítjük a szerkesztett adatokat
                $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
                $stmt->execute([$typeId]);
                $editType = $stmt->fetch();
                
                // Szobatípusok lista frissítése
                $roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();
            } else {
                $error = 'Hiba a módosítás során!';
            }
        } else {
            // Új beszúrás
            $stmt = $pdo->prepare("INSERT INTO room_types (type_name, description, detailed_description, base_price, max_guests, size_sqm) VALUES (?, ?, ?, ?, ?, ?)");
            $success = $stmt->execute([$typeName, $description, $detailedDescription, $basePrice, $maxGuests, $sizeSqm]);
            if ($success) {
                $newId = $pdo->lastInsertId();
                $message = 'Új szobatípus sikeresen létrehozva!';
                log_admin_action($pdo, 'room_type_create', "Új szobatípus: $typeName");
                
                // Frissítjük a listát
                $roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();
                
                // Átirányítjuk a szerkesztő oldalra, hogy azonnal lássa és jellemzőket adhasson hozzá
                header('Location: admin_room_types.php?edit=' . $newId);
                exit;
            } else {
                $error = 'Hiba a létrehozás során!';
            }
        }
    }
}

// Jellemzők mentése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_features'])) {
    $typeId = $_POST['type_id'] ?? 0;
    $features = $_POST['features'] ?? [];
    
    if ($typeId) {
        // Régi jellemzők törlése
        $stmt = $pdo->prepare("DELETE FROM room_type_features WHERE room_type_id = ?");
        $stmt->execute([$typeId]);
        
        // Újak beszúrása
        $insertStmt = $pdo->prepare("INSERT INTO room_type_features (room_type_id, feature_name) VALUES (?, ?)");
        $featureCount = 0;
        foreach ($features as $feature) {
            $feature = trim($feature);
            if (!empty($feature)) {
                $insertStmt->execute([$typeId, $feature]);
                $featureCount++;
            }
        }
        
        $message = $featureCount . ' jellemző sikeresen mentve!';
        log_admin_action($pdo, 'room_type_features', "Jellemzők mentve: $typeId - $featureCount db");
        
        // Frissítjük a szerkesztett adatokat
        $stmt = $pdo->prepare("SELECT * FROM room_type_features WHERE room_type_id = ? ORDER BY id");
        $stmt->execute([$typeId]);
        $editFeatures = $stmt->fetchAll();
    }
}

// Törlés
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    // Ellenőrizzük, hogy van-e szoba ehhez a típushoz
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?");
    $stmt->execute([$deleteId]);
    $roomCount = $stmt->fetch()['count'];
    
    if ($roomCount > 0) {
        $error = 'Nem törölhető, mert vannak szobák ezzel a típussal!';
    } else {
        // Típus adatainak lekérése naplózáshoz
        $stmt = $pdo->prepare("SELECT type_name FROM room_types WHERE id = ?");
        $stmt->execute([$deleteId]);
        $typeName = $stmt->fetch()['type_name'] ?? 'ismeretlen';
        
        // Képek törlése (fizikailag is)
        $stmt = $pdo->prepare("SELECT image_url FROM room_type_images WHERE room_type_id = ?");
        $stmt->execute([$deleteId]);
        $images = $stmt->fetchAll();
        
        $deletedFiles = 0;
        foreach ($images as $img) {
            $filepath = $img['image_url'];
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    $deletedFiles++;
                }
            }
        }
        
        // Adatbázisból törlés
        $pdo->prepare("DELETE FROM room_type_features WHERE room_type_id = ?")->execute([$deleteId]);
        $pdo->prepare("DELETE FROM room_type_images WHERE room_type_id = ?")->execute([$deleteId]);
        $pdo->prepare("DELETE FROM room_types WHERE id = ?")->execute([$deleteId]);
        
        $message = 'Szobatípus sikeresen törölve! (' . $deletedFiles . ' kép törölve)';
        log_admin_action($pdo, 'room_type_delete', "Szobatípus törölve: $typeName");
        
        // Átirányítás, hogy eltűnjön a GET paraméter
        header('Location: admin_room_types.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szobatípusok kezelése - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .admin-header {
            background: var(--dark-blue);
            color: white;
            padding: 30px;
            margin-bottom: 40px;
            border-left: 5px solid var(--gold);
        }
        
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin-bottom: 10px;
            color: var(--gold);
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .admin-card {
            background: var(--cream);
            padding: 30px;
            border: 1px solid var(--gold);
            margin-bottom: 30px;
        }
        
        .admin-card h2 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            margin-bottom: 20px;
            border-left: 4px solid var(--gold);
            padding-left: 15px;
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
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gold);
            background: white;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            font-style: italic;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-admin {
            background: var(--gold);
            color: var(--dark-blue);
            padding: 12px 30px;
            border: none;
            font-weight: 600;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }
        
        .btn-admin:hover {
            background: var(--dark-blue);
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-small {
            padding: 6px 15px;
            font-size: 12px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            padding: 15px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .room-types-list {
            background: white;
            padding: 20px;
        }
        
        .room-type-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .room-type-item:last-child {
            border-bottom: none;
        }
        
        .room-type-info h3 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            margin-bottom: 5px;
        }
        
        .room-type-info p {
            color: #666;
            font-size: 13px;
        }
        
        .room-type-actions {
            display: flex;
            gap: 10px;
        }
        
        .features-container {
            margin-top: 30px;
        }
        
        .feature-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .feature-item input {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--gold);
        }
        
        .feature-item .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-add-feature {
            background: var(--dark-blue);
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 600;
        }
        
        .nav-links {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }
        
        .nav-links a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        @media (max-width: 992px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Szobatípusok kezelése</h1>
            <p>Itt szerkesztheted a szobatípusok adatait és jellemzőit</p>
            <div class="nav-links">
                <a href="admin.php">← Vissza a dashboardra</a>
                <a href="admin_rooms.php">Szobák kezelése</a>
                <a href="upload.php">Képek feltöltése</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-grid">
            <!-- Bal oldal - Új/Szerkesztés űrlap -->
            <div>
                <div class="admin-card">
                    <h2><?php echo $editType ? 'Szobatípus szerkesztése' : 'Új szobatípus'; ?></h2>
                    
                    <?php if ($editType): ?>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="type_id" value="<?php echo $editType['id'] ?? 0; ?>">
                        
                        <div class="form-group">
                            <label>Szobatípus neve *</label>
                            <input type="text" name="type_name" required 
                                   value="<?php echo htmlspecialchars($editType['type_name'] ?? ''); ?>">
                            <small>Pl. Classic szoba, Superior szoba, Grand lakosztály</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Rövid leírás</label>
                            <textarea name="description" rows="4"><?php echo htmlspecialchars($editType['description'] ?? ''); ?></textarea>
                            <small>Ez a rövid leírás jelenik meg a szobatípus kártyáján a listában. (100-200 karakter)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Részletes leírás</label>
                            <textarea name="detailed_description" rows="8"><?php echo htmlspecialchars($editType['detailed_description'] ?? ''); ?></textarea>
                            <small>Ez a részletes leírás jelenik meg a modális ablakban a szoba megtekintésekor. Itt részletesen bemutathatod a szobát.</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Alapár (Ft) *</label>
                                <input type="number" name="base_price" required min="1" 
                                       value="<?php echo $editType['base_price'] ?? ''; ?>">
                                <small>Ez az alapár, amit a szobáknál lehet egyedileg módosítani</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Max. fő</label>
                                <input type="number" name="max_guests" min="1" max="10" 
                                       value="<?php echo $editType['max_guests'] ?? 2; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alapterület (m²)</label>
                            <input type="number" name="size_sqm" min="1" 
                                   value="<?php echo $editType['size_sqm'] ?? ''; ?>">
                        </div>
                        
                        <button type="submit" name="save_type" class="btn-admin">
                            <?php echo $editType ? 'Módosítás' : 'Létrehozás'; ?>
                        </button>
                        
                        <?php if ($editType): ?>
                            <a href="admin_room_types.php" class="btn-admin" style="background: #666; margin-left: 10px;">Új típus</a>
                        <?php endif; ?>
                    </form>
                    
                    <?php if ($editType): ?>
                        <!-- Jellemzők szerkesztése -->
                        <div class="features-container">
                            <h3>Jellemzők szerkesztése</h3>
                            
                            <form method="POST" id="featuresForm">
                                <input type="hidden" name="type_id" value="<?php echo $editType['id']; ?>">
                                <input type="hidden" name="save_features" value="1">
                                
                                <div id="featuresList">
                                    <?php if (!empty($editFeatures)): ?>
                                        <?php foreach ($editFeatures as $feature): ?>
                                            <div class="feature-item">
                                                <input type="text" name="features[]" 
                                                       value="<?php echo htmlspecialchars($feature['feature_name']); ?>" 
                                                       placeholder="Pl. Légkondicionáló">
                                                <button type="button" class="btn-remove" onclick="removeFeature(this)">✕</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="feature-item">
                                            <input type="text" name="features[]" placeholder="Pl. Légkondicionáló">
                                            <button type="button" class="btn-remove" onclick="removeFeature(this)">✕</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" class="btn-add-feature" onclick="addFeature()">
                                    + Új jellemző hozzáadása
                                </button>
                                
                                <button type="submit" class="btn-admin" style="margin-top: 20px;">
                                    Jellemzők mentése
                                </button>
                            </form>
                        </div>
                        
                        <!-- Kapcsolódó szobák listája -->
                        <?php
                        $roomsStmt = $pdo->prepare("SELECT id, room_number, price, status FROM rooms WHERE room_type_id = ?");
                        $roomsStmt->execute([$editType['id']]);
                        $relatedRooms = $roomsStmt->fetchAll();
                        ?>
                        
                        <?php if (!empty($relatedRooms)): ?>
                        <div style="margin-top: 30px;">
                            <h3>Kapcsolódó szobák</h3>
                            <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: var(--dark-blue); color: var(--gold);">
                                        <th style="padding: 8px;">Szobaszám</th>
                                        <th style="padding: 8px;">Ár</th>
                                        <th style="padding: 8px;">Státusz</th>
                                        <th style="padding: 8px;">Művelet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($relatedRooms as $room): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 8px;"><?php echo $room['room_number']; ?></td>
                                        <td style="padding: 8px;"><?php echo number_format($room['price'], 0, ',', ' '); ?> Ft</td>
                                        <td style="padding: 8px;">
                                            <span class="status-badge status-<?php echo $room['status']; ?>">
                                                <?php echo $room['status'] == 'available' ? 'Elérhető' : 'Foglalt'; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 8px;">
                                            <a href="admin_rooms.php?edit=<?php echo $room['id']; ?>" class="btn-small" style="background: var(--gold); color: var(--dark-blue); padding: 4px 8px; text-decoration: none;">Szerkeszt</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Képek kezelése link -->
                        <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border-left: 3px solid var(--gold);">
                            <h3 style="margin-bottom: 10px;">📷 Képek kezelése</h3>
                            <p style="font-size: 13px; margin-bottom: 15px;">
                                Ehhez a szobatípushoz tartozó képeket itt tudod feltölteni:
                            </p>
                            <a href="upload.php?room_type_id=<?php echo $editType['id']; ?>" class="btn-admin" style="display: inline-block; padding: 8px 20px; font-size: 13px;">
                                Képek feltöltése
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jobb oldal - Szobatípusok listája -->
            <div>
                <div class="admin-card">
                    <h2>Meglévő szobatípusok</h2>
                    
                    <div class="room-types-list">
                        <?php foreach ($roomTypes as $type): 
                            // Szobák száma
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?");
                            $stmt->execute([$type['id']]);
                            $roomCount = $stmt->fetch()['count'];
                            
                            // Jellemzők száma
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM room_type_features WHERE room_type_id = ?");
                            $stmt->execute([$type['id']]);
                            $featureCount = $stmt->fetch()['count'];
                            
                            // Képek száma
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM room_type_images WHERE room_type_id = ?");
                            $stmt->execute([$type['id']]);
                            $imageCount = $stmt->fetch()['count'];
                            
                            // Részletes leírás ellenőrzése
                            $hasDetailedDesc = !empty($type['detailed_description']);
                        ?>
                            <div class="room-type-item">
                                <div class="room-type-info">
                                    <h3><?php echo htmlspecialchars($type['type_name']); ?></h3>
                                    <p>
                                        <strong>Alapár:</strong> <?php echo number_format($type['base_price'], 0, ',', ' '); ?> Ft • 
                                        <strong>Max:</strong> <?php echo $type['max_guests']; ?> fő
                                    </p>
                                    <p style="font-size: 12px;">
                                        🏨 Szobák: <?php echo $roomCount; ?> db • 
                                        ⭐ Jellemzők: <?php echo $featureCount; ?> db • 
                                        📷 Képek: <?php echo $imageCount; ?> db
                                    </p>
                                    <p style="font-size: 11px; color: <?php echo $hasDetailedDesc ? '#28a745' : '#dc3545'; ?>">
                                        Részletes leírás: <?php echo $hasDetailedDesc ? '✅ Van' : '❌ Nincs';
                                        ?> • 
                                        Leírás hossza: <?php echo strlen($type['description'] ?? ''); ?> / <?php echo strlen($type['detailed_description'] ?? ''); ?> kar.
                                    </p>
                                </div>
                                <div class="room-type-actions">
                                    <a href="?edit=<?php echo $type['id']; ?>" class="btn-admin btn-small">Szerkeszt</a>
                                    <?php if ($roomCount == 0): ?>
                                    <a href="?delete=<?php echo $type['id']; ?>" 
                                       class="btn-admin btn-small btn-delete" 
                                       onclick="return confirm('Biztosan törölni szeretnéd? A hozzá tartozó képek és jellemzők is törlődnek!')">Törlés</a>
                                    <?php else: ?>
                                    <span class="btn-admin btn-small" style="background: #999; cursor: not-allowed;" title="Nem törölhető, mert vannak szobák ehhez a típushoz">Törlés</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($roomTypes)): ?>
                            <div style="text-align: center; padding: 60px;">
                                <span class="material-symbols-outlined" style="font-size: 48px; color: #ccc;">meeting_room</span>
                                <p style="margin-top: 20px; color: #999;">Még nincs egy szobatípus sem.</p>
                                <p style="font-size: 13px; color: #666;">Hozz létre egy szobatípust a bal oldali űrlap segítségével!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addFeature() {
            const list = document.getElementById('featuresList');
            const newItem = document.createElement('div');
            newItem.className = 'feature-item';
            newItem.innerHTML = `
                <input type="text" name="features[]" placeholder="Pl. Légkondicionáló">
                <button type="button" class="btn-remove" onclick="removeFeature(this)">✕</button>
            `;
            list.appendChild(newItem);
        }
        
        function removeFeature(button) {
            const item = button.parentElement;
            const list = document.getElementById('featuresList');
            
            // Ha ez az utolsó elem, ne töröljük, hanem ürítsük ki
            if (list.children.length === 1) {
                item.querySelector('input').value = '';
            } else {
                item.remove();
            }
        }
    </script>
</body>
</html>