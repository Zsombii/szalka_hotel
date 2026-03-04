<?php
require_once 'config.php';
require_once 'admin_auth.php';

// ========== ALAPVETŐ STATISZTIKÁK ==========

// Mai foglalások száma
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = CURDATE()");
$today_bookings = $stmt->fetch()['count'];

// Függőben lévő foglalások
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()['count'];

// Megerősített foglalások (ma érkezők)
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE check_in = ? AND status = 'confirmed'");
$stmt->execute([date('Y-m-d')]);
$check_in_today = $stmt->fetch()['count'];

// Szabad szobák száma
$stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'");
$available_rooms = $stmt->fetch()['count'];

// Havi bevétel (összesen)
$month_start = date('Y-m-01');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) as revenue FROM bookings WHERE booking_date >= ? AND status != 'cancelled'");
$stmt->execute([$month_start]);
$month_revenue = $stmt->fetch()['revenue'];

// Hírlevél feliratkozók
$newsletter_count = $pdo->query("SELECT COUNT(*) as count FROM newsletter")->fetch()['count'];

// Legutóbbi 5 foglalás
$recent_bookings = $pdo->query("
    SELECT b.*, r.room_number, rt.type_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY b.booking_date DESC
    LIMIT 5
")->fetchAll();

// Mai érkezők listája
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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Szalka</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f5f5;
        }

        /* ===== FELSŐ NAVIGÁCIÓS SÁV ===== */
        .top-nav {
            background: var(--dark-blue);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--gold);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-area h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--gold);
        }

        .logo-area .stars {
            color: var(--gold);
            font-size: 14px;
            letter-spacing: 2px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: var(--gold);
            font-weight: 600;
        }

        .logout-btn {
            background: transparent;
            border: 1px solid var(--gold);
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: var(--gold);
            color: var(--dark-blue);
        }

        /* ===== MENÜ ===== */
        .menu-nav {
            background: white;
            padding: 0 30px;
            border-bottom: 2px solid var(--gold);
        }

        .menu-nav ul {
            display: flex;
            list-style: none;
            gap: 5px;
        }

        .menu-nav a {
            display: inline-block;
            padding: 15px 20px;
            text-decoration: none;
            color: var(--dark-blue);
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 1px;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
        }

        .menu-nav a:hover,
        .menu-nav a.active {
            border-bottom-color: var(--gold);
            color: var(--gold);
        }

        /* ===== MAIN CONTENT ===== */
        .admin-main {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ===== ÜDVÖZLŐ SOR ===== */
        .welcome-row {
            background: white;
            padding: 25px 30px;
            margin-bottom: 30px;
            border-left: 5px solid var(--gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .welcome-row h2 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            font-size: 28px;
        }

        .date-badge {
            background: var(--dark-blue);
            color: var(--gold);
            padding: 8px 20px;
            font-weight: 600;
            font-size: 14px;
        }

        /* ===== STATISZTIKAI KÁRTYÁK - EGYSZERŰSÍTVE ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border: 1px solid var(--gold);
            text-align: center;
        }

        .stat-card .material-symbols-outlined {
            font-size: 32px;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-blue);
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== KÉT HASÁBOS ELRENDEZÉS ===== */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .panel {
            background: white;
            border: 1px solid var(--gold);
        }

        .panel-header {
            background: var(--dark-blue);
            color: var(--gold);
            padding: 15px 20px;
            font-weight: 600;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-header .material-symbols-outlined {
            font-size: 20px;
        }

        .panel-body {
            padding: 20px;
        }

        /* ===== TÁBLÁZAT ===== */
        .simple-table {
            width: 100%;
            border-collapse: collapse;
        }

        .simple-table th {
            text-align: left;
            padding: 10px;
            background: #f0f0f0;
            color: var(--dark-blue);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .simple-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .simple-table tr:hover td {
            background: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-confirmed {
            background: #28a745;
            color: white;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .room-badge {
            background: var(--gold);
            color: var(--dark-blue);
            padding: 2px 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }

        .btn-small {
            background: var(--gold);
            color: var(--dark-blue);
            padding: 4px 10px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-small:hover {
            background: var(--dark-blue);
            color: white;
        }

        .btn-small.delete {
            background: #dc3545;
            color: white;
        }

        .btn-small.delete:hover {
            background: #c82333;
        }

        /* ===== GYORS MŰVELETEK ===== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .action-btn {
            background: white;
            padding: 20px;
            text-align: center;
            border: 1px solid var(--gold);
            text-decoration: none;
            color: var(--dark-blue);
            transition: 0.3s;
        }

        .action-btn:hover {
            background: var(--gold);
            color: var(--dark-blue);
            transform: translateY(-3px);
        }

        .action-btn .material-symbols-outlined {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--gold);
        }

        .action-btn:hover .material-symbols-outlined {
            color: var(--dark-blue);
        }

        .action-btn span:last-child {
            display: block;
            font-weight: 600;
            font-size: 13px;
        }

        /* ===== RESZPONZÍV ===== */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .two-columns {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .menu-nav ul {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .welcome-row {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Felső navigációs sáv -->
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
    
    <!-- Menü -->
    <div class="menu-nav">
        <ul>
            <li><a href="admin.php" class="active">Dashboard</a></li>
            <li><a href="admin_room_types.php">Szobatípusok</a></li>
            <li><a href="admin_rooms.php">Szobák</a></li>
            <li><a href="admin_bookings.php">Foglalások</a></li>
            <li><a href="upload.php">Kép feltöltés</a></li>
        </ul>
    </div>
    
    <!-- Fő tartalom -->
    <div class="admin-main">
        <!-- Üdvözlő sor -->
        <div class="welcome-row">
            <h2>Üdv újra, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</h2>
            <div class="date-badge">📅 <?php echo date('Y. F j., l'); ?></div>
        </div>
        
        <!-- Statisztikai kártyák - csak a fontos adatok -->
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
        
        <!-- Két hasáb: Mai érkezők + Legutóbbi foglalások -->
        <div class="two-columns">
            <!-- Mai érkezők -->
            <div class="panel">
                <div class="panel-header">
                    <span class="material-symbols-outlined">login</span>
                    Ma érkező vendégek (<?php echo count($today_guests_list); ?> fő)
                </div>
                <div class="panel-body">
                    <?php if (empty($today_guests_list)): ?>
                        <p style="text-align: center; padding: 30px; color: #999;">Ma nem érkezik új vendég</p>
                    <?php else: ?>
                        <table class="simple-table">
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
                                    <td><?php echo $guest['room_number']; ?> <span class="room-badge"><?php echo $guest['type_name']; ?></span></td>
                                    <td><?php echo $nights; ?> éj</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Legutóbbi foglalások -->
            <div class="panel">
                <div class="panel-header">
                    <span class="material-symbols-outlined">history</span>
                    Legutóbbi foglalások
                </div>
                <div class="panel-body">
                    <?php if (empty($recent_bookings)): ?>
                        <p style="text-align: center; padding: 30px; color: #999;">Nincs megjeleníthető foglalás</p>
                    <?php else: ?>
                        <table class="simple-table">
                            <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Vendég</th>
                                    <th>Szoba</th>
                                    <th>Státusz</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><?php echo date('m-d H:i', strtotime($booking['booking_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                    <td><?php echo $booking['room_number']; ?></td>
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
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Gyors műveletek -->
        <div class="quick-actions">
            <a href="admin_room_types.php" class="action-btn">
                <span class="material-symbols-outlined">meeting_room</span>
                <span>Szobatípusok</span>
            </a>
            <a href="admin_rooms.php" class="action-btn">
                <span class="material-symbols-outlined">door_front</span>
                <span>Szobák</span>
            </a>
            <a href="admin_bookings.php" class="action-btn">
                <span class="material-symbols-outlined">book_online</span>
                <span>Foglalások</span>
            </a>
            <a href="upload.php" class="action-btn">
                <span class="material-symbols-outlined">upload</span>
                <span>Kép feltöltés</span>
            </a>
        </div>
        
        <!-- Hírlevél statisztika (kis kiegészítő) -->
        <div style="margin-top: 20px; text-align: right; font-size: 13px; color: #999;">
            📧 Hírlevél feliratkozók: <strong><?php echo $newsletter_count; ?></strong> fő
        </div>
    </div>
</body>
</html>