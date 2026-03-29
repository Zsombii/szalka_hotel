<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szalkaland Gyerekvilág - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/kids.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), url('img/jatszohaz1.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="container">
                <div class="header-top-row">
                </div>
                
                <div class="hotel-name">
                    <h1 style="color: var(--white);">HOTEL SZALKA</h1>
                    <div class="stars" style="color: var(--gold);">★★★★</div>
                    <span class="location" id="city-gold" style="color: var(--gold);">M Á T É S Z A L K A</span>
                </div>
                
                <div class="nav-wrapper" style="background: transparent;">
                    <nav class="main-nav">
                        <a href="index.php">HOTEL</a>
                        <a href="rooms.php">SZOBATÍPUSOK</a>
                        <a href="wellness.php">WELLNESS</a>
                        <a href="kids.php" class="active">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <section class="kids-intro">
        <div class="container">
            <h2>GYERMEKBIRODALOM</h2>
            <div class="subtitle">A LEGKISEBBEKNEK IS KIRÁLYI ELLÁTÁS</div>
            <p>
                A Szalkaland egy 200 m²-es fedett játszóház, ahol a gyerekek biztonságos környezetben játszhatnak, 
                miközben a szülők a wellness részlegen pihenhetnek, vagy akár együtt élvezhetik a családi programokat. 
                Animátoraink egész nap változatos foglalkozásokkal várják a kicsiket és nagyokat egyaránt.
            </p>
        </div>
    </section>

    <div class="playhouse-grid">
        <div class="playhouse-item">
            <img src="img/jatszohaz1.jpg" alt="Csúszdapark és labdamedence" loading="lazy">
            <div class="playhouse-caption">Játszósarok</div>
        </div>
        
        <div class="playhouse-item">
            <img src="img/jatszohaz2.jpg" alt="Kreatív foglalkoztató sarok" loading="lazy">
            <div class="playhouse-caption">Kreatív sarok</div>
        </div>
        
        <div class="playhouse-item">
            <img src="img/jatszohaz3.jpg" alt="Mászóka és akadálypálya" loading="lazy">
            <div class="playhouse-caption">Csocsó</div>
        </div>
    </div>

    <section class="kids-features">
        <div class="container">
            <h2>AMIT A SZALKALAND KÍNÁL</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <span class="material-symbols-outlined">child_care</span>
                    <h3>Animátorok</h3>
                    <p>Képzett animátoraink egész nap felügyelik és szórakoztatják a gyerekeket</p>
                </div>
                <div class="feature-card">
                    <span class="material-symbols-outlined">brush</span>
                    <h3>Kreatív foglalkozások</h3>
                    <p>Rajzolás, gyurmázás, kézműveskedés minden délelőtt és délután</p>
                </div>
                <div class="feature-card">
                    <span class="material-symbols-outlined">toys</span>
                    <h3>Játszóház</h3>
                    <p>Csúszdák, mászókák, labdamedence és babasarok a legkisebbeknek</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>