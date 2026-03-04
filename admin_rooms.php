<?php
require_once 'config.php';
require_once 'admin_auth.php';

$message = '';
$error = '';

// Szobák lekérése a szobatípusokkal együtt
$rooms = $pdo->query("
    SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY r.room_number
")->fetchAll();

// Szobatípusok a legördülő menühöz
$roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();

// Ha van ID a URL-ben, akkor azt a szobát szerkesztjük
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editRoom = null;

if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$editId]);
    $editRoom = $stmt->fetch();
}

// Mentés (új vagy szerkesztés)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_room'])) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $roomTypeId = (int)($_POST['room_type_id'] ?? 0);
    $roomNumber = trim($_POST['room_number'] ?? '');
    $type = trim($_POST['type'] ?? ''); // A kiválasztott típus neve
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'available';
    
    // Validáció
    if (empty($roomNumber)) {
        $error = 'A szobaszám megadása kötelező!';
    } elseif ($price <= 0) {
        $error = 'Az ár nem lehet nulla vagy negatív!';
    } elseif (!$roomTypeId) {
        $error = 'Válassz szobatípust!';
    } else {
        // Ellenőrizzük, hogy létezik-e már ilyen szobaszám (kivéve ha ugyanaz a szoba)
        if ($roomId) {
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
            $stmt->execute([$roomNumber, $roomId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $stmt->execute([$roomNumber]);
        }
        
        if ($stmt->fetch()) {
            $error = 'Már létezik ilyen szobaszám!';
        } else {
            if ($roomId) {
                // Módosítás
                $stmt = $pdo->prepare("
                    UPDATE rooms 
                    SET room_type_id = ?, room_number = ?, type = ?, price = ?, description = ?, status = ? 
                    WHERE id = ?
                ");
                $success = $stmt->execute([$roomTypeId, $roomNumber, $type, $price, $description, $status, $roomId]);
                
                if ($success) {
                    $message = 'Szoba sikeresen módosítva!';
                    log_admin_action($pdo, 'room_edit', "Szoba módosítva: $roomNumber");
                    
                    // Frissítjük a szerkesztett adatokat
                    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
                    $stmt->execute([$roomId]);
                    $editRoom = $stmt->fetch();
                    
                    // Szobák lista frissítése
                    $rooms = $pdo->query("
                        SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
                        FROM rooms r
                        JOIN room_types rt ON r.room_type_id = rt.id
                        ORDER BY r.room_number
                    ")->fetchAll();
                } else {
                    $error = 'Hiba a módosítás során!';
                }
            } else {
                // Új szoba
                $stmt = $pdo->prepare("
                    INSERT INTO rooms (room_type_id, room_number, type, price, description, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $success = $stmt->execute([$roomTypeId, $roomNumber, $type, $price, $description, $status]);
                
                if ($success) {
                    $message = 'Új szoba sikeresen létrehozva!';
                    log_admin_action($pdo, 'room_create', "Új szoba: $roomNumber");
                    
                    // Szobák lista frissítése
                    $rooms = $pdo->query("
                        SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
                        FROM rooms r
                        JOIN room_types rt ON r.room_type_id = rt.id
                        ORDER BY r.room_number
                    ")->fetchAll();
                    
                    // Új szoba létrehozása után ürítsük a formot
                    $editRoom = null;
                } else {
                    $error = 'Hiba a létrehozás során!';
                }
            }
        }
    }
}

// Törlés
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    // Ellenőrizzük, hogy van-e foglalás ehhez a szobához
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ?");
    $stmt->execute([$deleteId]);
    $bookingCount = $stmt->fetch()['count'];
    
    if ($bookingCount > 0) {
        $error = 'Nem törölhető, mert vannak foglalások ehhez a szobához!';
    } else {
        // Szoba adatainak lekérése naplózáshoz
        $stmt = $pdo->prepare("SELECT room_number FROM rooms WHERE id = ?");
        $stmt->execute([$deleteId]);
        $room = $stmt->fetch();
        
        // Törlés
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        if ($stmt->execute([$deleteId])) {
            $message = 'Szoba sikeresen törölve!';
            log_admin_action($pdo, 'room_delete', "Szoba törölve: " . ($room['room_number'] ?? 'ismeretlen'));
            
            // Átirányítás, hogy eltűnjön a GET paraméter
            header('Location: admin_rooms.php');
            exit;
        } else {
            $error = 'Hiba a törlés során!';
        }
    }
}

// Státusz gyorsváltás (AJAXhoz is használható)
if (isset($_GET['toggle_status'])) {
    $toggleId = (int)$_GET['toggle_status'];
    $newStatus = $_GET['status'] ?? '';
    
    if (in_array($newStatus, ['available', 'booked'])) {
        $stmt = $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        if ($stmt->execute([$newStatus, $toggleId])) {
            $message = 'Szoba státusza módosítva!';
            log_admin_action($pdo, 'room_status', "Státusz módosítás: $toggleId -> $newStatus");
        } else {
            $error = 'Hiba a státusz módosítása során!';
        }
    }
    
    // Átirányítás a GET paraméterek eltüntetéséhez
    header('Location: admin_rooms.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szobák kezelése - Admin</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gold);
            background: white;
            font-family: 'Montserrat', sans-serif;
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
        
        .btn-status {
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-status.available {
            background: #28a745;
            color: white;
        }
        
        .btn-status.booked {
            background: #dc3545;
            color: white;
        }
        
        .btn-status:hover {
            opacity: 0.8;
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
        
        .rooms-list {
            background: white;
            padding: 20px;
        }
        
        .room-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .room-item:last-child {
            border-bottom: none;
        }
        
        .room-info h3 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            margin-bottom: 5px;
        }
        
        .room-info p {
            color: #666;
            font-size: 13px;
        }
        
        .room-type-badge {
            display: inline-block;
            background: var(--gold);
            color: var(--dark-blue);
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
            margin-right: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-available {
            background: #28a745;
            color: white;
        }
        
        .status-booked {
            background: #dc3545;
            color: white;
        }
        
        .room-actions {
            display: flex;
            gap: 10px;
            align-items: center;
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
            <h1>Szobák kezelése</h1>
            <p>Itt szerkesztheted a konkrét szobák adatait (szobaszám, ár, státusz)</p>
            <div class="nav-links">
                <a href="admin.php">← Vissza a dashboardra</a>
                <a href="admin_room_types.php">Szobatípusok kezelése</a>
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
                    <h2><?php echo $editRoom ? 'Szoba szerkesztése' : 'Új szoba'; ?></h2>
                    
                    <form method="POST" id="roomForm">
                        <input type="hidden" name="room_id" value="<?php echo $editRoom['id'] ?? 0; ?>">
                        
                        <div class="form-group">
                            <label>Szobatípus *</label>
                            <select name="room_type_id" id="room_type_id" required>
                                <option value="">-- Válassz szobatípust --</option>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" 
                                        data-price="<?php echo $type['base_price']; ?>"
                                        data-name="<?php echo htmlspecialchars($type['type_name']); ?>"
                                        data-description="<?php echo htmlspecialchars($type['description']); ?>"
                                        <?php echo ($editRoom && $editRoom['room_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['type_name']); ?> 
                                        (alapár: <?php echo number_format($type['base_price'], 0, ',', ' '); ?> Ft)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Szobaszám *</label>
                                <input type="text" name="room_number" id="room_number" required 
                                       value="<?php echo htmlspecialchars($editRoom['room_number'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Ár (Ft/éj) *</label>
                                <input type="number" name="price" id="price" required min="1" 
                                       value="<?php echo $editRoom['price'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Szoba típus név *</label>
                            <input type="text" name="type" id="type" required 
                                   value="<?php echo htmlspecialchars($editRoom['type'] ?? ''); ?>">
                            <small>A szobatípus neve (automatikusan kitöltődik a kiválasztott típus alapján)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Leírás</label>
                            <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($editRoom['description'] ?? ''); ?></textarea>
                            <small>A szoba egyedi leírása (automatikusan kitöltődik a kiválasztott típus alapján, de módosítható)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Státusz</label>
                            <select name="status">
                                <option value="available" <?php echo ($editRoom && $editRoom['status'] == 'available') ? 'selected' : ''; ?>>Elérhető</option>
                                <option value="booked" <?php echo ($editRoom && $editRoom['status'] == 'booked') ? 'selected' : ''; ?>>Foglalt</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="save_room" class="btn-admin">
                            <?php echo $editRoom ? 'Módosítás' : 'Létrehozás'; ?>
                        </button>
                        
                        <?php if ($editRoom): ?>
                            <a href="admin_rooms.php" class="btn-admin" style="background: #666; margin-left: 10px;">Új szoba</a>
                        <?php endif; ?>
                    </form>
                    
                    <?php if ($editRoom): ?>
                        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-left: 3px solid var(--gold);">
                            <h3 style="margin-bottom: 10px;">📌 Megjegyzés</h3>
                            <p style="font-size: 13px; color: #666;">
                                A szoba <strong>részletes leírását, képét és jellemzőit</strong> a szobatípusnál kell szerkeszteni.<br>
                                <a href="admin_room_types.php?edit=<?php echo $editRoom['room_type_id']; ?>" style="color: var(--gold);">
                                    → Ugrás a szobatípus szerkesztéséhez
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jobb oldal - Szobák listája -->
            <div>
                <div class="admin-card">
                    <h2>Meglévő szobák</h2>
                    
                    <div class="rooms-list">
                        <?php foreach ($rooms as $room): 
                            // Foglalások száma ehhez a szobához
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND status IN ('pending', 'confirmed')");
                            $stmt->execute([$room['id']]);
                            $bookingCount = $stmt->fetch()['count'];
                        ?>
                            <div class="room-item">
                                <div class="room-info">
                                    <h3><?php echo htmlspecialchars($room['room_number']); ?>. szoba</h3>
                                    <p>
                                        <span class="room-type-badge"><?php echo htmlspecialchars($room['type_name']); ?></span>
                                        <span class="status-badge status-<?php echo $room['status']; ?>">
                                            <?php echo $room['status'] == 'available' ? 'Elérhető' : 'Foglalt'; ?>
                                        </span>
                                    </p>
                                    <p>
                                        Ár: <?php echo number_format($room['price'], 0, ',', ' '); ?> Ft • 
                                        Foglalások: <?php echo $bookingCount; ?> db
                                    </p>
                                </div>
                                <div class="room-actions">
                                    <a href="?toggle_status=<?php echo $room['id']; ?>&status=<?php echo $room['status'] == 'available' ? 'booked' : 'available'; ?>" 
                                       class="btn-status <?php echo $room['status']; ?>"
                                       onclick="return confirm('Biztosan módosítod a státuszt?')">
                                        <?php echo $room['status'] == 'available' ? '➡️ Foglalttá tesz' : '⬅️ Elérhetővé tesz'; ?>
                                    </a>
                                    <a href="?edit=<?php echo $room['id']; ?>" class="btn-admin btn-small">Szerkeszt</a>
                                    <a href="?delete=<?php echo $room['id']; ?>" 
                                       class="btn-admin btn-small btn-delete" 
                                       onclick="return confirm('Biztosan törölni szeretnéd? Csak akkor lehet, ha nincs hozzá foglalás!')">Törlés</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($rooms)): ?>
                            <div style="text-align: center; padding: 60px;">
                                <span class="material-symbols-outlined" style="font-size: 48px; color: #ccc;">door_front</span>
                                <p style="margin-top: 20px; color: #999;">Még nincs egy szoba sem a rendszerben.</p>
                                <p style="font-size: 13px; color: #666;">Hozz létre egy szobatípust, majd adj hozzá szobákat!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('room_type_id');
        const typeInput = document.getElementById('type');
        const priceInput = document.getElementById('price');
        const descriptionInput = document.getElementById('description');
        
        // Ha van kiválasztott típus (szerkesztésnél), akkor töltsük be az adatokat
        if (typeSelect.value) {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            if (selectedOption) {
                typeInput.value = selectedOption.dataset.name || '';
                priceInput.value = selectedOption.dataset.price || '';
                descriptionInput.value = selectedOption.dataset.description || '';
            }
        }
        
        // Típus váltásakor automatikus kitöltés
        typeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                typeInput.value = selectedOption.dataset.name || '';
                priceInput.value = selectedOption.dataset.price || '';
                descriptionInput.value = selectedOption.dataset.description || '';
            } else {
                // Ha nincs kiválasztva, ürítsük a mezőket
                typeInput.value = '';
                priceInput.value = '';
                descriptionInput.value = '';
            }
        });
    });
    </script>
</body>
</html>