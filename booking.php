<?php
require_once 'config.php';

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d', strtotime('+1 day'));
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+2 days'));
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 2;
$children = isset($_GET['children']) ? (int)$_GET['children'] : 0;

// Szoba adatok lekérése
if ($room_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name, rt.base_price, rt.max_guests, rt.size_sqm,
               (SELECT image_url FROM room_type_images WHERE room_type_id = rt.id AND is_main = 1 LIMIT 1) as main_image
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = ?
    ");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        header('Location: rooms.php');
        exit;
    }
    
    $max_guests = $room['max_guests'];
    
    // ÖSSZES azonos típusú szoba lekérése (ugyanaz a room_type_id)
    $sameTypeStmt = $pdo->prepare("
        SELECT r.id, r.room_number, r.status
        FROM rooms r
        WHERE r.room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
        ORDER BY r.room_number
    ");
    $sameTypeStmt->execute([$room_id]);
    $sameTypeRooms = $sameTypeStmt->fetchAll();
}

// Elérhetőség ellenőrzése a megadott időpontra
$is_available = true;
$availability_error = '';

if ($room_id && $check_in && $check_out) {
    // Foglalások ellenőrzése
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
    
    // Blokkolt dátumok ellenőrzése
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM unavailable_dates 
        WHERE room_id = ? AND date BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY)
    ");
    $stmt->execute([$room_id, $check_in, $check_out]);
    $unavailable = $stmt->fetch()['count'];
    
    if ($existing > 0 || $unavailable > 0) {
        $is_available = false;
        $availability_error = 'A kiválasztott időszakban a szoba már nem elérhető!';
    }
}

// Foglalás feldolgozása
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $guest_phone = trim($_POST['guest_phone'] ?? '');
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $adults = (int)($_POST['adults'] ?? 2);
    $children = (int)($_POST['children'] ?? 0);
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    // Validáció
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
        // Szoba adatok lekérése
        $stmt = $pdo->prepare("
            SELECT r.*, rt.max_guests, rt.base_price 
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE r.id = ?
        ");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch();
        
        if (!$room) {
            $error = 'A kiválasztott szoba nem található!';
        } elseif ($adults + $children > $room['max_guests']) {
            $error = 'A vendégek száma meghaladja a szoba maximális kapacitását (' . $room['max_guests'] . ' fő)!';
        } else {
            // Elérhetőség ellenőrzése
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
            
            // Blokkolt dátumok ellenőrzése
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM unavailable_dates 
                WHERE room_id = ? AND date BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY)
            ");
            $stmt->execute([$room_id, $check_in, $check_out]);
            $unavailable = $stmt->fetch()['count'];
            
            if ($existing > 0 || $unavailable > 0) {
                $error = 'A kiválasztott időszakban a szoba már nem elérhető! Kérjük, válasszon másik időpontot.';
            } else {
                // Ár számítás
                $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
                $total_price = $room['base_price'] * $days;
                
                // Foglalás mentése - azonnal megerősítve
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, adults, children, total_price, special_requests, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
                ");
                
                if ($stmt->execute([$room_id, $guest_name, $guest_email, $guest_phone, $check_in, $check_out, $adults, $children, $total_price, $special_requests])) {
                    $booking_id = $pdo->lastInsertId();
                    
                    // Átirányítás a siker oldalra
                    header('Location: booking_success.php?id=' . $booking_id);
                    exit;
                } else {
                    $error = 'Hiba történt a foglalás mentése során. Kérjük, próbálja újra!';
                }
            }
        }
    }
}

// ÖSSZES foglalás lekérése a naptárhoz (az adott szobára)
$booked_dates = [];
$unavailable_dates = [];

