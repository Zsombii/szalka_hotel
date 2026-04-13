<?php
require_once 'config.php';
require_once 'admin_auth.php';

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = CURDATE()");
$today_bookings = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE check_in = ? AND status = 'confirmed'");
$stmt->execute([date('Y-m-d')]);
$check_in_today = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'");
$available_rooms = $stmt->fetch()['count'];

$month_start = date('Y-m-01');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) as revenue FROM bookings WHERE booking_date >= ? AND status != 'cancelled'");
$stmt->execute([$month_start]);
$month_revenue = $stmt->fetch()['revenue'];

$newsletter_count = $pdo->query("SELECT COUNT(*) as count FROM newsletter")->fetch()['count'];

$today_guests = $pdo->prepare("
    SELECT b.*, r.room_number, rt.type_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE b.check_in = ? AND b.status = 'confirmed'
    ORDER BY b.guest_name
");
$today_guests->execute([date('Y-m-d')]);
$today_guests_list = $today_guests->fetchAll();

$booking_message = '';
$booking_error = '';

if (isset($_GET['change_status'])) {
    $booking_id = (int)$_GET['change_status'];
    $new_status = $_GET['status'] ?? '';
    
    if (in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $booking_id])) {
            $booking_message = 'Foglalás státusza sikeresen módosítva!';
        } else {
            $booking_error = 'Hiba a státusz módosítása során!';
        }
    }
}

if (isset($_GET['delete_booking'])) {
    $booking_id = (int)$_GET['delete_booking'];
    
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    if ($stmt->execute([$booking_id])) {
        $booking_message = 'Foglalás sikeresen törölve!';
    } else {
        $booking_error = 'Hiba a foglalás törlése során!';
    }
}

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d', strtotime('+30 days'));

$sql = "
    SELECT b.*, r.room_number, rt.type_name,
           DATEDIFF(b.check_out, b.check_in) as nights
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

$sql .= " AND b.check_in >= ? AND b.check_in <= ?";
$params[] = $date_from;
$params[] = $date_to;

$sql .= " ORDER BY b.check_in DESC, b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$stats = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'revenue' => 0];
foreach ($bookings as $b) {
    $stats['total']++;
    $stats[$b['status']]++;
    if ($b['status'] != 'cancelled') {
        $stats['revenue'] += $b['total_price'];
    }
}

$room_type_message = '';
$room_type_error = '';

$roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_type'])) {
    $typeId = (int)($_POST['type_id'] ?? 0);
    $typeName = trim($_POST['type_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $detailedDescription = trim($_POST['detailed_description'] ?? '');
    $basePrice = (int)($_POST['base_price'] ?? 0);
    $maxGuests = (int)($_POST['max_guests'] ?? 2);
    $sizeSqm = !empty($_POST['size_sqm']) ? (int)$_POST['size_sqm'] : null;
    
    if (empty($typeName)) {
        $room_type_error = 'A szobatípus neve kötelező!';
    } elseif ($basePrice <= 0) {
        $room_type_error = 'Az alapár nem lehet nulla vagy negatív!';
    } else {
        if ($typeId) {
            $stmt = $pdo->prepare("UPDATE room_types SET type_name = ?, description = ?, detailed_description = ?, base_price = ?, max_guests = ?, size_sqm = ? WHERE id = ?");
            if ($stmt->execute([$typeName, $description, $detailedDescription, $basePrice, $maxGuests, $sizeSqm, $typeId])) {
                $room_type_message = 'Szobatípus sikeresen módosítva!';
            } else {
                $room_type_error = 'Hiba a módosítás során!';
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO room_types (type_name, description, detailed_description, base_price, max_guests, size_sqm) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$typeName, $description, $detailedDescription, $basePrice, $maxGuests, $sizeSqm])) {
                $room_type_message = 'Új szobatípus sikeresen létrehozva!';
            } else {
                $room_type_error = 'Hiba a létrehozás során!';
            }
        }
        $roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_features'])) {
    $typeId = (int)($_POST['type_id'] ?? 0);
    $features = $_POST['features'] ?? [];
    
    if ($typeId) {
        $pdo->prepare("DELETE FROM room_type_features WHERE room_type_id = ?")->execute([$typeId]);
        
        $insertStmt = $pdo->prepare("INSERT INTO room_type_features (room_type_id, feature_name) VALUES (?, ?)");
        $featureCount = 0;
        foreach ($features as $feature) {
            $feature = trim($feature);
            if (!empty($feature)) {
                $insertStmt->execute([$typeId, $feature]);
                $featureCount++;
            }
        }
        $room_type_message = $featureCount . ' jellemző sikeresen mentve!';
    }
}

