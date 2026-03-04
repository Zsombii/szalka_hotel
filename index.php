<?php
require_once 'config.php';
include 'header.php';
?>

<!-- KIEMELT SZOBÁK -->
<section class="rooms-section">
    <div class="container">
        <h2>KIEMELT SZOBÁINK</h2>
        <div class="section-subtitle">EXKLUZÍV KOMFORT MÁTÉSZALKÁN</div>
        
        <div class="room-grid">
                        <?php
            // 3 szabad szoba lekérése (room_type_id-val együtt)
            $stmt = $pdo->query("SELECT r.*, rt.type_name as room_type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id WHERE r.status = 'available' ORDER BY RAND() LIMIT 3");
            
            if($stmt->rowCount() > 0):
                while($room = $stmt->fetch()):
                    // Fő kép lekérése a szobatípusból
                    $imageQuery = $pdo->prepare("
                        SELECT image_url FROM room_type_images 
                        WHERE room_type_id = ? AND is_main = 1 
                        LIMIT 1
                    ");
                    $imageQuery->execute([$room['room_type_id']]);
                    $mainImage = $imageQuery->fetch();
                    
                    // Ha nincs főkép, akkor bármelyik kép a típushoz
                    if (!$mainImage) {
                        $imageQuery = $pdo->prepare("
                            SELECT image_url FROM room_type_images 
                            WHERE room_type_id = ? 
                            LIMIT 1
                        ");
                        $imageQuery->execute([$room['room_type_id']]);
                        $mainImage = $imageQuery->fetch();
                    }
                    
                    $image = $mainImage ? $mainImage['image_url'] : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
                    
                    // További képek lekérése a szobatípusból (a főkép kivételével)
                    $galleryQuery = $pdo->prepare("
                        SELECT image_url FROM room_type_images 
                        WHERE room_type_id = ? AND is_main = 0 
                        ORDER BY sort_order 
                        LIMIT 4
                    ");
                    $galleryQuery->execute([$room['room_type_id']]);
                    $galleryImages = $galleryQuery->fetchAll();
                    
                    // Szoba jellemzők lekérése a szobatípusból
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

<!-- WELLNESS AJÁNLAT -->
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

<!-- SZALKALAND GYEREKVILÁG -->
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

<!-- GASZTRONÓMIA BEJELENTKEZŐ -->
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

<style>
/* Gasztronómia előnézet stílusok */
.gastro-preview-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #fff 0%, #fff8f0 100%);
}

.gastro-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.gastro-preview-content {
    padding-left: 30px;
}

.gastro-preview-content h2 {
    font-size: 42px;
    color: var(--dark-blue);
    margin: 20px 0;
    font-family: 'Playfair Display', serif;
}

.gastro-preview-text {
    font-size: 16px;
    line-height: 1.8;
    color: #666;
    margin: 25px 0;
}

.gastro-highlights {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 30px 0;
}

.gastro-highlight-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.gastro-highlight-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(197, 160, 89, 0.1);
}

.gastro-highlight-item .material-symbols-outlined {
    color: var(--gold);
    font-size: 24px;
}

.gastro-highlight-item span:last-child {
    font-size: 14px;
    font-weight: 500;
    color: var(--dark-blue);
}

.gastro-preview-images {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    position: relative;
}

.gastro-preview-image {
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.gastro-preview-image:hover {
    transform: scale(1.02);
}

.gastro-preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.gastro-preview-image.main {
    grid-column: span 2;
    height: 300px;
}

.gastro-preview-image.small {
    height: 150px;
}

.gastro-preview-image.small:last-child {
    grid-column: span 2;
    height: 180px;
}

@media (max-width: 992px) {
    .gastro-preview-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .gastro-preview-content {
        padding-left: 0;
        order: 2;
    }
    
    .gastro-preview-images {
        order: 1;
    }
    
    .gastro-highlights {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .gastro-preview-content h2 {
        font-size: 32px;
    }
    
    .gastro-highlights {
        grid-template-columns: 1fr;
    }
    
    .gastro-preview-image.main {
        height: 250px;
    }
    
    .gastro-preview-image.small {
        height: 120px;
    }
}
</style>

<!-- JavaScript a modális ablakokhoz -->
<script>

// Fő kép cseréje a galériában
function changeMainImage(roomId, imageSrc) {
    document.getElementById('mainImage' + roomId).src = imageSrc;
    
    // Aktív állapot eltávolítása minden képről
    var thumbnails = document.querySelectorAll('#roomModal' + roomId + ' .modal-gallery img');
    thumbnails.forEach(function(img) {
        img.classList.remove('active');
    });
    
    // Aktív állapot beállítása a kattintott képre
    event.target.classList.add('active');
}
</script>

<?php include 'footer.php'; ?>