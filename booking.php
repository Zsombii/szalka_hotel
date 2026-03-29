<?php
require_once 'config.php';

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$room_type_id = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d', strtotime('+1 day'));
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+2 days'));
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 2;
$children = isset($_GET['children']) ? (int)$_GET['children'] : 0;

if ($room_type_id > 0 && $room_id == 0) {
    $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_type_id = ? AND status = 'available' ORDER BY room_number LIMIT 1");
    $stmt->execute([$room_type_id]);
    $firstAvailable = $stmt->fetch();
    
    if ($firstAvailable) {
        $room_id = $firstAvailable['id'];
    } else {
        header('Location: rooms.php?error=noroom');
        exit;
    }
}

if ($room_id) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        header('Location: rooms.php');
        exit;
    }
    
    if (!empty($room['room_type_id'])) {
        $typeStmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
        $typeStmt->execute([$room['room_type_id']]);
        $typeData = $typeStmt->fetch();
        
        if ($typeData) {
            $room['type_name'] = $typeData['type_name'];
            $room['base_price'] = $typeData['base_price'];
            $room['max_guests'] = $typeData['max_guests'];
            $room['size_sqm'] = $typeData['size_sqm'];
        } else {
            $room['type_name'] = $room['type'];
            $room['base_price'] = $room['price'];
            $room['max_guests'] = 2;
            $room['size_sqm'] = 20;
        }
        
        $imgStmt = $pdo->prepare("SELECT image_url FROM room_type_images WHERE room_type_id = ? AND is_main = 1 LIMIT 1");
        $imgStmt->execute([$room['room_type_id']]);
        $imgData = $imgStmt->fetch();
        $room['main_image'] = $imgData ? $imgData['image_url'] : null;
        
        $featuresStmt = $pdo->prepare("SELECT feature_name FROM room_type_features WHERE room_type_id = ? ORDER BY id");
        $featuresStmt->execute([$room['room_type_id']]);
        $room['features'] = $featuresStmt->fetchAll();
        
        $sameStmt = $pdo->prepare("SELECT id, room_number, status FROM rooms WHERE room_type_id = ? ORDER BY room_number");
        $sameStmt->execute([$room['room_type_id']]);
        $sameTypeRooms = $sameStmt->fetchAll();
        
        $hasAvailableRoomInType = false;
        foreach ($sameTypeRooms as $r) {
            if ($r['status'] == 'available') {
                $hasAvailableRoomInType = true;
                break;
            }
        }
    } else {
        $room['type_name'] = $room['type'];
        $room['base_price'] = $room['price'];
        $room['max_guests'] = 2;
        $room['size_sqm'] = 20;
        $room['main_image'] = null;
        $room['features'] = [];
        $sameTypeRooms = [];
        $hasAvailableRoomInType = ($room['status'] == 'available');
    }
    
    $max_guests = $room['max_guests'];
}

$is_available = true;
$availability_error = '';