if (isset($_GET['delete_type'])) {
    $deleteId = (int)$_GET['delete_type'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?");
    $stmt->execute([$deleteId]);
    $roomCount = $stmt->fetch()['count'];
    
    if ($roomCount > 0) {
        $room_type_error = 'Nem törölhető, mert vannak szobák ezzel a típussal!';
    } else {
        $stmt = $pdo->prepare("SELECT image_url FROM room_type_images WHERE room_type_id = ?");
        $stmt->execute([$deleteId]);
        $images = $stmt->fetchAll();
        
        foreach ($images as $img) {
            if (file_exists($img['image_url'])) {
                unlink($img['image_url']);
            }
        }
        
        $pdo->prepare("DELETE FROM room_type_features WHERE room_type_id = ?")->execute([$deleteId]);
        $pdo->prepare("DELETE FROM room_type_images WHERE room_type_id = ?")->execute([$deleteId]);
        $pdo->prepare("DELETE FROM room_types WHERE id = ?")->execute([$deleteId]);
        
        $room_type_message = 'Szobatípus sikeresen törölve!';
        $roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY id")->fetchAll();
    }
}

$editType = null;
$editFeatures = [];
if (isset($_GET['edit_type'])) {
    $editId = (int)$_GET['edit_type'];
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
    $stmt->execute([$editId]);
    $editType = $stmt->fetch();
    
    if ($editType) {
        $stmt = $pdo->prepare("SELECT * FROM room_type_features WHERE room_type_id = ? ORDER BY id");
        $stmt->execute([$editId]);
        $editFeatures = $stmt->fetchAll();
    }
}

$room_message = '';
$room_error = '';

$rooms = $pdo->query("
    SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
    FROM rooms r
    JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY r.room_number
")->fetchAll();

$roomTypesList = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_room'])) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $roomTypeId = (int)($_POST['room_type_id'] ?? 0);
    $roomNumber = trim($_POST['room_number'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'available';
    
    if (empty($roomNumber)) {
        $room_error = 'A szobaszám megadása kötelező!';
    } elseif ($price <= 0) {
        $room_error = 'Az ár nem lehet nulla vagy negatív!';
    } elseif (!$roomTypeId) {
        $room_error = 'Válassz szobatípust!';
    } else {
        if ($roomId) {
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
            $stmt->execute([$roomNumber, $roomId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $stmt->execute([$roomNumber]);
        }
        
        if ($stmt->fetch()) {
            $room_error = 'Már létezik ilyen szobaszám!';
        } else {
            if ($roomId) {
                $stmt = $pdo->prepare("UPDATE rooms SET room_type_id = ?, room_number = ?, type = ?, price = ?, description = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$roomTypeId, $roomNumber, $type, $price, $description, $status, $roomId])) {
                    $room_message = 'Szoba sikeresen módosítva!';
                } else {
                    $room_error = 'Hiba a módosítás során!';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO rooms (room_type_id, room_number, type, price, description, status) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$roomTypeId, $roomNumber, $type, $price, $description, $status])) {
                    $room_message = 'Új szoba sikeresen létrehozva!';
                } else {
                    $room_error = 'Hiba a létrehozás során!';
                }
            }
            $rooms = $pdo->query("
                SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                ORDER BY r.room_number
            ")->fetchAll();
        }
    }
}

if (isset($_GET['delete_room'])) {
    $deleteId = (int)$_GET['delete_room'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ?");
    $stmt->execute([$deleteId]);
    $bookingCount = $stmt->fetch()['count'];
    
    if ($bookingCount > 0) {
        $room_error = 'Nem törölhető, mert vannak foglalások ehhez a szobához!';
    } else {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        if ($stmt->execute([$deleteId])) {
            $room_message = 'Szoba sikeresen törölve!';
            $rooms = $pdo->query("
                SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                ORDER BY r.room_number
            ")->fetchAll();
        } else {
            $room_error = 'Hiba a törlés során!';
        }
    }
}

if (isset($_GET['toggle_room_status'])) {
    $toggleId = (int)$_GET['toggle_room_status'];
    $newStatus = $_GET['status'] ?? '';
    
    if (in_array($newStatus, ['available', 'booked'])) {
        $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?")->execute([$newStatus, $toggleId]);
        $room_message = 'Szoba státusza módosítva!';
        $rooms = $pdo->query("
            SELECT r.*, rt.type_name, rt.description as type_description, rt.base_price
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.id
            ORDER BY r.room_number
        ")->fetchAll();
    }
}

$editRoom = null;
if (isset($_GET['edit_room'])) {
    $editId = (int)$_GET['edit_room'];
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$editId]);
    $editRoom = $stmt->fetch();
}

$upload_message = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $room_type_id = (int)($_POST['room_type_id'] ?? 0);
    $is_main = isset($_POST['is_main']) ? 1 : 0;
    
    if (!$room_type_id) {
        $upload_error = 'Válassz ki egy szobatípust!';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'Hiba a fájl feltöltésekor!';
    } else {
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $upload_error = 'Csak JPEG, PNG, GIF és WEBP képek tölthetők fel!';
        } elseif ($file['size'] > $max_size) {
            $upload_error = 'A fájl túl nagy! Maximum 5 MB lehet.';
        } else {
            $upload_dir = 'uploads/room_types/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'roomtype_' . $room_type_id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $image_url = $filepath;
                
                if ($is_main) {
                    $pdo->prepare("UPDATE room_type_images SET is_main = 0 WHERE room_type_id = ?")->execute([$room_type_id]);
                }
                
                $stmt = $pdo->prepare("INSERT INTO room_type_images (room_type_id, image_url, is_main) VALUES (?, ?, ?)");
                if ($stmt->execute([$room_type_id, $image_url, $is_main])) {
                    $upload_message = 'Kép sikeresen feltöltve a szobatípushoz!';
                } else {
                    $upload_error = 'Adatbázis hiba!';
                    unlink($filepath);
                }
            } else {
                $upload_error = 'Hiba a fájl mentésekor!';
            }
        }
    }
}

if (isset($_GET['delete_image'])) {
    $imageId = (int)$_GET['delete_image'];
    
    $stmt = $pdo->prepare("SELECT image_url FROM room_type_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch();
    
    if ($image) {
        if (file_exists($image['image_url'])) {
            unlink($image['image_url']);
        }
        $pdo->prepare("DELETE FROM room_type_images WHERE id = ?")->execute([$imageId]);
        $upload_message = 'Kép sikeresen törölve!';
    }
}

if (isset($_GET['make_main'])) {
    $imageId = (int)$_GET['make_main'];
    
    $stmt = $pdo->prepare("SELECT room_type_id FROM room_type_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch();
    
    if ($image) {
        $pdo->prepare("UPDATE room_type_images SET is_main = 0 WHERE room_type_id = ?")->execute([$image['room_type_id']]);
        $pdo->prepare("UPDATE room_type_images SET is_main = 1 WHERE id = ?")->execute([$imageId]);
        $upload_message = 'Főkép sikeresen beállítva!';
    }
}

$active_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Szalka</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="top-nav">
        <div class="logo-area">
            <h1>HOTEL SZALKA</h1>
            <div class="stars">★★★★</div>
        </div>
        
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            <span style="color: rgba(255,255,255,0.5);">|</span>
            <span style="color: white;"><?php echo date('Y.m.d.'); ?></span>
            <a href="logout.php" class="logout-btn">Kijelentkezés</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="?section=dashboard" class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a></li>
                <li><a href="?section=room-types" class="<?php echo $active_section == 'room-types' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">meeting_room</span>
                    Szobatípusok
                </a></li>
                <li><a href="?section=rooms" class="<?php echo $active_section == 'rooms' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">door_front</span>
                    Szobák
                </a></li>
                <li><a href="?section=bookings" class="<?php echo $active_section == 'bookings' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">book_online</span>
                    Foglalások
                </a></li>
                <li><a href="?section=upload" class="<?php echo $active_section == 'upload' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">upload</span>
                    Kép feltöltés
                </a></li>
                <li><a href="?section=images" class="<?php echo $active_section == 'images' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">photo_library</span>
                    Képek kezelése
                </a></li>
            </ul>
        </div>
        
        <div class="content-area">
            <?php if ($active_section == 'dashboard'): ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="material-symbols-outlined">today</span>
                        <div class="stat-value"><?php echo $today_bookings; ?></div>
                        <div class="stat-label">Mai foglalás</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="material-symbols-outlined">hourglass_empty</span>
                        <div class="stat-value"><?php echo $pending_bookings; ?></div>
                        <div class="stat-label">Függőben</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="material-symbols-outlined">login</span>
                        <div class="stat-value"><?php echo $check_in_today; ?></div>
                        <div class="stat-label">Ma érkezik</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="material-symbols-outlined">hotel</span>
                        <div class="stat-value"><?php echo $available_rooms; ?></div>
                        <div class="stat-label">Szabad szoba</div>
                    </div>
                    
                    <div class="stat-card">
                        <span class="material-symbols-outlined">payments</span>
                        <div class="stat-value"><?php echo number_format($month_revenue, 0, ',', ' '); ?> Ft</div>
                        <div class="stat-label">Havi bevétel</div>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-header">
                        <span class="material-symbols-outlined">login</span>
                        Ma érkező vendégek (<?php echo count($today_guests_list); ?> fő)
                    </div>
                    <div class="panel-body">
                        <?php if (empty($today_guests_list)): ?>
                            <p style="text-align: center; padding: 30px; color: #999;">Ma nem érkezik új vendég</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Vendég</th>
                                        <th>Szoba</th>
                                        <th>Éjszaka</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_guests_list as $guest): 
                                        $nights = (strtotime($guest['check_out']) - strtotime($guest['check_in'])) / (60*60*24);
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($guest['guest_name']); ?></td>
                                        <td><?php echo $guest['room_number']; ?></td>
                                        <td><?php echo $nights; ?> éj</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: right; font-size: 13px; color: #999;">
                    Hírlevél feliratkozók: <strong><?php echo $newsletter_count; ?></strong> fő
                </div>
            <?php endif; ?>
            
            <?php if ($active_section == 'room-types'): ?>
                <?php if ($room_type_message): ?>
                    <div class="message"><?php echo $room_type_message; ?></div>
                <?php endif; ?>
                <?php if ($room_type_error): ?>
                    <div class="error"><?php echo $room_type_error; ?></div>
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="card">
                        <div class="card-header">
                            <span><?php echo $editType ? 'Szobatípus szerkesztése' : 'Új szobatípus'; ?></span>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="type_id" value="<?php echo $editType['id'] ?? 0; ?>">
                                
                                <div class="form-group">
                                    <label>Szobatípus neve *</label>
                                    <input type="text" name="type_name" required value="<?php echo htmlspecialchars($editType['type_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Rövid leírás</label>
                                    <textarea name="description" rows="3"><?php echo htmlspecialchars($editType['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Részletes leírás</label>
                                    <textarea name="detailed_description" rows="5"><?php echo htmlspecialchars($editType['detailed_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Alapár (Ft) *</label>
                                        <input type="number" name="base_price" required min="1" value="<?php echo $editType['base_price'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Max. fő</label>
                                        <input type="number" name="max_guests" min="1" value="<?php echo $editType['max_guests'] ?? 2; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Alapterület (m²)</label>
                                    <input type="number" name="size_sqm" min="1" value="<?php echo $editType['size_sqm'] ?? ''; ?>">
                                </div>
                                
                                <button type="submit" name="save_type" class="btn"><?php echo $editType ? 'Módosítás' : 'Létrehozás'; ?></button>
                                <?php if ($editType): ?>
                                    <a href="?section=room-types" class="btn" style="background: #666; color: white;">Új típus</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($editType): ?>
                        <div class="card">
                            <div class="card-header">
                                <span>Jellemzők szerkesztése</span>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="type_id" value="<?php echo $editType['id']; ?>">
                                    <input type="hidden" name="save_features" value="1">
                                    
                                    <div id="featuresList">
                                        <?php if (!empty($editFeatures)): ?>
                                            <?php foreach ($editFeatures as $feature): ?>
                                                <div class="feature-item">
                                                    <input type="text" name="features[]" value="<?php echo htmlspecialchars($feature['feature_name']); ?>" placeholder="Pl. Légkondicionáló">
                                                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="feature-item">
                                                <input type="text" name="features[]" placeholder="Pl. Légkondicionáló">
                                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="button" class="btn-add" onclick="addFeature()">+ Új jellemző</button>
                                    <button type="submit" class="btn" style="margin-top: 15px; width: 100%;">Jellemzők mentése</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <span>Meglévő szobatípusok</span>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Alapár</th>
                                        <th>Max fő</th>
                                        <th>Szobák</th>
                                        <th>Jellemzők</th>
                                        <th>Képek</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roomTypes as $type): 
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?");
                                        $stmt->execute([$type['id']]);
                                        $roomCount = $stmt->fetch()['count'];
                                        
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM room_type_features WHERE room_type_id = ?");
                                        $stmt->execute([$type['id']]);
                                        $featureCount = $stmt->fetch()['count'];
                                        
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM room_type_images WHERE room_type_id = ?");
                                        $stmt->execute([$type['id']]);
                                        $imageCount = $stmt->fetch()['count'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                                        <td><?php echo number_format($type['base_price'], 0, ',', ' '); ?> Ft</td>
                                        <td><?php echo $type['max_guests']; ?> fő</td>
                                        <td><?php echo $roomCount; ?></td>
                                        <td><?php echo $featureCount; ?></td>
                                        <td><?php echo $imageCount; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?section=room-types&edit_type=<?php echo $type['id']; ?>" class="btn btn-small">Szerkeszt</a>
                                                <?php if ($roomCount == 0): ?>
                                                    <a href="?delete_type=<?php echo $type['id']; ?>&section=room-types" class="btn btn-small btn-danger" onclick="return confirm('Biztosan törlöd?')">Törlés</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
                    `;
                    list.appendChild(newItem);
                }
                </script>
            <?php endif; ?>
            
            <?php if ($active_section == 'rooms'): ?>
                <?php if ($room_message): ?>
                    <div class="message"><?php echo $room_message; ?></div>
                <?php endif; ?>
                <?php if ($room_error): ?>
                    <div class="error"><?php echo $room_error; ?></div>
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="card">
                        <div class="card-header">
                            <span><?php echo $editRoom ? 'Szoba szerkesztése' : 'Új szoba'; ?></span>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="room_id" value="<?php echo $editRoom['id'] ?? 0; ?>">
                                
                                <div class="form-group">
                                    <label>Szobatípus *</label>
                                    <select name="room_type_id" id="room_type_select" required>
                                        <option value="">-- Válassz --</option>
                                        <?php foreach ($roomTypesList as $type): ?>
                                            <option value="<?php echo $type['id']; ?>" 
                                                data-price="<?php echo $type['base_price']; ?>"
                                                data-name="<?php echo htmlspecialchars($type['type_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($type['description']); ?>"
                                                <?php echo ($editRoom && $editRoom['room_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['type_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Szobaszám *</label>
                                        <input type="text" name="room_number" required value="<?php echo htmlspecialchars($editRoom['room_number'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Ár (Ft) *</label>
                                        <input type="number" name="price" id="room_price" required min="1" value="<?php echo $editRoom['price'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Szoba típus név *</label>
                                    <input type="text" name="type" id="room_type_name" required value="<?php echo htmlspecialchars($editRoom['type'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Leírás</label>
                                    <textarea name="description" id="room_description" rows="3"><?php echo htmlspecialchars($editRoom['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Státusz</label>
                                    <select name="status">
                                        <option value="available" <?php echo ($editRoom && $editRoom['status'] == 'available') ? 'selected' : ''; ?>>Elérhető</option>
                                        <option value="booked" <?php echo ($editRoom && $editRoom['status'] == 'booked') ? 'selected' : ''; ?>>Foglalt</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="save_room" class="btn"><?php echo $editRoom ? 'Módosítás' : 'Létrehozás'; ?></button>
                                <?php if ($editRoom): ?>
                                    <a href="?section=rooms" class="btn" style="background: #666; color: white;">Új szoba</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <span>Szobák listája</span>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Szobaszám</th>
                                        <th>Típus</th>
                                        <th>Ár</th>
                                        <th>Státusz</th>
                                        <th>Foglalások</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rooms as $room): 
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND status IN ('pending', 'confirmed')");
                                        $stmt->execute([$room['id']]);
                                        $bookingCount = $stmt->fetch()['count'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                        <td><?php echo number_format($room['price'], 0, ',', ' '); ?> Ft</td>
                                        <td>
                                            <span class="status-badge status-<?php echo $room['status']; ?>">
                                                <?php echo $room['status'] == 'available' ? 'Elérhető' : 'Foglalt'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $bookingCount; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?section=rooms&toggle_room_status=<?php echo $room['id']; ?>&status=<?php echo $room['status'] == 'available' ? 'booked' : 'available'; ?>" 
                                                   class="btn btn-small <?php echo $room['status'] == 'available' ? 'btn' : 'btn-danger'; ?>">
                                                    <?php echo $room['status'] == 'available' ? 'Foglalttá tesz' : 'Elérhetővé tesz'; ?>
                                                </a>
                                                <a href="?section=rooms&edit_room=<?php echo $room['id']; ?>" class="btn btn-small">Szerkeszt</a>
                                                <a href="?section=rooms&delete_room=<?php echo $room['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Biztosan törlöd?')">Törlés</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <script>
                document.getElementById('room_type_select')?.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    if (selected.value) {
                        document.getElementById('room_price').value = selected.dataset.price || '';
                        document.getElementById('room_type_name').value = selected.dataset.name || '';
                        document.getElementById('room_description').value = selected.dataset.description || '';
                    }
                });
                </script>
            <?php endif; ?>
            
            <?php if ($active_section == 'bookings'): ?>
                <?php if ($booking_message): ?>
                    <div class="message"><?php echo $booking_message; ?></div>
                <?php endif; ?>
                <?php if ($booking_error): ?>
                    <div class="error"><?php echo $booking_error; ?></div>
                <?php endif; ?>
                
                <div class="stats-grid" style="margin-bottom: 20px;">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Összes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">Függőben</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['confirmed']; ?></div>
                        <div class="stat-label">Megerősített</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['cancelled']; ?></div>
                        <div class="stat-label">Lemondott</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['revenue'], 0, ',', ' '); ?></div>
                        <div class="stat-label">Bevétel (Ft)</div>
                    </div>
                </div>
                
                <form method="GET" class="filter-form">
                    <input type="hidden" name="section" value="bookings">
                    
                    <div class="filter-group">
                        <label>Státusz</label>
                        <select name="status_filter">
                            <option value="">Összes</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Függőben</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Megerősített</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Lemondott</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Dátum tól</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Dátum ig</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    
                    <button type="submit" class="btn">Szűrés</button>
                    <a href="?section=bookings" class="btn" style="background: #666; color: white;">Alaphelyzet</a>
                </form>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vendég</th>
                                        <th>Szoba</th>
                                        <th>Érkezés</th>
                                        <th>Távozás</th>
                                        <th>Éj</th>
                                        <th>Vendégek</th>
                                        <th>Megjegyzés</th>
                                        <th>Összeg</th>
                                        <th>Státusz</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($bookings)): ?>
                                        <tr>
                                            <td colspan="10" style="text-align: center; padding: 40px;">Nincs megjeleníthető foglalás</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['guest_name']); ?><br>
                                                    <small><?php echo htmlspecialchars($booking['guest_email']); ?></small>
                                                </td>
                                                <td><?php echo $booking['room_number']; ?><br><small><?php echo htmlspecialchars($booking['type_name']); ?></small></td>
                                                <td><?php echo date('Y-m-d', strtotime($booking['check_in'])); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($booking['check_out'])); ?></td>
                                                <td><?php echo $booking['nights']; ?></td>
                                                <td><?php echo $booking['adults'] + $booking['children']; ?> fő</td>
                                                <td style="max-width: 250px;">
                                                    <?php 
                                                    if (!empty($booking['special_requests'])) {
                                                        echo '<textarea readonly style="width: 100%; min-height: 30px; padding: 5px; font-size: 11px; border: 1px solid #ddd; background: #f9f9f9; resize: vertical; font-family: inherit;">' . htmlspecialchars($booking['special_requests']) . '</textarea>';
                                                    } else {
                                                        echo '<span style="color: #999; font-style: italic;">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo number_format($booking['total_price'], 0, ',', ' '); ?> Ft</td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                        <?php 
                                                        switch($booking['status']) {
                                                            case 'pending': echo 'Függőben'; break;
                                                            case 'confirmed': echo 'Megerősítve'; break;
                                                            case 'cancelled': echo 'Lemondva'; break;
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <?php if ($booking['status'] != 'confirmed'): ?>
                                                            <a href="?change_status=<?php echo $booking['id']; ?>&status=confirmed&section=bookings" 
                                                               class="btn btn-small" 
                                                               onclick="return confirm('Biztosan megerősíted?')">✓ Megerősít</a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($booking['status'] != 'cancelled'): ?>
                                                            <a href="?change_status=<?php echo $booking['id']; ?>&status=cancelled&section=bookings" 
                                                               class="btn btn-small btn-danger" 
                                                               onclick="return confirm('Biztosan lemondod?')">✕ Lemond</a>
                                                        <?php endif; ?>
                                                        
                                                        <a href="?delete_booking=<?php echo $booking['id']; ?>&section=bookings" 
                                                           class="btn btn-small btn-danger" 
                                                           onclick="return confirm('Biztosan törlöd?')">🗑️ Törlés</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($active_section == 'upload'): ?>
                <?php if ($upload_message): ?>
                    <div class="message"><?php echo $upload_message; ?></div>
                <?php endif; ?>
                <?php if ($upload_error): ?>
                    <div class="error"><?php echo $upload_error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <span>Kép feltöltése szobatípushoz</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="upload_image" value="1">
                            
                            <div class="form-group">
                                <label>Válassz szobatípust:</label>
                                <select name="room_type_id" required>
                                    <option value="">-- Válassz --</option>
                                    <?php foreach ($roomTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Kép kiválasztása:</label>
                                <input type="file" name="image" accept="image/*" required onchange="previewImage(this)">
                                <img id="preview" class="image-preview" alt="Előnézet">
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="is_main" id="is_main">
                                <label for="is_main">Főkép (ez lesz a szobatípus fő képe)</label>
                            </div>
                            
                            <button type="submit" class="btn" style="margin-top: 20px;">Feltöltés</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($active_section == 'images'): ?>
                <?php if ($upload_message): ?>
                    <div class="message"><?php echo $upload_message; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <span>Szobatípusok képei</span>
                    </div>
                    <div class="card-body">
                        <?php 
                        $hasImages = false;
                        foreach ($roomTypes as $type): 
                            $stmt = $pdo->prepare("SELECT * FROM room_type_images WHERE room_type_id = ? ORDER BY is_main DESC, id DESC");
                            $stmt->execute([$type['id']]);
                            $images = $stmt->fetchAll();
                            
                            if (!empty($images)): 
                                $hasImages = true;
                        ?>
                                <h3 style="margin: 30px 0 15px; color: var(--dark-blue); border-bottom: 1px solid var(--gold); padding-bottom: 5px;">
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </h3>
                                <div class="image-gallery">
                                    <?php foreach ($images as $image): ?>
                                        <div class="gallery-item">
                                            <img src="<?php echo $image['image_url']; ?>" alt="Szoba kép">
                                            <div class="btn-group">
                                                <?php if (!$image['is_main']): ?>
                                                    <a href="?section=images&make_main=<?php echo $image['id']; ?>" class="btn btn-small">Főkép</a>
                                                <?php else: ?>
                                                    <span class="btn btn-small" style="background: var(--gold); color: var(--dark-blue);">Főkép</span>
                                                <?php endif; ?>
                                                <a href="?section=images&delete_image=<?php echo $image['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Biztosan törlöd?')">Törlés</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if (!$hasImages): ?>
                            <p style="text-align: center; padding: 60px; color: #999;">Még nincsenek feltöltött képek.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
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