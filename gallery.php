<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galéria - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gallery.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto;">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), url('img/bar1.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="container">
                <div class="hotel-name">
                    <h1>HOTEL SZALKA</h1>
                    <div class="stars">★★★★</div>
                    <span class="location" id="city-gold">M Á T É S Z A L K A</span>
                </div>
                
                <div class="nav-wrapper">
                    <nav class="main-nav">
                        <a href="index.php">HOTEL</a>
                        <a href="rooms.php">SZOBATÍPUSOK</a>
                        <a href="wellness.php">WELLNESS</a>
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php" class="active">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="gallery-hero">
        <div>
            <h1>GALÉRIA</h1>
            <p style="color: var(--white); letter-spacing: 4px;">KÉPEK HOTELÜNKBŐL</p>
        </div>
    </div>

    <div class="gallery-container">
        <div class="gallery-grid">
            <?php
            $imgDir = 'img/';
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (is_dir($imgDir)) {
                $files = scandir($imgDir);
                
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && !is_dir($imgDir . $file)) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, $allowed)) {
                            echo '<div class="gallery-item">';
                            echo '<img src="' . $imgDir . $file . '" alt="' . htmlspecialchars($file) . '">';
                            echo '</div>';
                        }
                    }
                }
            }
            ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>