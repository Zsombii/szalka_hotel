<?php
require_once 'config.php';

// Itt NEM include-oljuk a header.php-t, mert az tartalmazza a hero szekciót
// Közvetlenül beépítjük a header részleteit hero nélkül
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szobáink - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        /* Google Icons beállítások */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 20;
            color: var(--gold);
            font-size: 20px;
            vertical-align: middle;
        }
        
        /* ========== SZOBATÍPUSOK OLDAL SPECIÁLIS STÍLUSAI ========== */
        .room-types-header {
            text-align: center;
            padding: 60px 0 30px;
            background: var(--cream);
        }

        .room-types-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            letter-spacing: 4px;
        }

        .room-types-header .subtitle {
            color: var(--gold);
            font-size: 16px;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .room-types-header .description {
            max-width: 800px;
            margin: 0 auto;
            color: #666;
            font-size: 16px;
            line-height: 1.8;
        }

        /* KÉK HÁTTÉR A KÁRTYÁK KÖRÜL - FIX HÁTTÉRKÉPPEL */
        .room-types-container {
            position: relative;
            padding: 80px 20px;
            background: var(--dark-blue); /* Alapértelmezett kék háttér */
        }

        .room-types-container.has-bg-image {
            background: transparent; /* Ha van háttérkép, akkor átlátszó */
            position: relative;
            overflow: hidden; /* Hogy ne lógjon ki a kép */
        }

        .room-types-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            z-index: 5;
        }

        .room-types-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            z-index: 5;
        }

        /* FIX HÁTTÉRKÉP - ugyanúgy, mint a header.php-ban */
        .room-types-container.has-bg-image {
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Konténer a kártyáknak - hogy a kép alatt legyenek */
        .room-types-container .container {
            position: relative;
            z-index: 10;
        }

        /* Sötét overlay a jobb olvashatóságért */
        .room-types-container.has-bg-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Sötét átfedés */
            z-index: 2;
        }

        /* Az arany csíkoknak magasabb z-index kell */
        .room-types-container.has-bg-image::after {
            z-index: 6;
        }

        /* SZOBATÍPUS KÁRTYÁK - KÉP + SZÖVEG MELLETT */
        .room-type-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 0; /* Elvesszük a margót, mert a konténer adja */
            background: var(--white);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            border: 1px solid var(--gold);
            position: relative;
            z-index: 20;
        }

        .room-type-card:hover {
            box-shadow: 0 30px 60px rgba(197, 160, 89, 0.3);
            transform: scale(1.02);
            border-color: var(--gold);
        }

        .room-type-card:nth-child(even) {
            direction: rtl;
        }

        .room-type-card:nth-child(even) .room-type-content {
            direction: ltr;
        }

        .room-type-image {
            height: 500px;
            overflow: hidden;
        }

        .room-type-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .room-type-card:hover .room-type-image img {
            transform: scale(1.05);
        }

        .room-type-content {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .room-type-category {
            font-size: 14px;
            color: var(--gold);
            letter-spacing: 8px;
            text-transform: uppercase;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .room-type-name {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            line-height: 1.2;
            letter-spacing: 2px;
        }

        .room-type-description {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 25px;
        }

        .room-type-features {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            background: var(--cream);
            padding: 8px 16px;
            border-left: 2px solid var(--gold);
        }

        .feature-item i {
            font-style: normal;
            font-size: 18px;
            color: var(--gold);
        }

        /* BŐVEBBEN GOMB - KÉK ALAPBÓL, ARANY HOVERRE */
        .btn-bovebben {
            display: inline-block;
            background: var(--dark-blue);
            color: var(--white);
            padding: 16px 48px;
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-size: 14px;
            transition: 0.3s;
            border: 2px solid var(--dark-blue);
            cursor: pointer;
            width: fit-content;
        }

        .btn-bovebben:hover {
            background: var(--gold);
            color: var(--dark-blue);
            border: 2px solid var(--gold);
        }

        /* MODÁLIS ABLAK - KÉP ALAPJÁN */
        .modal-features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .modal-feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(197, 160, 89, 0.2);
            font-size: 14px;
        }

        .modal-feature-item .material-symbols-outlined {
            color: var(--gold);
            font-size: 18px;
        }

        .modal-feature-item span:last-child {
            color: #555;
        }

        .modal-room-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
            background: var(--cream);
            padding: 30px;
        }

        .modal-detail-box h4 {
            color: var(--gold);
            font-size: 16px;
            letter-spacing: 2px;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-left: 3px solid var(--gold);
            padding-left: 10px;
        }

        .modal-detail-box p {
            font-size: 14px;
            line-height: 1.8;
            color: #555;
            text-align: justify;
        }

        /* Reszponzív */
        @media (max-width: 992px) {
            .room-type-card {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .room-type-card:nth-child(even) {
                direction: ltr;
            }
            
            .room-type-image {
                height: 400px;
            }
            
            .room-type-content {
                padding: 40px 30px;
            }
            
            .room-type-name {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .room-types-header h1 {
                font-size: 36px;
            }
            
            .room-type-image {
                height: 300px;
            }
            
            .room-type-content {
                padding: 30px 20px;
            }
            
            .room-type-name {
                font-size: 30px;
            }
            
            .btn-bovebben {
                padding: 14px 32px;
                font-size: 12px;
                letter-spacing: 3px;
            }
            
            .modal-room-details {
                grid-template-columns: 1fr;
            }
            
            .modal-features-grid {
                grid-template-columns: 1fr;
            }
            
            .room-types-container {
                padding: 60px 15px;
            }
            
            /* Mobilon ne legyen fixed, mert furcsán viselkedhet */
            .room-types-container.has-bg-image {
                background-attachment: scroll;
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

    <!-- HEADER RÉSZ -->
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

    <!-- SZOBATÍPUSOK LISTÁJA - KÉP + SZÖVEG MELLETT, FIX HÁTTÉRREL -->
    <?php
    // Szobatípusok lekérése a jellemzőkkel és képekkel együtt
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
        $cardNumber = $i + 1; // 1-től kezdődő sorszám
        
        // Fő kép meghatározása
        $mainImage = $roomType['main_image'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
        
        // Jellemzők lekérése (max 4)
        $featuresStmt = $pdo->prepare("
            SELECT feature_name FROM room_type_features 
            WHERE room_type_id = ? 
            ORDER BY id 
            LIMIT 4
        ");
        $featuresStmt->execute([$roomType['id']]);
        $features = $featuresStmt->fetchAll();
        
        // Konkrét szobák lekérése ehhez a típushoz
        $roomsStmt = $pdo->prepare("
            SELECT * FROM rooms 
            WHERE room_type_id = ? 
            ORDER BY room_number
        ");
        $roomsStmt->execute([$roomType['id']]);
        $rooms = $roomsStmt->fetchAll();
        
        // Típus név formázása
        $typeDisplay = strtoupper(str_replace(' szoba', '', $roomType['type_name']));
        
        // Meghatározzuk, hogy ennél a kártyánál legyen-e háttérkép (1., 3., 5.)
        $hasBackgroundImage = ($cardNumber == 1 || $cardNumber == 3 || $cardNumber == 5);
    ?>
    
    <!-- Külön konténer minden szobatípushoz, FIX háttérképpel -->
    <div class="room-types-container <?php echo $hasBackgroundImage ? 'has-bg-image' : ''; ?>" 
         style="<?php 
            if ($hasBackgroundImage) {
                echo "background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('$mainImage'); background-attachment: fixed;";
            } else {
                echo 'background: #F8F5F0;';
            }
         ?>">
        
        <div class="container">
            <!-- SZOBATÍPUS KÁRTYA -->
            <div class="room-type-card">
                <!-- Kép oldal -->
                <div class="room-type-image">
                    <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($roomType['type_name']); ?>">
                </div>
                
                <!-- Tartalom oldal -->
                <div class="room-type-content">
                    <div class="room-type-category"><?php echo $typeDisplay; ?></div>
                    <h2 class="room-type-name"><?php echo htmlspecialchars($roomType['type_name']); ?></h2>
                    
                    <p class="room-type-description">
                        <?php echo htmlspecialchars($roomType['description']); ?>
                    </p>
                    

                    
                    <!-- Bővebben gomb - megnyitja az első szoba modálját -->
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

    <!-- SZOBARÉSZLETEK MODÁLISOK - KÉP ALAPJÁN -->
    <?php
    // Az összes szoba adatának lekérése a modális ablakokhoz
    $allRoomsStmt = $pdo->query("
        SELECT r.id, r.room_number, r.price, r.status, r.room_type_id,
               rt.type_name, rt.max_guests, rt.size_sqm, 
               rt.description as room_type_description,
               rt.detailed_description as room_type_detailed_description
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
    ");

    while($room = $allRoomsStmt->fetch()):
        // Jellemzők lekérése a típushoz
        $roomFeaturesStmt = $pdo->prepare("
            SELECT feature_name FROM room_type_features 
            WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
            ORDER BY id
        ");
        $roomFeaturesStmt->execute([$room['id']]);
        $roomFeatures = $roomFeaturesStmt->fetchAll();
        
        // Képek lekérése a típushoz
        $roomImagesStmt = $pdo->prepare("
            SELECT * FROM room_type_images 
            WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE id = ?)
            ORDER BY is_main DESC, sort_order
        ");
        $roomImagesStmt->execute([$room['id']]);
        $allImages = $roomImagesStmt->fetchAll();
        
        // Fő kép és galéria képek szétválasztása
        $mainImage = null;
        $galleryImages = [];
        
        foreach($allImages as $img) {
            if($img['is_main'] == 1 && !$mainImage) {
                $mainImage = $img;
            } else {
                $galleryImages[] = $img;
            }
        }
        
        // Ha nincs főkép, az első kép legyen a főkép
        if(!$mainImage && !empty($allImages)) {
            $mainImage = $allImages[0];
            // És a többit hagyjuk galériának
            for($i = 1; $i < count($allImages); $i++) {
                $galleryImages[] = $allImages[$i];
            }
        }
        
        $mainImageUrl = $mainImage['image_url'] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304';
    ?>
    <div id="roomModal<?php echo $room['id']; ?>" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-room-number"><?php echo $room['room_number']; ?>. számú szoba</span>
                <h2><?php echo htmlspecialchars($room['type_name']); ?></h2>
                <span class="close" onclick="closeRoomModal(<?php echo $room['id']; ?>)">&times;</span>
            </div>
            
            <div class="modal-body">
                <!-- Fő kép -->
                <div class="modal-main-image">
                    <img src="<?php echo $mainImageUrl; ?>" 
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                         id="mainImage<?php echo $room['id']; ?>">
                </div>
                
                <!-- További képek (főkép nélkül) -->
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
                
                <!-- Szoba részletes adatai - két oszlopban a kép alatt -->
                <div class="modal-room-details">
                    <!-- BAL OSZLOP - Leírás a room_types táblából -->
                    <div class="modal-detail-box">
                        <h4>RÉSZLETES LEÍRÁS</h4>
                        <p>
                            <?php echo nl2br(htmlspecialchars($room['room_type_detailed_description'] ?? 'A szálloda csodálatos parkjára tekintő tágas, privát erkélyen egy pohár nemes bort kortyolva kettesben gyönyörködhetünk a természetben. A különleges hangulat tökéletes relaxációt biztosít, melyet a modern és letisztult felszereltség is elősegít.')); ?>
                        </p>
                    </div>
                    
                    <!-- JOBB OSZLOP - Felszereltség ikonokkal -->
                    <div class="modal-detail-box">
                        <h4>FELSZERELTSÉG</h4>
                        <?php if (!empty($roomFeatures)): ?>
                            <div class="modal-features-grid">
                                <?php 
                                // ÖSSZES jellemző megjelenítése
                                foreach($roomFeatures as $feature):
                                    $featureName = $feature['feature_name'];
                                    $icon = 'check'; // Alapértelmezett ikon
                                    
                                    // Ikonok hozzárendelése a jellemzők alapján
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
                
                <!-- Ár és foglalás -->
                <div class="modal-price-section">
                    <div class="modal-price">
                        <span class="price-label">Ár / éj:</span>
                        <span class="price-value"><?php echo number_format($room['price'], 0, ',', ' '); ?> Ft</span>
                    </div>
                    <?php if($room['status'] == 'available'): ?>
                        <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn-premium">FOGLALÁS</a>
                    <?php else: ?>
                        <button class="btn-premium" style="background: #ccc; border-color: #ccc; cursor: not-allowed;" disabled>FOGLALT</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <!-- JavaScript a modális ablakokhoz -->
    <script>
    // Modális ablak megnyitása
    function openRoomModal(roomId) {
        document.getElementById('roomModal' + roomId).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    // Modális ablak bezárása
    function closeRoomModal(roomId) {
        document.getElementById('roomModal' + roomId).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Fő kép cseréje
    function changeMainImage(roomId, imageSrc) {
        document.getElementById('mainImage' + roomId).src = imageSrc;
        
        var thumbnails = document.querySelectorAll('#roomModal' + roomId + ' .modal-gallery img');
        thumbnails.forEach(function(img) {
            img.classList.remove('active');
        });
        
        event.target.classList.add('active');
    }

    // Kattintás a modálison kívülre
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            var modals = document.getElementsByClassName('modal');
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        }
    }

    // ESC billentyű
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            var modals = document.getElementsByClassName('modal');
            for (var i = 0; i < modals.length; i++) {
                if (modals[i].style.display === 'block') {
                    modals[i].style.display = 'none';
                    document.body.style.overflow = 'auto';
                    break;
                }
            }
        }
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>