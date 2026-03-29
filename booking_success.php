<?php
require_once 'config.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT b.*, r.room_number, rt.type_name, rt.base_price,
           (SELECT image_url FROM room_type_images WHERE room_type_id = rt.id AND is_main = 1 LIMIT 1) as main_image
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit;
}

$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$days = $interval->days;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikeres foglalás - Hotel Szalka Mátészalka ****</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/booking_success.css">
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">✓</div>
            <h1>SIKERES FOGLALÁS</h1>
        </div>
        
        <div class="booking-id">
            FOGLALÁSI AZONOSÍTÓ: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
        </div>
        
        <div class="email-note">
            📧 Visszaigazolás elküldve: <?php echo htmlspecialchars($booking['guest_email']); ?>
        </div>
        
        <div class="room-mini">
            <img src="<?php echo htmlspecialchars($booking['main_image'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304'); ?>" alt="Szoba">
            <div>
                <h4><?php echo htmlspecialchars($booking['type_name']); ?></h4>
                <p>Szobaszám: <?php echo $booking['room_number']; ?></p>
                <p><?php echo $booking['adults'] + $booking['children']; ?> fő • <?php echo $days; ?> éj</p>
            </div>
        </div>
        
        <div class="compact-grid">
            <div class="info-card">
                <h3>VENDÉG ADATOK</h3>
                <div class="info-row">
                    <span class="info-label">Név:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['guest_email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telefon:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['guest_phone']); ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>IDŐPONT</h3>
                <div class="info-row">
                    <span class="info-label">Érkezés:</span>
                    <span class="info-value"><?php echo date('Y. m. d.', strtotime($booking['check_in'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Távozás:</span>
                    <span class="info-value"><?php echo date('Y. m. d.', strtotime($booking['check_out'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Státusz:</span>
                    <span class="info-value" style="color: #28a745;">Megerősített</span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($booking['special_requests'])): ?>
        <div class="info-card" style="margin-bottom: 15px;">
            <h3>KÜLÖN KÉRÉSEK</h3>
            <p style="font-size: 12px;"><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="price-summary">
            <div>
                <div class="price-label">TELJES ÖSSZEG</div>
                <div class="price-detail"><?php echo number_format($booking['base_price'], 0, ',', ' '); ?> Ft/éj × <?php echo $days; ?> éj</div>
            </div>
            <div class="price-value"><?php echo number_format($booking['total_price'], 0, ',', ' '); ?> Ft</div>
        </div>
        
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-print">NYOMTATÁS</button>
            <a href="index.php" class="btn btn-home">FŐOLDAL</a>
        </div>
    </div>
</body>
</html>