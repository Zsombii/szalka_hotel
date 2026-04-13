<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Szalka - Mátészalka ****</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <section class="hero-unified">
        <header class="main-header">
            <div class="container">
                
                <div class="hotel-name">
                    <h1>HOTEL SZALKA</h1>
                    <div class="stars">★★★★</div>
                    <span class="location" id="city-gold">M Á T É S Z A L K A</span>
                </div>
                
                <div class="nav-wrapper">
                    <nav class="main-nav">
                        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOTEL</a>
                        <a href="rooms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>">SZOBATÍPUSOK</a>
                        <a href="wellness.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'wellness.php' ? 'active' : ''; ?>">WELLNESS</a>
                        <a href="kids.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kids.php' ? 'active' : ''; ?>">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gastronomy.php' ? 'active' : ''; ?>">GASZTRONÓMIA</a>
                        <a href="gallery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">GALÉRIA</a>
                        <a href="#doc-links">DOKUMENTÁCIÓ</a>
                    </nav>
                </div>
            </div>
        </header>

        <div class="hero-content-unified">
            <div class="container">
                <h2>ÜDVÖZÖLJÜK A HOTEL SZALKÁBAN</h2>
                <p>MÁTÉSZALKA LEGELEGÁNSABB SZÁLLODÁJA</p>
                <a href="rooms.php" class="btn-premium">Foglalás</a>
            </div>
        </div>
    </section>