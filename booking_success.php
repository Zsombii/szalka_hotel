<?php
require_once 'config.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Foglalás adatok lekérése
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

// Napok száma
$days = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikeres foglalás - Hotel Szalka Mátészalka ****</title>
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            padding: 15px;
        }

        .success-container {
            max-width: 700px;
            width: 100%;
            background: var(--cream);
            border: 2px solid var(--gold);
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-height: 95vh;
            overflow-y: auto;
        }

        .success-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 15px;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .success-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--dark-blue);
            line-height: 1.2;
        }

        .booking-id {
            background: var(--dark-blue);
            color: var(--gold);
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 3px;
        }

        .compact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-card {
            background: var(--white);
            border: 1px solid var(--gold);
            padding: 12px;
        }

        .info-card h3 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            font-size: 16px;
            margin-bottom: 10px;
            border-left: 3px solid var(--gold);
            padding-left: 8px;
        }

        .info-row {
            display: flex;
            margin-bottom: 6px;
            font-size: 13px;
            line-height: 1.4;
        }

        .info-label {
            width: 90px;
            color: var(--gold);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            flex-shrink: 0;
        }

        .info-value {
            color: var(--dark-blue);
            font-weight: 500;
        }

        .room-mini {
            display: flex;
            gap: 10px;
            background: var(--cream);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--gold);
        }

        .room-mini img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border: 1px solid var(--gold);
        }

        .room-mini h4 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            font-size: 16px;
            margin-bottom: 3px;
        }

        .room-mini p {
            font-size: 12px;
            color: #666;
        }

        .price-summary {
            background: var(--dark-blue);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .price-label {
            color: var(--gold);
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .price-value {
            font-size: 24px;
            font-weight: 700;
            text-align: right;
        }

        .price-detail {
            font-size: 11px;
            color: rgba(255,255,255,0.7);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            border: 2px solid;
            flex: 1;
            text-align: center;
            max-width: 200px;
        }

        .btn-print {
            background: var(--gold);
            color: var(--dark-blue);
            border-color: var(--gold);
        }

        .btn-print:hover {
            background: var(--dark-blue);
            color: white;
            border-color: var(--dark-blue);
        }

        .btn-home {
            background: transparent;
            color: var(--dark-blue);
            border-color: var(--dark-blue);
        }

        .btn-home:hover {
            background: var(--dark-blue);
            color: white;
        }

        .email-note {
            background: rgba(212, 175, 55, 0.1);
            padding: 8px 12px;
            font-size: 12px;
            color: var(--dark-blue);
            margin-bottom: 15px;
            border-left: 3px solid var(--gold);
        }

        @media (max-width: 600px) {
            .compact-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                max-width: 100%;
                width: 100%;
            }
            
            .success-header h1 {
                font-size: 22px;
            }
        }
    </style>
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
            <button onclick="window.print()" class="btn btn-print">🖨️ NYOMTATÁS</button>
            <a href="index.php" class="btn btn-home">FŐOLDAL</a>
        </div>
    </div>
</body>
</html>