<?php
require_once 'config.php';
include 'header.php';
?>

<link rel="stylesheet" href="css/index.css">
<section class="rooms-section">
    <div class="container">
        <h2>EXKLUZÍV SZOBÁINK</h2>
        <div class="section-subtitle">KIEMELT KOMFORT MÁTÉSZALKÁN</div>
        
        <div class="room-grid">
                        <?php
            $stmt = $pdo->query("SELECT r.*, rt.type_name as room_type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id WHERE r.status = 'available' ORDER BY RAND() LIMIT 3");
            
            if($stmt->rowCount() > 0):
                while($room = $stmt->fetch()):
                    $imageQuery = $pdo->prepare("
                        SELECT image_url FROM room_type_images 
                        WHERE room_type_id = ? AND is_main = 1 
                        LIMIT 1
                    ");
                    $imageQuery->execute([$room['room_type_id']]);
                    $mainImage = $imageQuery->fetch();
                    
                    if (!$mainImage) {
                        $imageQuery = $pdo->prepare("
                            SELECT image_url FROM room_type_images 
                            WHERE room_type_id = ? 
                            LIMIT 1
                        ");
                        $imageQuery->execute([$room['room_type_id']]);
                        $mainImage = $imageQuery->fetch();
                    }
                    
                    $image = $mainImage ? $mainImage['image_url'] : '';
                    
                    $galleryQuery = $pdo->prepare("
                        SELECT image_url FROM room_type_images 
                        WHERE room_type_id = ? AND is_main = 0 
                        ORDER BY sort_order 
                        LIMIT 4
                    ");
                    $galleryQuery->execute([$room['room_type_id']]);
                    $galleryImages = $galleryQuery->fetchAll();
                    
                    $featuresQuery = $pdo->prepare("SELECT feature_name FROM room_type_features WHERE room_type_id = ? ORDER BY id");
                    $featuresQuery->execute([$room['room_type_id']]);
                    $features = $featuresQuery->fetchAll();
            ?>
                    <div class="room-card">
                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($room['type']); ?>">
                        <div class="room-card-content">
                            <span class="room-number"><?php echo htmlspecialchars($room['room_number']); ?></span>
                            <h3><?php echo htmlspecialchars($room['type']); ?></h3>
                            <p class="room-description">
                                <?php 
                                echo htmlspecialchars(substr($room['description'], 0, 100)) . '...'; 
                                ?>
                            </p>
                            <div class="price">
                                <?php echo number_format($room['price'], 0, ',', ' '); ?> Ft 
                                <small>/éj</small>
                            </div>
                            <div class="room-buttons">
                                
                                <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn-premium btn-small">FOGLALÁS</a>
                            </div>
                        </div>
                    </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-rooms">
                    <h3>Jelenleg nincs szabad szobánk</h3>
                    <p>Kérjük, nézz vissza később, vagy foglalj későbbi dátumra!</p>
                    <a href="rooms.php" class="btn-premium">ÖSSZES SZOBA MEGTEKINTÉSE</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="rooms-link">
            <a href="rooms.php">ÖSSZES SZOBÁNK MEGTEKINTÉSE</a>
        </div>
    </div>
</section>

<section class="wellness-section">
    <div class="container">
        <h2>WELLNESS & SPA</h2>
        <div class="gold-divider"></div>
        <p class="wellness-text">
            Fedezze fel 800 m²-es wellness birodalmunkat! Finn szauna, gőzkabin, sós vizű medence, 
            jacuzzi és pihenőszoba várja a feltöltődni vágyókat.
        </p>
        <a href="wellness.php" class="btn-premium btn-outline">TUDJON MEG TÖBBET</a>
    </div>
</section>

<section class="kids-section">
    <div class="container">
        <div class="kids-grid">
            <div class="kids-content">
                <h2>SZALKALAND<br>GYEREKVILÁG</h2>
                <div class="gold-divider"></div>
                <p>
                    A legkisebb vendégeinket különleges élményekkel várjuk! 200 m²-es fedett játszóház, 
                    felügyelt gyerekprogramok, animátorok, kreatív foglalkozások és családi szobák. 
                    Nálunk a gyerekek is királyi ellátásban részesülnek!
                </p>
                <a href="kids.php" class="btn-premium btn-dark">FEDEZZE FEL</a>
            </div>
            <div class="kids-images">
                <img src="img/jatszohaz1.jpg" alt="Gyerekvilág">
                <img src="img/jatszohaz2.jpg" alt="Családi szoba" class="image-offset">
            </div>
        </div>
    </div>
</section>

<section class="gastro-preview-section">
    <div class="container">
        <div class="gastro-preview-grid">
            <div class="gastro-preview-images">
                <div class="gastro-preview-image main">
                    <img src="img/etterem1.jpg" alt="Étterem belső">
                </div>
                <div class="gastro-preview-image small">
                    <img src="img/reggeli1.jpg" alt="Reggeli">
                </div>
                <div class="gastro-preview-image small">
                    <img src="img/desszert1.jpg" alt="Desszertek">
                </div>
                <div class="gastro-preview-image small">
                    <img src="img/borlap1.jpg" alt="Borlap">
                </div>
            </div>
            
            <div class="gastro-preview-content">
                <h2>GASZTRONÓMIAI ÉLMÉNYEK</h2>
                <div class="gold-divider"></div>
                <p class="gastro-preview-text">
                    Hotelunk étterme a magyar konyha klasszikusait és a nemzetközi gasztronómia különlegességeit kínálja. 
                    Reggeli svédasztalunk helyi finomságokkal, la carte éttermünk séfajánlataival, 
                    borbárunk a legjobb magyar borokkal várja vendégeinket. Saját cukrászdánkban készülő desszertjeink 
                    pedig garantáltan elkápráztatják az édesszájúakat.
                </p>
                
                <div class="gastro-highlights">
                    <div class="gastro-highlight-item">
                        <span class="material-symbols-outlined">restaurant</span>
                        <span>Svédasztalos reggeli</span>
                    </div>
                    <div class="gastro-highlight-item">
                        <span class="material-symbols-outlined">local_bar</span>
                        <span>La carte étterem</span>
                    </div>
                    <div class="gastro-highlight-item">
                        <span class="material-symbols-outlined">wine_bar</span>
                        <span>Magyar borok</span>
                    </div>
                    <div class="gastro-highlight-item">
                        <span class="material-symbols-outlined">cake</span>
                        <span>Házi desszertek</span>
                    </div>
                </div>
                
                <a href="gastronomy.php" class="btn-premium">GASZTRONÓMIAI KÍNÁLATUNK</a>
            </div>
        </div>
    </div>
</section>

<script src="js/index.js"></script>

<?php include 'footer.php'; ?>