if ($room_id) {
    // Foglalt időszakok lekérése
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
    
    // Blokkolt dátumok lekérése
    $stmt = $pdo->prepare("
        SELECT date FROM unavailable_dates 
        WHERE room_id = ?
        ORDER BY date
    ");
    $stmt->execute([$room_id]);
    $unavailable = $stmt->fetchAll();
    
    foreach ($unavailable as $date) {
        $unavailable_dates[] = $date['date'];
    }
}

// Összes foglalt dátum (egyedi)
$all_unavailable = array_unique(array_merge($booked_dates, $unavailable_dates));
sort($all_unavailable);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        .booking-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .booking-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            letter-spacing: 4px;
        }

        .booking-header .subtitle {
            color: var(--gold);
            font-size: 16px;
            letter-spacing: 6px;
            text-transform: uppercase;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
        }

        /* Szoba előnézet */
        .room-preview {
            background: var(--cream);
            padding: 30px;
            border: 1px solid var(--gold);
            height: fit-content;
        }

        .room-preview h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            border-left: 4px solid var(--gold);
            padding-left: 15px;
        }

        .preview-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            margin-bottom: 20px;
            border: 1px solid var(--gold);
        }

        .preview-details {
            margin-bottom: 20px;
        }

        .preview-details h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--dark-blue);
            margin-bottom: 10px;
        }

        .preview-room-number {
            color: var(--gold);
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: block;
        }

        /* Szoba választó stílusok */
        .room-selector {
            margin-bottom: 15px;
        }

        .room-selector label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-blue);
        }

        .room-selector select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gold);
            background: var(--white);
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            cursor: pointer;
        }

        .room-selector select:focus {
            outline: none;
            border-color: var(--dark-blue);
        }

        .room-selector small {
            display: block;
            margin-top: 5px;
            color: var(--gold);
            font-size: 12px;
        }

        /* Jellemzők 2 oszlopban */
        .preview-features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
            list-style: none;
            padding: 0;
        }

        .preview-features-grid li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(197, 160, 89, 0.2);
            font-size: 14px;
        }

        .preview-features-grid .material-symbols-outlined {
            color: var(--gold);
            font-size: 18px;
        }

        .preview-price {
            background: var(--dark-blue);
            color: var(--white);
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        .preview-price .price-label {
            font-size: 14px;
            color: var(--gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .preview-price .price-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--white);
        }

        .preview-price .price-small {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }

        /* Foglalási űrlap */
        .booking-form {
            background: var(--cream);
            padding: 40px;
            border: 2px solid var(--gold);
        }

        .booking-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--dark-blue);
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid var(--gold);
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gold);
            background: var(--white);
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--dark-blue);
        }

        /* Dátumválasztó stílusok */
        input[type="date"] {
            position: relative;
        }

        /* Foglalt dátumok piros színűek */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
        }

        /* Egyedi stílus a foglalt dátumokhoz - ez csak a naptár megnyitásakor látszik */
        .unavailable-date {
            background-color: #ffcccc !important;
            color: #ff0000 !important;
            text-decoration: line-through;
            border-radius: 50%;
        }

        /* Nem elérhető időszak jelzése */
        .unavailable-warning {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .unavailable-warning .material-symbols-outlined {
            color: #dc3545;
        }

        .date-unavailable {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }

        .availability-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background: var(--white);
            border: 1px solid var(--gold);
            flex-wrap: wrap;
        }

        .indicator-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dot-green {
            background: #28a745;
        }

        .dot-red {
            background: #dc3545;
        }

        .dot-orange {
            background: #ffc107;
        }

        /* Telefonszám mező - csak számok */
        input[type="tel"] {
            -moz-appearance: textfield;
        }
        
        input[type="tel"]::-webkit-outer-spin-button,
        input[type="tel"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .capacity-info {
            font-size: 12px;
            color: var(--gold);
            margin-top: 5px;
            font-style: italic;
        }

        .btn-book {
            width: 100%;
            background: var(--gold);
            color: var(--dark-blue);
            padding: 16px;
            border: 2px solid var(--gold);
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        .btn-book:hover {
            background: var(--dark-blue);
            color: var(--white);
            border-color: var(--dark-blue);
        }

        .btn-book:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #ccc;
            border-color: #999;
            color: #666;
        }

        .btn-book:disabled:hover {
            background: #ccc;
            color: #666;
            border-color: #999;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        /* Saját naptár stílusok */
        .custom-calendar {
            position: absolute;
            background: white;
            border: 2px solid var(--gold);
            padding: 15px;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 300px;
            font-family: 'Montserrat', sans-serif;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: var(--dark-blue);
        }

        .calendar-header button {
            background: none;
            border: 1px solid var(--gold);
            padding: 5px 10px;
            cursor: pointer;
            color: var(--dark-blue);
            font-weight: bold;
        }

        .calendar-header button:hover {
            background: var(--gold);
            color: white;
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
            color: var(--gold);
            font-size: 12px;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            padding: 8px 5px;
            text-align: center;
            cursor: pointer;
            border-radius: 3px;
            font-size: 13px;
            transition: 0.2s;
        }

        .calendar-day.available {
            background-color: #e8f4e8;
            color: #28a745;
        }

        .calendar-day.available:hover {
            background-color: var(--gold);
            color: white;
        }

        .calendar-day.unavailable {
            background-color: #ffcccc;
            color: #ff0000;
            text-decoration: line-through;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .calendar-day.past {
            background-color: #f0f0f0;
            color: #999;
            text-decoration: line-through;
            cursor: not-allowed;
        }

        .calendar-day.selected {
            background-color: var(--gold);
            color: var(--dark-blue);
            font-weight: bold;
        }

        .calendar-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            color: var(--gold);
        }

        @media (max-width: 992px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .booking-header h1 {
                font-size: 36px;
            }
            
            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }
            
            .booking-form {
                padding: 30px 20px;
            }
            
            .preview-features-grid {
                grid-template-columns: 1fr;
            }
            
            .availability-indicator {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Egyszerűsített header hero nélkül -->
    <section class="hero-unified" style="min-height: auto; background-attachment: scroll;">
        <header class="main-header">
            <div class="container">
                <div class="header-top-row">
                </div>
                
                <div class="hotel-name">
                    <h1>HOTEL SZALKA</h1>
                    <div class="stars">★★★★</div>
                    <span class="location" id="city-gold">M Á T É S Z A L K A</span>
                </div>
                
                <div class="nav-wrapper">
                    <nav class="main-nav">
                        <a href="index.php">HOTEL</a>
                        <a href="offers.php">AJÁNLATOK</a>
                        <a href="rooms.php">SZOBATÍPUSOK</a>
                        <a href="wellness.php">WELLNESS</a>
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">SZALKA ÉLMÉNYEK</a>
                        <a href="gallery.php">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="booking-container">
        <div class="booking-header">
            <h1>FOGLALÁS</h1>
            <div class="subtitle">SZÁLLÁS FOGLALÁSA</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($room) && $room): ?>
            <div class="booking-grid">
                <!-- Szoba előnézet -->
                <div class="room-preview">
                    <h2>KIVÁLASZTOTT SZOBA</h2>
                    
                    <img src="<?php echo htmlspecialchars($room['main_image'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304'); ?>" 
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                         class="preview-image">
                    
                    <div class="preview-details">
                        <?php if (count($sameTypeRooms) > 1): ?>
                            <!-- Szoba választó legördülő menü -->
                            <div class="room-selector">
                                <label for="room_selector">Válasszon szobát:</label>
                                <select id="room_selector" name="room_id" form="bookingForm">
                                    <?php foreach ($sameTypeRooms as $roomOption): 
                                        // Elérhetőség ellenőrzése a kiválasztott időszakra
                                        $isRoomAvailable = true;
                                        if ($check_in && $check_out) {
                                            $availStmt = $pdo->prepare("
                                                SELECT COUNT(*) as count FROM bookings 
                                                WHERE room_id = ? 
                                                AND status IN ('pending', 'confirmed')
                                                AND (
                                                    (check_in <= ? AND check_out > ?) OR
                                                    (check_in < ? AND check_out >= ?) OR
                                                    (check_in >= ? AND check_out <= ?)
                                                )
                                            ");
                                            $availStmt->execute([$roomOption['id'], $check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
                                            $isRoomAvailable = ($availStmt->fetch()['count'] == 0);
                                        }
                                    ?>
                                        <option value="<?php echo $roomOption['id']; ?>" 
                                                <?php echo ($roomOption['id'] == $room_id) ? 'selected' : ''; ?>
                                                <?php echo (!$isRoomAvailable) ? 'style="color: #000000; "' : ''; ?>>
                                            <?php echo $roomOption['room_number']; ?>. szoba 
                                            <?php echo (!$isRoomAvailable) ? '(foglalt az időszakra)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Több azonos típusú szoba közül választhat</small>
                            </div>
                        <?php else: ?>
                            <span class="preview-room-number"><?php echo $room['room_number']; ?>. SZOBA</span>
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                        
                        <?php
                        // ÖSSZES jellemző lekérése
                        $featuresStmt = $pdo->prepare("
                            SELECT feature_name FROM room_type_features 
                            WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
                            ORDER BY id
                        ");
                        $featuresStmt->execute([$room_id]);
                        $features = $featuresStmt->fetchAll();
                        ?>
                        
                        <?php if (!empty($features)): ?>
                            <ul class="preview-features-grid">
                                <?php 
                                $iconMap = [
                                    'fő' => 'person',
                                    'm²' => 'square_foot',
                                    'kilátás' => 'landscape',
                                    'Légkondicionáló' => 'ac_unit',
                                    'LED TV' => 'tv',
                                    'Minibár' => 'kitchen',
                                    'Széf' => 'lock',
                                    'Fürdőköpeny' => 'shower',
                                    'Jacuzzi' => 'hot_tub',
                                    'Erkély' => 'balcony',
                                    'Kávéfőző' => 'coffee_maker',
                                    'Zuhanyzó' => 'shower',
                                    'Hajszárító' => 'self_care',
                                    'Telefon' => 'call',
                                    'Wi-Fi' => 'wifi',
                                    'Wifi' => 'wifi',
                                    'papucs' => 'flip_flop',
                                    'Vízforraló' => 'coffee_maker',
                                    'OLED TV' => 'tv',
                                    'default' => 'check'
                                ];
                                
                                foreach ($features as $feature): 
                                    $featureName = $feature['feature_name'];
                                    $icon = $iconMap['default'];
                                    
                                    foreach ($iconMap as $key => $value) {
                                        if (strpos($featureName, $key) !== false) {
                                            $icon = $value;
                                            break;
                                        }
                                    }
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

                <!-- Foglalási űrlap -->
                <div class="booking-form">
                    <h2>VENDÉGADATOK</h2>
                    
                    <?php if (!$is_available): ?>
                        <div class="unavailable-warning">
                            <span class="material-symbols-outlined">warning</span>
                            <span><?php echo $availability_error; ?> Kérjük, válasszon másik időpontot!</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="capacity-info">
                        Szoba maximális kapacitása: <strong><?php echo $room['max_guests']; ?> fő</strong>
                    </div>

                    <form method="POST" id="bookingForm">
                        <!-- A room_id-t a select fogja küldeni, ezért nem kell hidden mező -->
                        
                        <input type="hidden" id="maxGuests" value="<?php echo $room['max_guests']; ?>">
                        <input type="hidden" id="unavailableDates" value='<?php echo json_encode($all_unavailable); ?>'>

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
                                <input type="tel" name="guest_phone" id="phoneInput" required value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>" 
                                       pattern="[0-9+\-\s]+" title="Csak számokat, + és - jeleket használhat!"
                                       onkeypress="return onlyNumbers(event)"
                                       oninput="validatePhone(this)">
                            </div>
                        </div>

                        <div class="form-row-3">
                            <div class="form-group">
                                <label>Érkezés *</label>
                                <input type="text" name="check_in" id="check_in" required value="<?php echo htmlspecialchars($_POST['check_in'] ?? $check_in); ?>" 
                                       placeholder="Érkezés dátuma" readonly onclick="showCalendar('check_in')" style="cursor: pointer; background-color: #fff;">
                            </div>

                            <div class="form-group">
                                <label>Távozás *</label>
                                <input type="text" name="check_out" id="check_out" required value="<?php echo htmlspecialchars($_POST['check_out'] ?? $check_out); ?>" 
                                       placeholder="Távozás dátuma" readonly onclick="showCalendar('check_out')" style="cursor: pointer; background-color: #fff;">
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
                                <input type="text" id="totalGuests" value="<?php echo $adults + $children; ?> fő" readonly disabled style="background: #f5f5f5;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Külön kérések (opcionális)</label>
                            <textarea name="special_requests" rows="4"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-book" id="submitBtn" <?php echo !$is_available ? 'disabled' : ''; ?>>FOGLALÁS VÉGLEGESÍTÉSE</button>
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
    // Csak számokat, + és - jeleket engedélyez a telefonszám mezőben
    function onlyNumbers(event) {
        var charCode = (event.which) ? event.which : event.keyCode;
        var charStr = String.fromCharCode(charCode);
        
        // Engedélyezett karakterek: számok (0-9), +, -
        // 8 = backspace, 9 = tab, 13 = enter, 37 = balra nyíl, 39 = jobbra nyíl, 46 = delete
        if (charStr.match(/[0-9+\-]/) || charCode == 8 || charCode == 9 || charCode == 13 || charCode == 37 || charCode == 39 || charCode == 46) {
            return true;
        }
        return false;
    }

    // Valós idejű validáció és tisztítás
    function validatePhone(input) {
        // Csak számokat, + és - jeleket hagy meg
        input.value = input.value.replace(/[^0-9+\-]/g, '');
    }

    // Kapacitás kezelés
    const maxGuests = <?php echo $room['max_guests'] ?? 0; ?>;
    
    function updateChildrenOptions() {
        const adultsSelect = document.getElementById('adults');
        const childrenSelect = document.getElementById('children');
        const totalGuestsInput = document.getElementById('totalGuests');
        
        const adults = parseInt(adultsSelect.value);
        const maxChildren = maxGuests - adults;
        
        // Gyermekek legördülő frissítése
        let options = '';
        for (let i = 0; i <= maxChildren; i++) {
            options += `<option value="${i}">${i} fő</option>`;
        }
        childrenSelect.innerHTML = options;
        
        // Összes vendég frissítése
        const children = parseInt(childrenSelect.value);
        totalGuestsInput.value = (adults + children) + ' fő';
    }
    
    function updateAdultsOptions() {
        const adultsSelect = document.getElementById('adults');
        const childrenSelect = document.getElementById('children');
        const totalGuestsInput = document.getElementById('totalGuests');
        
        const adults = parseInt(adultsSelect.value);
        const children = parseInt(childrenSelect.value);
        
        // Összes vendég frissítése
        totalGuestsInput.value = (adults + children) + ' fő';
        
        // Felnőttek minimum értékének beállítása
        // Ha túl sok a gyerek, a felnőttek számát automatikusan növeljük
        if (adults + children > maxGuests) {
            const newAdults = maxGuests - children;
            adultsSelect.value = newAdults;
            totalGuestsInput.value = maxGuests + ' fő';
        }
    }

    // Szoba váltás kezelése
    document.getElementById('room_selector')?.addEventListener('change', function() {
        const selectedRoomId = this.value;
        
        // URL paraméter frissítése (hogy bookmarkolható legyen)
        const url = new URL(window.location.href);
        url.searchParams.set('room_id', selectedRoomId);
        
        // Megtartjuk a dátumokat is
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        if (checkIn) url.searchParams.set('check_in', checkIn);
        if (checkOut) url.searchParams.set('check_out', checkOut);
        
        window.location.href = url.toString();
    });

    // Foglalt dátumok tömbje
    const unavailableDates = <?php echo json_encode($all_unavailable); ?>;
    
    // Aktuális kiválasztott mező (check_in vagy check_out)
    let currentDateField = 'check_in';
    
    // Dátumok formázása YYYY-MM-DD
    function formatDate(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }
    
    // Dátum ellenőrzése, hogy foglalt-e
    function isDateUnavailable(dateStr) {
        return unavailableDates.includes(dateStr);
    }
    
    // Dátum ellenőrzése, hogy múltbeli-e
    function isPastDate(year, month, day) {
        const date = new Date(year, month, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date < today;
    }
    
    // Naptár megjelenítése
    function showCalendar(fieldId) {
        // Eltávolítjuk a régi naptárat, ha van
        const oldCalendar = document.getElementById('custom-calendar');
        if (oldCalendar) {
            oldCalendar.remove();
        }
        
        currentDateField = fieldId;
        
        // Aktuális dátum a kiválasztott mezőből, vagy mai dátum
        let currentDate = new Date();
        const fieldValue = document.getElementById(fieldId).value;
        if (fieldValue) {
            const parts = fieldValue.split('-');
            if (parts.length === 3) {
                currentDate = new Date(parts[0], parts[1] - 1, parts[2]);
            }
        }
        
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth();
        
        // Naptár létrehozása
        createCalendar(currentYear, currentMonth, fieldId);
    }
    
    // Naptár létrehozása
    function createCalendar(year, month, fieldId) {
        // Naptár konténer
        const calendar = document.createElement('div');
        calendar.id = 'custom-calendar';
        calendar.className = 'custom-calendar';
        
        // Hónap nevek
        const monthNames = ['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 
                            'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'];
        
        // Fejléc
        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.innerHTML = `
            <button id="prev-month" onclick="changeMonth(-1)">◀</button>
            <span>${monthNames[month]} ${year}</span>
            <button id="next-month" onclick="changeMonth(1)">▶</button>
        `;
        calendar.appendChild(header);
        
        // Napok fejléc
        const weekdays = ['H', 'K', 'Sze', 'Cs', 'P', 'Szo', 'V'];
        const weekdayDiv = document.createElement('div');
        weekdayDiv.className = 'weekdays';
        weekdays.forEach(day => {
            const dayDiv = document.createElement('div');
            dayDiv.textContent = day;
            weekdayDiv.appendChild(dayDiv);
        });
        calendar.appendChild(weekdayDiv);
        
        // Napok
        const daysDiv = document.createElement('div');
        daysDiv.className = 'calendar-days';
        
        // Első nap a hónapban
        const firstDay = new Date(year, month, 1);
        let startDay = firstDay.getDay(); // 0 = vasárnap, 1 = hétfő, ...
        // Átalakítás: hétfő legyen az első nap (1)
        if (startDay === 0) startDay = 6; // vasárnap -> 6
        else startDay = startDay - 1; // hétfő -> 0, kedd -> 1, ...
        
        // Üres cellák a hónap előtt
        for (let i = 0; i < startDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day';
            emptyDay.style.visibility = 'hidden';
            daysDiv.appendChild(emptyDay);
        }
        
        // Hónap napjai
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = formatDate(year, month, day);
            const isUnavailable = isDateUnavailable(dateStr);
            const isPast = isPastDate(year, month, day);
            
            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';
            dayCell.textContent = day;
            
            if (isUnavailable) {
                dayCell.classList.add('unavailable');
            } else if (isPast) {
                dayCell.classList.add('past');
            } else {
                dayCell.classList.add('available');
                
                // Kattintás esemény
                dayCell.addEventListener('click', function() {
                    document.getElementById(currentDateField).value = dateStr;
                    
                    // Ha check_in változott, állítsuk be a check_out minimumát
                    if (currentDateField === 'check_in') {
                        updateCheckOutMin(dateStr);
                    }
                    
                    // Ha check_out változott, ellenőrizzük a check_in-t
                    if (currentDateField === 'check_out') {
                        updateCheckInMax(dateStr);
                    }
                    
                    // Naptár bezárása
                    document.getElementById('custom-calendar').remove();
                    
                    // Elérhetőség ellenőrzése
                    checkAvailability();
                });
            }
            
            // Ha ez a kiválasztott dátum
            const fieldValue = document.getElementById(currentDateField).value;
            if (fieldValue === dateStr) {
                dayCell.classList.add('selected');
            }
            
            daysDiv.appendChild(dayCell);
        }
        
        calendar.appendChild(daysDiv);
        
        // Lábléc
        const footer = document.createElement('div');
        footer.className = 'calendar-footer';
        footer.innerHTML = 'Zöld: Elérhető | Piros: Foglalt';
        calendar.appendChild(footer);
        
        // Pozicionálás a mező alatt
        const targetInput = document.getElementById(currentDateField);
        const rect = targetInput.getBoundingClientRect();
        calendar.style.top = rect.bottom + window.scrollY + 10 + 'px';
        calendar.style.left = rect.left + 'px';
        
        document.body.appendChild(calendar);
        
        // Kattintás a naptáron kívülre - bezárás
        setTimeout(() => {
            document.addEventListener('click', function closeCalendar(e) {
                if (!calendar.contains(e.target) && e.target !== targetInput) {
                    calendar.remove();
                    document.removeEventListener('click', closeCalendar);
                }
            });
        }, 100);
    }
    
    // Hónap váltás
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
        
        calendar.remove();
        createCalendar(newYear, newMonth, currentDateField);
    }
    
    // Check-out minimum dátumának beállítása
    function updateCheckOutMin(checkInDate) {
        const checkOut = document.getElementById('check_out');
        const checkIn = new Date(checkInDate);
        checkIn.setDate(checkIn.getDate() + 1);
        const minCheckOut = formatDate(checkIn.getFullYear(), checkIn.getMonth(), checkIn.getDate());
        
        // Ha a jelenlegi check_out kisebb, mint a minimum, állítsuk be
        if (!checkOut.value || checkOut.value <= checkInDate) {
            checkOut.value = minCheckOut;
        }
    }
    
    // Check-in maximum dátumának beállítása
    function updateCheckInMax(checkOutDate) {
        const checkIn = document.getElementById('check_in');
        const checkOut = new Date(checkOutDate);
        checkOut.setDate(checkOut.getDate() - 1);
        const maxCheckIn = formatDate(checkOut.getFullYear(), checkOut.getMonth(), checkOut.getDate());
        
        // Ha a jelenlegi check_in nagyobb, mint a maximum, állítsuk be
        if (!checkIn.value || checkIn.value >= checkOutDate) {
            checkIn.value = maxCheckIn;
        }
    }
    
    // Elérhetőség ellenőrzése
    function checkAvailability() {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const submitBtn = document.getElementById('submitBtn');
        const warningDiv = document.querySelector('.unavailable-warning');
        
        if (!checkIn.value || !checkOut.value) return;
        
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        
        // Dátumok közötti napok ellenőrzése
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
        
        // Vizuális visszajelzés
        if (isAvailable) {
            checkIn.classList.remove('date-unavailable');
            checkOut.classList.remove('date-unavailable');
            submitBtn.disabled = false;
            
            // Ha létezik a warning div, rejtsük el
            if (warningDiv) {
                warningDiv.style.display = 'none';
            }
        } else {
            checkIn.classList.add('date-unavailable');
            checkOut.classList.add('date-unavailable');
            submitBtn.disabled = true;
            
            // Ha létezik a warning div, jelenítsük meg, ha nem, hozzuk létre
            if (warningDiv) {
                warningDiv.style.display = 'flex';
            } else {
                // Új warning div létrehozása
                const newWarning = document.createElement('div');
                newWarning.className = 'unavailable-warning';
                newWarning.innerHTML = '<span class="material-symbols-outlined">warning</span><span>A kiválasztott időszakban a szoba már nem elérhető! Kérjük, válasszon másik időpontot!</span>';
                
                const form = document.getElementById('bookingForm');
                form.parentNode.insertBefore(newWarning, form);
            }
        }
    }

    // Űrlap beküldés előtti ellenőrzés
    document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
        const adults = parseInt(document.getElementById('adults').value);
        const children = parseInt(document.getElementById('children').value);
        
        if (adults + children > maxGuests) {
            e.preventDefault();
            alert('A vendégek száma meghaladja a szoba maximális kapacitását (' + maxGuests + ' fő)!');
            return false;
        }
        
        // Elérhetőség ellenőrzése
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
            alert('A kiválasztott időszakban a szoba már nem elérhető! Kérjük, válasszon másik időpontot.');
            return false;
        }
    });
    
    // Oldalbetöltéskor ellenőrizzük az elérhetőséget
    document.addEventListener('DOMContentLoaded', function() {
        checkAvailability();
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>