if ($room_id && $check_in && $check_out && isset($room) && $room) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE room_id = ? 
        AND status IN ('pending', 'confirmed')
        AND (
            (check_in <= ? AND check_out > ?) OR
            (check_in < ? AND check_out >= ?) OR
            (check_in >= ? AND check_out <= ?)
        )
    ");
    $stmt->execute([$room_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
    $existing = $stmt->fetch()['count'];
    
    if ($existing > 0) {
        $is_available = false;
        $availability_error = 'A kiválasztott időszakban a szoba már nem elérhető!';
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $guest_phone = trim($_POST['guest_phone'] ?? '');
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $adults = (int)($_POST['adults'] ?? 2);
    $children = (int)($_POST['children'] ?? 0);
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    if (empty($guest_name) || empty($guest_email) || empty($guest_phone) || empty($check_in) || empty($check_out)) {
        $error = 'Minden mező kitöltése kötelező!';
    } elseif (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen email cím!';
    } elseif (!preg_match('/^[0-9+\-\s]+$/', $guest_phone)) {
        $error = 'A telefonszám csak számokat, + és - jeleket tartalmazhat!';
    } elseif (strtotime($check_in) < strtotime('today')) {
        $error = 'A bejelentkezés dátuma nem lehet múltbeli!';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $error = 'A kijelentkezés dátuma nem lehet korábbi vagy egyenlő a bejelentkezéssel!';
    } else {
        $roomData = null;
        
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $roomData = $stmt->fetch();
        
        if ($roomData && !empty($roomData['room_type_id'])) {
            $typeStmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
            $typeStmt->execute([$roomData['room_type_id']]);
            $typeData = $typeStmt->fetch();
            
            if ($typeData) {
                $roomData['max_guests'] = $typeData['max_guests'];
                $roomData['base_price'] = $typeData['base_price'];
            } else {
                $roomData['max_guests'] = 2;
                $roomData['base_price'] = $roomData['price'];
            }
        } elseif ($roomData) {
            $roomData['max_guests'] = 2;
            $roomData['base_price'] = $roomData['price'];
        }
        
        if (!$roomData) {
            $error = 'A kiválasztott szoba nem található!';
        } elseif ($roomData['status'] != 'available') {
            $error = 'Ez a szoba jelenleg nem fogad foglalásokat! (Státusz: ' . ($roomData['status'] == 'booked' ? 'Foglalt (admin által)' : 'Karbantartás alatt') . ')';
        } elseif ($adults + $children > $roomData['max_guests']) {
            $error = 'A vendégek száma meghaladja a szoba maximális kapacitását (' . $roomData['max_guests'] . ' fő)!';
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE room_id = ? 
                AND status IN ('pending', 'confirmed')
                AND (
                    (check_in <= ? AND check_out > ?) OR
                    (check_in < ? AND check_out >= ?) OR
                    (check_in >= ? AND check_out <= ?)
                )
            ");
            $stmt->execute([$room_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
            $existing = $stmt->fetch()['count'];
            
            if ($existing > 0) {
                $error = 'A kiválasztott időszakban a szoba már nem elérhető! Kérjük, válasszon másik időpontot.';
            } else {
                $check_in_date = date('Y-m-d', strtotime($check_in));
                $check_out_date = date('Y-m-d', strtotime($check_out));
                $days = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
                $total_price = $roomData['base_price'] * $days;
                
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, adults, children, total_price, special_requests, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
                ");
                
                if ($stmt->execute([$room_id, $guest_name, $guest_email, $guest_phone, $check_in, $check_out, $adults, $children, $total_price, $special_requests])) {
                    $booking_id = $pdo->lastInsertId();
                    header('Location: booking_success.php?id=' . $booking_id);
                    exit;
                } else {
                    $error = 'Hiba történt a foglalás mentése során. Kérjük, próbálja újra!';
                }
            }
        }
    }
}

$booked_dates = [];

if ($room_id) {
    $stmt = $pdo->prepare("
        SELECT check_in, check_out FROM bookings 
        WHERE room_id = ? AND status IN ('pending', 'confirmed')
        ORDER BY check_in
    ");
    $stmt->execute([$room_id]);
    $bookings = $stmt->fetchAll();
    
    foreach ($bookings as $booking) {
        $start = new DateTime($booking['check_in']);
        $end = new DateTime($booking['check_out']);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
        foreach ($period as $date) {
            $booked_dates[] = $date->format('Y-m-d');
        }
    }
}

$all_unavailable = array_unique($booked_dates);
sort($all_unavailable);

$initial_days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
$initial_total = $room['base_price'] * $initial_days;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/booking.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto;">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), url('img/recepcio2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="container">
                <div class="hotel-name">
                    <h1>HOTEL SZALKA</h1>
                    <div class="stars">★★★★</div>
                    <span class="location" style="color: #c5a059; letter-spacing: 27px;">MÁTÉSZALKA</span>
                </div>
                <div class="nav-wrapper">
                    <nav class="main-nav">
                        <a href="index.php">HOTEL</a>
                        <a href="rooms.php">SZOBATÍPUSOK</a>
                        <a href="wellness.php">WELLNESS</a>
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="booking-container">
        <div class="booking-header">
            <h1>FOGLALÁS</h1>
            <div class="subtitle">SZOBA FOGLALÁSA</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($room) && $room): ?>
            <?php 
            $hasAvailableRoom = false;
            if (isset($sameTypeRooms) && !empty($sameTypeRooms)) {
                foreach ($sameTypeRooms as $r) {
                    if ($r['status'] == 'available') {
                        $hasAvailableRoom = true;
                        break;
                    }
                }
            } else {
                $hasAvailableRoom = ($room['status'] == 'available');
            }
            
            if (!$hasAvailableRoom): 
            ?>
                <div class="room-unavailable-badge">
                    <span class="material-symbols-outlined">block</span>
                    <span>Ebből a szobatípusból jelenleg egyetlen szoba sem elérhető!</span>
                </div>
            <?php endif; ?>
            
            <div class="booking-grid">
                <div class="room-preview">
                    <h2>KIVÁLASZTOTT SZOBA</h2>
                    
                    <img src="<?php echo htmlspecialchars($room['main_image'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304'); ?>" 
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                         class="preview-image">
                    
                    <div class="preview-details">
                        <?php if (isset($sameTypeRooms) && count($sameTypeRooms) > 1): ?>
                            <div class="room-selector">
                                <label for="room_selector">Válasszon szobaszámot:</label>
                                <select id="room_selector">
                                    <?php foreach ($sameTypeRooms as $roomOption): ?>
                                        <?php 
                                        $isAvailable = ($roomOption['status'] == 'available');
                                        $selected = ($roomOption['id'] == $room_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $roomOption['id']; ?>" 
                                                <?php echo $selected; ?>
                                                <?php echo !$isAvailable ? 'disabled' : ''; ?>
                                                style="<?php echo !$isAvailable ? 'color: #999; background: #f0f0f0;' : ''; ?>">
                                            <?php echo $roomOption['room_number']; ?>. szoba
                                            <?php echo !$isAvailable ? ' (nem elérhető)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Csak a nem szürkített szobák választhatók</small>
                            </div>
                        <?php else: ?>
                            <span class="preview-room-number"><?php echo $room['room_number']; ?>. SZOBA</span>
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                        
                        <?php if (!empty($room['features'])): ?>
                            <ul class="preview-features-grid">
                                <?php 
                                foreach($room['features'] as $feature):
                                    $featureName = $feature['feature_name'];
                                    $icon = 'check';
                                    
                                    if(strpos($featureName, 'fő') !== false || strpos($featureName, 'fő részére') !== false) $icon = 'person';
                                    elseif(strpos($featureName, 'm²') !== false) $icon = 'square_foot';
                                    elseif(strpos($featureName, 'kilátás') !== false) $icon = 'landscape';
                                    elseif(strpos($featureName, 'Légkondicionáló') !== false) $icon = 'ac_unit';
                                    elseif(strpos($featureName, 'LED TV') !== false) $icon = 'tv';
                                    elseif(strpos($featureName, 'TV') !== false) $icon = 'tv';
                                    elseif(strpos($featureName, 'Minibár') !== false) $icon = 'kitchen';
                                    elseif(strpos($featureName, 'Széf') !== false) $icon = 'lock';
                                    elseif(strpos($featureName, 'Fürdőköpeny') !== false) $icon = 'shower';
                                    elseif(strpos($featureName, 'Jacuzzi') !== false) $icon = 'hot_tub';
                                    elseif(strpos($featureName, 'Erkély') !== false) $icon = 'balcony';
                                    elseif(strpos($featureName, 'Kávéfőző') !== false) $icon = 'coffee_maker';
                                    elseif(strpos($featureName, 'Zuhanyzó') !== false) $icon = 'shower';
                                    elseif(strpos($featureName, 'Hajszárító') !== false) $icon = 'self_care';
                                    elseif(strpos($featureName, 'Telefon') !== false) $icon = 'call';
                                    elseif(strpos($featureName, 'Wi-Fi') !== false || strpos($featureName, 'Wifi') !== false) $icon = 'wifi';
                                    elseif(strpos($featureName, 'papucs') !== false) $icon = 'flip_flop';
                                    elseif(strpos($featureName, 'Vízforraló') !== false) $icon = 'coffee_maker';
                                ?>
                                    <li>
                                        <span class="material-symbols-outlined"><?php echo $icon; ?></span>
                                        <span><?php echo htmlspecialchars($featureName); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <div class="preview-price">
                            <div class="price-label">ÁR / ÉJ</div>
                            <div class="price-value"><?php echo number_format($room['base_price'], 0, ',', ' '); ?> Ft</div>
                        </div>
                    </div>
                </div>

                <div class="booking-form">
                    <h2>VENDÉGADATOK</h2>
                    
                    <?php if (!$is_available && $room['status'] == 'available'): ?>
                        <div class="unavailable-warning">
                            <span class="material-symbols-outlined">warning</span>
                            <span><?php echo $availability_error; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="capacity-info">
                        Maximális kapacitás: <strong><?php echo $room['max_guests']; ?> fő</strong>
                    </div>

                    <form method="POST" id="bookingForm">
                        <input type="hidden" name="room_id" id="selected_room_id" value="<?php echo $room_id; ?>">
                        <input type="hidden" id="maxGuests" value="<?php echo $room['max_guests']; ?>">
                        <input type="hidden" id="unavailableDates" value='<?php echo json_encode($all_unavailable); ?>'>
                        <input type="hidden" id="pricePerNight" value="<?php echo $room['base_price']; ?>">

                        <div class="form-group">
                            <label>Teljes név *</label>
                            <input type="text" name="guest_name" required value="<?php echo htmlspecialchars($_POST['guest_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email cím *</label>
                                <input type="email" name="guest_email" required value="<?php echo htmlspecialchars($_POST['guest_email'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Telefonszám *</label>
                                <input type="tel" name="guest_phone" pattern="[0-9+\-\s]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Érkezés *</label>
                                <input type="text" name="check_in" id="check_in" required value="<?php echo htmlspecialchars($_POST['check_in'] ?? $check_in); ?>" 
                                       readonly onclick="showCalendar('check_in')" style="cursor: pointer;">
                            </div>

                            <div class="form-group">
                                <label>Távozás *</label>
                                <input type="text" name="check_out" id="check_out" required value="<?php echo htmlspecialchars($_POST['check_out'] ?? $check_out); ?>" 
                                       readonly onclick="showCalendar('check_out')" style="cursor: pointer;">
                            </div>

                            <div class="form-group">
                                <label>Felnőttek *</label>
                                <select name="adults" id="adults" onchange="updateChildrenOptions()">
                                    <?php for ($i = 1; $i <= $room['max_guests']; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($adults == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> fő
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Gyermekek (0-16 év)</label>
                                <select name="children" id="children" onchange="updateAdultsOptions()">
                                    <?php
                                    $max_children = $room['max_guests'] - $adults;
                                    for ($i = 0; $i <= $max_children; $i++): 
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($children == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> fő
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Összesen</label>
                                <input type="text" id="totalGuests" value="<?php echo $adults + $children; ?> fő" readonly disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Külön kérések</label>
                            <textarea name="special_requests" rows="3"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                        </div>

                        <div class="price-summary">
                            <div class="price-label">VÉGÖSSZEG</div>
                            <div class="price-detail" id="priceDetail">
                                <?php echo number_format($room['base_price'], 0, ',', ' '); ?> Ft/éj × <?php echo $initial_days; ?> éj
                            </div>
                            <div class="price-value" id="totalPrice">
                                <?php echo number_format($initial_total, 0, ',', ' '); ?> Ft
                            </div>
                        </div>

                        <?php
                        $canBook = ($room['status'] == 'available' && $is_available);
                        $buttonText = 'FOGLALÁS VÉGLEGESÍTÉSE';
                        
                        if ($room['status'] != 'available') {
                            $buttonText = 'A SZOBA NEM ELÉRHETŐ';
                            $canBook = false;
                        } elseif (!$is_available) {
                            $buttonText = 'AZ IDŐPONT NEM ELÉRHETŐ';
                            $canBook = false;
                        }
                        ?>
                        
                        <button type="submit" class="btn-book" id="submitBtn" <?php echo !$canBook ? 'disabled' : ''; ?>>
                            <?php echo $buttonText; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: var(--cream); border: 1px solid var(--gold);">
                <h3 style="margin-bottom: 20px; color: var(--dark-blue);">Nincs kiválasztott szoba</h3>
                <p style="margin-bottom: 30px;">Kérjük, válasszon szobát a szobáink közül!</p>
                <a href="rooms.php" class="btn-premium">SZOBÁK MEGTEKINTÉSE</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    const maxGuests = <?php echo $room['max_guests'] ?? 0; ?>;
    const unavailableDates = <?php echo json_encode($all_unavailable); ?>;
    const pricePerNight = <?php echo $room['base_price']; ?>;
    let currentDateField = 'check_in';
    
    function updateChildrenOptions() {
        const adults = parseInt(document.getElementById('adults').value);
        const childrenSelect = document.getElementById('children');
        const currentChildren = parseInt(childrenSelect.value) || 0;
        const maxChildren = maxGuests - adults;
        
        let newChildren = currentChildren;
        if (currentChildren > maxChildren) {
            newChildren = maxChildren;
        }
        
        let options = '';
        for (let i = 0; i <= maxChildren; i++) {
            const selected = (i === newChildren) ? 'selected' : '';
            options += `<option value="${i}" ${selected}>${i} fő</option>`;
        }
        childrenSelect.innerHTML = options;
        updateTotalGuests();
    }
    
    function updateAdultsOptions() {
        const children = parseInt(document.getElementById('children').value);
        const adultsSelect = document.getElementById('adults');
        const currentAdults = parseInt(adultsSelect.value) || 2;
        const maxAdults = maxGuests - children;
        
        let newAdults = currentAdults;
        if (currentAdults > maxAdults) {
            newAdults = maxAdults;
        }
        
        let options = '';
        for (let i = 1; i <= maxGuests; i++) {
            if (i <= maxGuests - children) {
                const selected = (i === newAdults) ? 'selected' : '';
                options += `<option value="${i}" ${selected}>${i} fő</option>`;
            }
        }
        adultsSelect.innerHTML = options;
        updateTotalGuests();
    }
    
    function updateTotalGuests() {
        const adults = parseInt(document.getElementById('adults').value);
        const children = parseInt(document.getElementById('children').value);
        document.getElementById('totalGuests').value = (adults + children) + ' fő';
    }
    
    document.getElementById('room_selector')?.addEventListener('change', function() {
        const selectedRoomId = this.value;
        document.getElementById('selected_room_id').value = selectedRoomId;
        
        const url = new URL(window.location.href);
        url.searchParams.set('room_id', selectedRoomId);
        window.location.href = url.toString();
    });
    
    function formatDate(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }
    
    function isDateUnavailable(dateStr) {
        return unavailableDates.includes(dateStr);
    }
    
    function isPastDate(year, month, day) {
        const date = new Date(year, month, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date < today;
    }

    function isFutureDate(year, month, day) {
        const date = new Date(year, month, day);
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        maxDate.setHours(0, 0, 0, 0);
        return date > maxDate;
    }
    
    function showCalendar(fieldId) {
        const oldCalendar = document.getElementById('custom-calendar');
        if (oldCalendar) oldCalendar.remove();
        
        currentDateField = fieldId;
        
        let currentDate = new Date();
        const fieldValue = document.getElementById(fieldId).value;
        if (fieldValue) {
            const parts = fieldValue.split('-');
            if (parts.length === 3) {
                currentDate = new Date(parts[0], parts[1] - 1, parts[2]);
            }
        }
        
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        
        if (currentDate > maxDate) {
            currentDate = maxDate;
        }
        if (currentDate < new Date()) {
            currentDate = new Date();
        }
        
        createCalendar(currentDate.getFullYear(), currentDate.getMonth(), fieldId);
    }
    
    function createCalendar(year, month, fieldId) {
        const calendar = document.createElement('div');
        calendar.id = 'custom-calendar';
        calendar.className = 'custom-calendar';
        
        const monthNames = ['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 
                            'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'];
        
        calendar.innerHTML = `
            <div class="calendar-header">
                <button onclick="changeMonth(-1)">◀</button>
                <span>${monthNames[month]} ${year}</span>
                <button onclick="changeMonth(1)">▶</button>
            </div>
            <div class="weekdays">
                <div>H</div><div>K</div><div>Sze</div><div>Cs</div><div>P</div><div>Szo</div><div>V</div>
            </div>
            <div class="calendar-days" id="calendar-days"></div>
        `;
        
        const daysDiv = calendar.querySelector('#calendar-days');
        
        const firstDay = new Date(year, month, 1);
        let startDay = firstDay.getDay();
        if (startDay === 0) startDay = 6;
        else startDay = startDay - 1;
        
        for (let i = 0; i < startDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day';
            emptyDay.style.visibility = 'hidden';
            daysDiv.appendChild(emptyDay);
        }
        
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = formatDate(year, month, day);
            const isUnavailable = isDateUnavailable(dateStr);
            const isPast = isPastDate(year, month, day);
            const isFuture = isFutureDate(year, month, day);
            
            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';
            dayCell.textContent = day;
            
            if (isPast) {
                dayCell.classList.add('past');
            }
            else if (isFuture) {
                dayCell.classList.add('past');
                dayCell.title = 'Csak 1 évvel előre lehet foglalni';
            }
            else if (isUnavailable) {
                dayCell.classList.add('unavailable');
            } 
            else {
                dayCell.classList.add('available');
                dayCell.onclick = function() {
                    document.getElementById(currentDateField).value = dateStr;
                    if (currentDateField === 'check_in') {
                        updateCheckOutMin(dateStr);
                    }
                    if (currentDateField === 'check_out') {
                        updateCheckInMax(dateStr);
                    }
                    calendar.remove();
                    checkAvailability();
                    updateTotalPrice();
                };
            }
            
            if (document.getElementById(currentDateField).value === dateStr) {
                dayCell.classList.add('selected');
            }
            
            daysDiv.appendChild(dayCell);
        }
        
        const targetInput = document.getElementById(currentDateField);
        const rect = targetInput.getBoundingClientRect();
        calendar.style.top = rect.bottom + window.scrollY + 10 + 'px';
        calendar.style.left = rect.left + 'px';
        
        document.body.appendChild(calendar);
        
        setTimeout(() => {
            document.addEventListener('click', function closeCalendar(e) {
                if (!calendar.contains(e.target) && e.target !== targetInput) {
                    calendar.remove();
                    document.removeEventListener('click', closeCalendar);
                }
            });
        }, 100);
    }
    
    function changeMonth(direction) {
        const calendar = document.getElementById('custom-calendar');
        if (!calendar) return;
        
        const headerText = calendar.querySelector('.calendar-header span').textContent;
        const parts = headerText.split(' ');
        const monthNames = ['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 
                            'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'];
        const currentMonth = monthNames.indexOf(parts[0]);
        const currentYear = parseInt(parts[1]);
        
        let newYear = currentYear;
        let newMonth = currentMonth + direction;
        
        if (newMonth < 0) {
            newMonth = 11;
            newYear--;
        } else if (newMonth > 11) {
            newMonth = 0;
            newYear++;
        }
        
        const today = new Date();
        const maxYear = today.getFullYear() + 1;
        const maxMonth = today.getMonth();
        
        if (newYear > maxYear) {
            return;
        }
        if (newYear === maxYear && newMonth > maxMonth) {
            return;
        }
        if (newYear < today.getFullYear()) {
            return;
        }
        if (newYear === today.getFullYear() && newMonth < today.getMonth()) {
            return;
        }
        
        calendar.remove();
        createCalendar(newYear, newMonth, currentDateField);
    }
    
    function updateCheckOutMin(checkInDate) {
        const checkOut = document.getElementById('check_out');
        const checkIn = new Date(checkInDate);
        checkIn.setDate(checkIn.getDate() + 1);
        const minCheckOut = formatDate(checkIn.getFullYear(), checkIn.getMonth(), checkIn.getDate());
        
        if (!checkOut.value || checkOut.value <= checkInDate) {
            checkOut.value = minCheckOut;
        }
    }
    
    function updateCheckInMax(checkOutDate) {
        const checkIn = document.getElementById('check_in');
        const checkOut = new Date(checkOutDate);
        checkOut.setDate(checkOut.getDate() - 1);
        const maxCheckIn = formatDate(checkOut.getFullYear(), checkOut.getMonth(), checkOut.getDate());
        
        if (!checkIn.value || checkIn.value >= checkOutDate) {
            checkIn.value = maxCheckIn;
        }
    }
    
    function checkAvailability() {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!checkIn.value || !checkOut.value) return;
        
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        
        let isAvailable = true;
        let currentDate = new Date(start);
        
        while (currentDate < end) {
            const dateStr = currentDate.toISOString().split('T')[0];
            if (unavailableDates.includes(dateStr)) {
                isAvailable = false;
                break;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        if (isAvailable) {
            checkIn.classList.remove('date-unavailable');
            checkOut.classList.remove('date-unavailable');
            <?php if ($room['status'] == 'available'): ?>
            submitBtn.disabled = false;
            submitBtn.textContent = 'FOGLALÁS VÉGLEGESÍTÉSE';
            <?php endif; ?>
        } else {
            checkIn.classList.add('date-unavailable');
            checkOut.classList.add('date-unavailable');
            submitBtn.disabled = true;
            submitBtn.textContent = 'AZ IDŐPONT NEM ELÉRHETŐ';
        }
    }
    
    function updateTotalPrice() {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        
        if (!checkIn.value || !checkOut.value) return;
        
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        
        if (end <= start) return;
        
        const nights = Math.round((end - start) / (1000 * 60 * 60 * 24));
        const total = pricePerNight * nights;
        
        document.getElementById('priceDetail').innerHTML = `${pricePerNight.toLocaleString('hu-HU')} Ft/éj × ${nights} éj`;
        document.getElementById('totalPrice').innerHTML = `${total.toLocaleString('hu-HU')} Ft`;
    }
    
    document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
        const roomSelector = document.getElementById('room_selector');
        if (roomSelector) {
            const selectedOption = roomSelector.options[roomSelector.selectedIndex];
            if (selectedOption.disabled) {
                e.preventDefault();
                alert('A kiválasztott szoba nem elérhető! Kérjük, válasszon másik szobát a listából.');
                return false;
            }
        }
        
        const adults = parseInt(document.getElementById('adults').value);
        const children = parseInt(document.getElementById('children').value);
        
        if (adults + children > maxGuests) {
            e.preventDefault();
            alert('A vendégek száma meghaladja a szoba maximális kapacitását (' + maxGuests + ' fő)!');
            return false;
        }
        
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        
        if (!checkIn.value || !checkOut.value) {
            e.preventDefault();
            alert('Kérjük, válassza ki az érkezés és távozás dátumát!');
            return false;
        }
        
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        
        if (end <= start) {
            e.preventDefault();
            alert('A távozás dátuma nem lehet korábbi vagy egyenlő az érkezés dátumával!');
            return false;
        }
        
        let isAvailable = true;
        let currentDate = new Date(start);
        
        while (currentDate < end) {
            const dateStr = currentDate.toISOString().split('T')[0];
            if (unavailableDates.includes(dateStr)) {
                isAvailable = false;
                break;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        if (!isAvailable) {
            e.preventDefault();
            alert('A kiválasztott időszakban a szoba már nem elérhető!');
            return false;
        }
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!isset($room) || $room['status'] == 'available'): ?>
        checkAvailability();
        <?php endif; ?>
        updateTotalPrice();
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>