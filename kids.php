<?php
// kids.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szalkaland Gyerekvilág - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        /* ========== SZALKALAND GYEREKVILÁG OLDAL SPECIÁLIS STÍLUSAI ========== */
        .kids-hero {
            background: var(--dark-blue);
            min-height: 30vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            border-bottom: 3px solid var(--gold);
        }

        .kids-hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--gold);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .kids-hero-content p {
            font-size: 18px;
            letter-spacing: 4px;
            text-transform: uppercase;
            max-width: 800px;
            margin: 0 auto;
            color: var(--white);
        }

        /* ========== BEVEZETŐ SZEKCIÓ ========== */
        .kids-intro {
            padding: 80px 0;
            background: var(--cream);
            text-align: center;
        }

        .kids-intro h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .kids-intro .subtitle {
            color: var(--gold);
            font-size: 16px;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .kids-intro p {
            max-width: 900px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 1.8;
            color: #555;
        }

        /* ========== JÁTSZÓHÁZ KÉPEK - 3 NAGY KÉP EGYMÁS MELLETT ========== */
        .playhouse-grid {
            display: flex;
            flex-wrap: nowrap;
            gap: 30px;
            padding: 60px 40px;
            background: var(--white);
            max-width: 1600px;
            margin: 0 auto;
            justify-content: center;
        }

        .playhouse-item {
            position: relative;
            overflow: hidden;
            border: 4px solid var(--gold);
            transition: 0.3s;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            aspect-ratio: 4/3;
            width: 100%;
            max-width: 500px;
        }

        .playhouse-item:hover {
            transform: scale(1.05);
            box-shadow: 0 30px 60px rgba(197, 160, 89, 0.4);
            border-color: var(--gold);
            z-index: 10;
        }

        .playhouse-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
            display: block;
        }

        .playhouse-item:hover img {
            transform: scale(1.1);
        }

        .playhouse-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(10, 30, 60, 0.9);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            transform: translateY(100%);
            transition: transform 0.3s;
            border-top: 3px solid var(--gold);
        }

        .playhouse-item:hover .playhouse-caption {
            transform: translateY(0);
        }

        /* ========== JÁTSZÓHÁZ JELLEMZŐK - 3 KÁRTYA KÖZÉPRE IGAZÍTVA ========== */
        .kids-features {
            padding: 80px 0;
            background: var(--cream);
            text-align: center;
        }

        .kids-features h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--dark-blue);
            margin-bottom: 40px;
        }

        .features-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            justify-content: center;
        }

        .feature-card {
            background: var(--white);
            padding: 40px 30px;
            border: 1px solid var(--gold);
            transition: 0.3s;
            flex: 0 1 300px;
            min-width: 280px;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(197, 160, 89, 0.2);
        }

        .feature-card .material-symbols-outlined {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 20px;
            color: var(--dark-blue);
            margin-bottom: 15px;
        }

        .feature-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        /* ========== PROGRAMOK ========== */
        .kids-programs {
            padding: 80px 0;
            background: linear-gradient(rgba(10,30,60,0.9), rgba(10,30,60,0.9)), 
                        url('img/jatszohaz1.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: var(--white);
            text-align: center;
        }

        .kids-programs h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--gold);
            margin-bottom: 40px;
        }

        .programs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .program-card {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-bottom: 3px solid var(--gold);
            transition: 0.3s;
        }

        .program-card:hover {
            background: rgba(197, 160, 89, 0.2);
            transform: translateY(-5px);
        }

        .program-card h3 {
            font-size: 24px;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .program-card p {
            font-size: 15px;
            line-height: 1.8;
            color: rgba(255,255,255,0.9);
        }

        .program-time {
            display: inline-block;
            margin-top: 15px;
            padding: 5px 15px;
            background: var(--gold);
            color: var(--dark-blue);
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 1px;
        }

        /* ========== NYITVATARTÁS ========== */
        .kids-hours {
            padding: 80px 0;
            background: var(--dark-blue);
            color: var(--white);
            text-align: center;
        }

        .kids-hours h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--gold);
            margin-bottom: 30px;
        }

        .hours-box {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255,255,255,0.05);
            padding: 40px;
            border: 1px solid var(--gold);
        }

        .hours-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(197, 160, 89, 0.3);
            font-size: 18px;
        }

        .hours-row:last-child {
            border-bottom: none;
        }

        .hours-day {
            font-weight: 600;
            color: var(--gold);
        }

        .hours-time {
            color: var(--white);
        }

        .hours-note {
            margin-top: 30px;
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }

        /* Reszponzív */
        @media (max-width: 1400px) {
            .playhouse-grid {
                padding: 60px 30px;
                gap: 25px;
            }
            
            .playhouse-item {
                max-width: 450px;
            }
        }

        @media (max-width: 1200px) {
            .playhouse-grid {
                padding: 60px 20px;
                gap: 20px;
            }
            
            .playhouse-item {
                max-width: 400px;
            }
            
            .playhouse-caption {
                font-size: 18px;
                padding: 15px;
            }
        }

        @media (max-width: 992px) {
            .kids-hero-content h1 {
                font-size: 56px;
            }

            .programs-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .playhouse-grid {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .playhouse-item {
                max-width: 500px;
                width: calc(50% - 15px);
            }
        }

        @media (max-width: 768px) {
            .kids-hero-content h1 {
                font-size: 42px;
            }

            .kids-intro h2 {
                font-size: 36px;
            }

            .kids-intro p {
                font-size: 16px;
                padding: 0 20px;
            }

            .playhouse-grid {
                flex-direction: column;
                align-items: center;
                padding: 40px 20px;
            }
            
            .playhouse-item {
                max-width: 600px;
                width: 100%;
                aspect-ratio: 16/9;
            }

            .programs-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }

            .hours-box {
                margin: 0 20px;
                padding: 30px 20px;
            }

            .hours-row {
                font-size: 16px;
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .feature-card {
                min-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Egységes header -->
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                url('img/jatszohaz1.jpg');">
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

    <!-- Bevezető szekció -->
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

    <!-- Játszóház képek - 3 NAGY kép egymás mellett -->
    <div class="playhouse-grid">
        <!-- 1. kép - Csúszdapark -->
        <div class="playhouse-item">
            <img src="img/jatszohaz1.jpg" alt="Csúszdapark és labdamedence" loading="lazy">
            <div class="playhouse-caption">Játszósarok</div>
        </div>
        
        <!-- 2. kép - Kreatív sarok -->
        <div class="playhouse-item">
            <img src="img/jatszohaz2.jpg" alt="Kreatív foglalkoztató sarok" loading="lazy">
            <div class="playhouse-caption">Kreatív sarok</div>
        </div>
        
        <!-- 3. kép - Mászóka -->
        <div class="playhouse-item">
            <img src="img/jatszohaz3.jpg" alt="Mászóka és akadálypálya" loading="lazy">
            <div class="playhouse-caption">Csocsó</div>
        </div>
    </div>

    <!-- Játszóház jellemzői - 3 kártya középen -->
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