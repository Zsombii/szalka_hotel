<?php
require_once 'config.php';
require_once 'admin_auth.php';

$message = '';
$error = '';

// Státusz módosítás
if (isset($_GET['change_status'])) {
    $booking_id = (int)$_GET['change_status'];
    $new_status = $_GET['status'] ?? '';
    
    if (in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $booking_id])) {
            $message = 'Foglalás státusza sikeresen módosítva!';
        } else {
            $error = 'Hiba a státusz módosítása során!';
        }
    }
}

// Foglalás törlése
if (isset($_GET['delete'])) {
    $booking_id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    if ($stmt->execute([$booking_id])) {
        $message = 'Foglalás sikeresen törölve!';
    } else {
        $error = 'Hiba a foglalás törlése során!';
    }
}

// Szűrési paraméterek
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d', strtotime('+30 days'));

// Foglalások lekérése
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

// Statisztikák
$stats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'revenue' => 0
];

foreach ($bookings as $b) {
    $stats['total']++;
    $stats[$b['status']]++;
    if ($b['status'] != 'cancelled') {
        $stats['revenue'] += $b['total_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalások kezelése - Admin</title>
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
        
        .nav-links {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            padding: 5px 10px;
            border: 1px solid var(--gold);
            transition: 0.3s;
        }
        
        .nav-links a:hover {
            background: var(--gold);
            color: var(--dark-blue);
        }
        
        .nav-links a.active {
            background: var(--gold);
            color: var(--dark-blue);
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
        
        /* Statisztika kártyák */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--cream);
            padding: 20px;
            border: 1px solid var(--gold);
            text-align: center;
        }
        
        .stat-card h3 {
            font-family: 'Playfair Display', serif;
            color: var(--dark-blue);
            font-size: 16px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--gold);
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Szűrő űrlap */
        .filter-form {
            background: var(--cream);
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--gold);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-blue);
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--gold);
            background: var(--white);
        }
        
        .btn-filter {
            background: var(--gold);
            color: var(--dark-blue);
            padding: 10px 20px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            min-width: 120px;
        }
        
        .btn-filter:hover {
            background: var(--dark-blue);
            color: var(--white);
        }
        
        .btn-reset {
            background: #666;
            color: white;
            padding: 10px 20px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-reset:hover {
            background: #444;
        }
        
        /* Táblázat */
        .table-container {
            background: var(--white);
            border: 1px solid var(--gold);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--dark-blue);
            color: var(--gold);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
            vertical-align: middle;
        }
        
        tr:hover td {
            background: var(--cream);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 0;
        }
        
        .status-pending {
            background: #ffc107;
            color: #000;
        }
        
        .status-confirmed {
            background: #28a745;
            color: #fff;
        }
        
        .status-cancelled {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            background: var(--gold);
            color: var(--dark-blue);
            border: none;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .btn-small:hover {
            background: var(--dark-blue);
            color: var(--white);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .guest-info {
            font-weight: 600;
            color: var(--dark-blue);
        }
        
        .guest-contact {
            font-size: 12px;
            color: #666;
        }
        
        .price-highlight {
            font-weight: 700;
            color: var(--gold);
        }
        
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Foglalások kezelése</h1>
            <p>Összesen <?php echo count($bookings); ?> foglalás a rendszerben</p>
            <div class="nav-links">
                <a href="admin.php">Szobatípusok</a>
                <a href="admin_rooms.php">Szobák</a>
                <a href="admin_bookings.php" class="active">Foglalások</a>
                <a href="upload.php">Képek feltöltése</a>
                <a href="index.php">← Vissza a főoldalra</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statisztikák -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Összes foglalás</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">db</div>
            </div>
            
            <div class="stat-card">
                <h3>Függőben</h3>
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">db</div>
            </div>
            
            <div class="stat-card">
                <h3>Megerősített</h3>
                <div class="stat-number"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label">db</div>
            </div>
            
            <div class="stat-card">
                <h3>Lemondott</h3>
                <div class="stat-number"><?php echo $stats['cancelled']; ?></div>
                <div class="stat-label">db</div>
            </div>
            
            <div class="stat-card">
                <h3>Bevétel*</h3>
                <div class="stat-number"><?php echo number_format($stats['revenue'], 0, ',', ' '); ?></div>
                <div class="stat-label">Ft (megerősített)</div>
            </div>
        </div>
        
        <!-- Szűrő űrlap -->
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Státusz</label>
                <select name="status">
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
            
            <button type="submit" class="btn-filter">Szűrés</button>
            <a href="admin_bookings.php" class="btn-reset">Alaphelyzet</a>
        </form>
        
        <!-- Foglalások táblázata -->
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
                        <th>Összeg</th>
                        <th>Státusz</th>
                        <th>Foglalás dátuma</th>
                        <th>Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px;">
                                Nincs megjeleníthető foglalás
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="guest-info"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                    <div class="guest-contact"><?php echo htmlspecialchars($booking['guest_email']); ?></div>
                                    <div class="guest-contact"><?php echo htmlspecialchars($booking['guest_phone']); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo $booking['room_number']; ?></strong><br>
                                    <small><?php echo htmlspecialchars($booking['type_name']); ?></small>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($booking['check_in'])); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($booking['check_out'])); ?></td>
                                <td><?php echo $booking['nights']; ?></td>
                                <td><?php echo $booking['adults'] + $booking['children']; ?> fő<br>
                                    <small>(f:<?php echo $booking['adults']; ?>, g:<?php echo $booking['children']; ?>)</small>
                                </td>
                                <td class="price-highlight"><?php echo number_format($booking['total_price'], 0, ',', ' '); ?> Ft</td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php 
                                        switch($booking['status']) {
                                            case 'pending': echo 'Függőben'; break;
                                            case 'confirmed': echo 'Megerősítve'; break;
                                            case 'cancelled': echo 'Lemondva'; break;
                                            default: echo $booking['status'];
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 5px;">
                                        <?php if ($booking['status'] != 'confirmed'): ?>
                                            <a href="?change_status=<?php echo $booking['id']; ?>&status=confirmed" 
                                               class="btn-small" 
                                               onclick="return confirm('Biztosan megerősíted ezt a foglalást?')">
                                                ✓ Megerősít
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['status'] != 'cancelled'): ?>
                                            <a href="?change_status=<?php echo $booking['id']; ?>&status=cancelled" 
                                               class="btn-small btn-delete" 
                                               onclick="return confirm('Biztosan lemondod ezt a foglalást?')">
                                                ✕ Lemond
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="?delete=<?php echo $booking['id']; ?>" 
                                           class="btn-small btn-delete" 
                                           onclick="return confirm('Biztosan törlöd ezt a foglalást? Ez a művelet nem visszavonható!')">
                                            🗑️ Törlés
                                        </a>
                                        
                                        <?php if (!empty($booking['special_requests'])): ?>
                                            <button class="btn-small" onclick="alert('<?php echo addslashes(htmlspecialchars($booking['special_requests'])); ?>')">
                                                💬 Kérés
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>