<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szobáink - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/rooms.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto; background-attachment: scroll;">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), url('img/lakosztaly1.3.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
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
                        <a href="rooms.php" class="active">SZOBATÍPUSOK</a>
                        <a href="wellness.php">WELLNESS</a>
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="room-types-header">
        <div class="container">
            <h1>SZOBÁINK</h1>
            <div class="subtitle">EXKLUZÍV KOMFORT MÁTÉSZALKÁN</div>
            <p class="description">
                Hotel Szalkánk 5 egyedi szobatípussal várja vendégeit. Válogasson klasszikus szobáink, 
                superior kategóriánk vagy exkluzív lakosztályaink közül. Minden szobánk légkondicionált, 
                és felszereltsége a legmagasabb igényeket is kielégíti.
            </p>
        </div>
    </div>

    <?php
    $stmt = $pdo->query("
        SELECT 
            rt.*,
            (SELECT image_url FROM room_type_images WHERE room_type_id = rt.id AND is_main = 1 LIMIT 1) as main_image,
            (SELECT COUNT(*) FROM rooms WHERE room_type_id = rt.id AND status = 'available') as available_rooms
        FROM room_types rt
        ORDER BY rt.base_price
    ");
    
    $allRoomTypes = $stmt->fetchAll();
    $totalRoomTypes = count($allRoomTypes);
    ?>
    
    <?php for ($i = 0; $i < $totalRoomTypes; $i++): 
        $roomType = $allRoomTypes[$i];
        $cardNumber = $i + 1;
        
        $mainImage = $roomType['main_image'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
        
        $featuresStmt = $pdo->prepare("
            SELECT feature_name FROM room_type_features 
            WHERE room_type_id = ? 
            ORDER BY id 
            LIMIT 4
        ");
        $featuresStmt->execute([$roomType['id']]);
        $features = $featuresStmt->fetchAll();
        
        $roomsStmt = $pdo->prepare("
            SELECT * FROM rooms 
            WHERE room_type_id = ? 
            ORDER BY room_number
        ");
        $roomsStmt->execute([$roomType['id']]);
        $rooms = $roomsStmt->fetchAll();
        
        $typeDisplay = strtoupper(str_replace(' szoba', '', $roomType['type_name']));
        
        $hasBackgroundImage = ($cardNumber == 1 || $cardNumber == 3 || $cardNumber == 5);
    ?>
    
    <div class="room-types-container <?php echo $hasBackgroundImage ? 'has-bg-image' : ''; ?>" 
         style="<?php 
            if ($hasBackgroundImage) {
                echo "background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('$mainImage'); background-attachment: fixed;";
            } else {
                echo 'background: #F8F5F0;';
            }
         ?>">
        
        <div class="container">
            <div class="room-type-card">
                <div class="room-type-image">
                    <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($roomType['type_name']); ?>">
                </div>
                
                <div class="room-type-content">
                    <div class="room-type-category"><?php echo $typeDisplay; ?></div>
                    <h2 class="room-type-name"><?php echo htmlspecialchars($roomType['type_name']); ?></h2>
                    
                    <p class="room-type-description">
                        <?php echo htmlspecialchars($roomType['description']); ?>
                    </p>
                    

                    <?php if (!empty($rooms)): ?>
                        <button onclick="openRoomModal(<?php echo $rooms[0]['id']; ?>)" class="btn-bovebben">
                            BŐVEBBEN
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php endfor; ?>

    <?php
    $availableRoomsCountStmt = $pdo->query("
        SELECT room_type_id, COUNT(*) as available_count 
        FROM rooms 
        WHERE status = 'available' 
        GROUP BY room_type_id
    ");
    $availableRoomsCount = [];
    while($row = $availableRoomsCountStmt->fetch()) {
        $availableRoomsCount[$row['room_type_id']] = $row['available_count'];
    }
    
    $allRoomsStmt = $pdo->query("
        SELECT r.id, r.room_number, r.price, r.status, r.room_type_id,
               rt.type_name, rt.max_guests, rt.size_sqm, 
               rt.description as room_type_description,
               rt.detailed_description as room_type_detailed_description
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
    ");

    while($room = $allRoomsStmt->fetch()):
        $roomFeaturesStmt = $pdo->prepare("
            SELECT feature_name FROM room_type_features 
            WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
            ORDER BY id
        ");
        $roomFeaturesStmt->execute([$room['id']]);
        $roomFeatures = $roomFeaturesStmt->fetchAll();
        
        $roomImagesStmt = $pdo->prepare("
            SELECT * FROM room_type_images 
            WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
            ORDER BY is_main DESC, sort_order
        ");
        $roomImagesStmt->execute([$room['id']]);
        $allImages = $roomImagesStmt->fetchAll();
        
        $mainImage = null;
        $galleryImages = [];
        
        foreach($allImages as $img) {
            if($img['is_main'] == 1 && !$mainImage) {
                $mainImage = $img;
            } else {
                $galleryImages[] = $img;
            }
        }
        
        if(!$mainImage && !empty($allImages)) {
            $mainImage = $allImages[0];
            for($i = 1; $i < count($allImages); $i++) {
                $galleryImages[] = $allImages[$i];
            }
        }
        
        $mainImageUrl = $mainImage['image_url'] ?? 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=1171&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
        
        $hasAvailableRoom = isset($availableRoomsCount[$room['room_type_id']]) && $availableRoomsCount[$room['room_type_id']] > 0;
    ?>
    <div id="roomModal<?php echo $room['id']; ?>" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-room-number"><?php echo $room['room_number']; ?>. számú szoba</span>
                <h2><?php echo htmlspecialchars($room['type_name']); ?></h2>
                <span class="close" onclick="closeRoomModal(<?php echo $room['id']; ?>)">&times;</span>
            </div>
            
            <div class="modal-body">
                <div class="modal-main-image">
                    <img src="<?php echo $mainImageUrl; ?>" 
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                         id="mainImage<?php echo $room['id']; ?>">
                </div>
                
                <?php if (!empty($galleryImages)): ?>
                <div class="modal-gallery">
                    <?php foreach($galleryImages as $index => $image): ?>
                        <img src="<?php echo $image['image_url']; ?>" 
                             alt="Szoba részlet" 
                             onclick="changeMainImage(<?php echo $room['id']; ?>, '<?php echo $image['image_url']; ?>')"
                             class="">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="modal-room-details">
                    <div class="modal-detail-box">
                        <h4>RÉSZLETES LEÍRÁS</h4>
                        <p>
                            <?php echo nl2br(htmlspecialchars($room['room_type_detailed_description'] ?? 'A szálloda csodálatos parkjára tekintő tágas, privát erkélyen egy pohár nemes bort kortyolva kettesben gyönyörködhetünk a természetben. A különleges hangulat tökéletes relaxációt biztosít, melyet a modern és letisztult felszereltség is elősegít.')); ?>
                        </p>
                    </div>
                    
                    <div class="modal-detail-box">
                        <h4>FELSZERELTSÉG</h4>
                        <?php if (!empty($roomFeatures)): ?>
                            <div class="modal-features-grid">
                                <?php 
                                foreach($roomFeatures as $feature):
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
                                    <div class="modal-feature-item">
                                        <span class="material-symbols-outlined"><?php echo $icon; ?></span>
                                        <span><?php echo htmlspecialchars($featureName); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Nincsenek megjeleníthető jellemzők</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="modal-price-section">
                    <div class="modal-price">
                        <span class="price-label">Ár / éj:</span>
                        <span class="price-value"><?php echo number_format($room['price'], 0, ',', ' '); ?> Ft</span>
                    </div>
                    <?php if($hasAvailableRoom): ?>
                        <a href="booking.php?room_type_id=<?php echo $room['room_type_id']; ?>" class="btn-premium">FOGLALÁS</a>
                    <?php else: ?>
                        <button class="btn-premium" style="background: #ccc; border-color: #ccc; cursor: not-allowed;" disabled>NINCS ELÉRHETŐ SZOBA</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

<script src="js/rooms.js"></script>

    <?php include 'footer.php'; ?>
</body>
</html>