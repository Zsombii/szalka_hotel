<?php
// wellness.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness & Spa - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        /* ========== WELLNESS OLDAL SPECIÁLIS STÍLUSAI ========== */
        .wellness-hero {
            background: var(--dark-blue);
            min-height: 30vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            border-bottom: 3px solid var(--gold);
        }

        .wellness-hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--gold);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .wellness-hero-content p {
            font-size: 18px;
            letter-spacing: 4px;
            text-transform: uppercase;
            max-width: 800px;
            margin: 0 auto;
            color: var(--white);
        }

        /* ========== WELLNESS SZEKCIÓK ========== */
        .wellness-section {
            padding: 100px 0;
            position: relative;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Háttérképek az egyes szekciókhoz */
        .wellness-section.medence-section {
            background: #F8F5F0;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .wellness-section.szauna-section {
            background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                        url('img/szauna1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .wellness-section.masszazs-section {
            background: #F8F5F0;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .wellness-section.fitness-section {
            background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                        url('img/edzohatter.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .wellness-section:first-of-type {
            padding-top: 80px;
        }

        .wellness-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 60px;
            align-items: center;
        }

        .wellness-grid.reverse {
            direction: rtl;
        }

        .wellness-grid.reverse .wellness-content {
            direction: ltr;
        }

        .wellness-content {
            padding: 50px 40px;
            background: rgba(10, 30, 60, 0.9);
            backdrop-filter: blur(5px);
            border: 2px solid var(--gold);
            text-align: center;
        }

        .wellness-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--gold);
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
            text-align: center;
        }

        .wellness-content h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--gold);
        }

        .wellness-content .subtitle {
            color: var(--gold);
            font-size: 14px;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .wellness-content p {
            font-size: 16px;
            line-height: 1.8;
            color: var(--white);
            margin-bottom: 30px;
            text-align: center;
        }

        /* JELLEMZŐK KÖZÉPRE IGAZÍTVA, EGYMÁS ALATT */
        .wellness-features {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            width: 100%;
        }

        .wellness-feature-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            border-left: 3px solid var(--gold);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(5px);
            width: fit-content;
            min-width: 280px;
            transition: 0.3s;
        }

        .wellness-feature-item:hover {
            transform: translateX(10px);
            background: rgba(197, 160, 89, 0.2);
        }

        .wellness-feature-item .material-symbols-outlined {
            color: var(--gold);
            font-size: 28px;
        }

        .wellness-feature-item span:last-child {
            font-weight: 600;
            color: var(--white);
            font-size: 16px;
        }

        /* ========== SLIDESHOW - MÉRET NÖVELVE ========== */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            height: 600px;
            overflow: hidden;
            border-radius: 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 3px solid var(--gold);
        }

        .slide {
            display: none;
            width: 100%;
            height: 100%;
        }

        .slide.active {
            display: block;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .slide:hover img {
            transform: scale(1.05);
        }

        .slideshow-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .slideshow-dot {
            width: 12px;
            height: 12px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
        }

        .slideshow-dot.active {
            background: var(--gold);
            transform: scale(1.2);
        }

        .slideshow-prev, .slideshow-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(10, 30, 60, 0.7);
            color: white;
            padding: 16px;
            cursor: pointer;
            font-size: 24px;
            z-index: 10;
            transition: 0.3s;
            border: none;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
        }

        .slideshow-prev:hover, .slideshow-next:hover {
            background: var(--gold);
        }

        .slideshow-prev {
            left: 10px;
        }

        .slideshow-next {
            right: 10px;
        }

        /* ========== WELLNESS TÉRKÉP ========== */
        .wellness-map {
            background: var(--dark-blue);
            color: var(--white);
            padding: 80px 0;
            text-align: center;
        }

        .wellness-map h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--gold);
            margin-bottom: 30px;
        }

        .map-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .map-item {
            padding: 30px;
            background: rgba(255,255,255,0.05);
            border-bottom: 3px solid var(--gold);
            transition: 0.3s;
        }

        .map-item:hover {
            transform: translateY(-10px);
            background: rgba(197, 160, 89, 0.1);
        }

        .map-item .material-symbols-outlined {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .map-item h4 {
            font-size: 20px;
            margin-bottom: 15px;
            letter-spacing: 2px;
            color: var(--gold);
        }

        .map-item p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
        }

        /* ========== NYITVATARTÁS ========== */
        .opening-hours {
            background: linear-gradient(rgba(10,30,60,0.6), rgba(10,30,60,0.6)), 
                        url('wellness.png');
            background-size: cover;
            background-attachment: fixed;
            color: var(--white);
            padding: 80px 0;
            text-align: center;
        }

        .opening-hours h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--gold);
            margin-bottom: 40px;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .hours-item {
            background: rgba(10, 30, 60, 0.7);
            padding: 20px;
            border: 3px solid rgba(197, 160, 89, 0.3);
            transition: 0.3s;
        }

        .hours-item:hover {
            border-color: var(--gold);
            background: rgba(197, 160, 89, 0.1);
        }

        .hours-item h4 {
            font-size: 20px;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .hours-item p {
            font-size: 18px;
            line-height: 1.8;
            color: var(--white);
            font-weight: 600;
        }

        .hours-note {
            margin-top: 40px;
            font-size: 14px;
            color: rgba(255,255,255,0.6);
        }

        @media (max-width: 1200px) {
            .slideshow-container {
                height: 500px;
            }
        }

        @media (max-width: 992px) {
            .wellness-hero-content h1 {
                font-size: 56px;
            }
            
            .wellness-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .wellness-grid.reverse {
                direction: ltr;
            }
            
            .slideshow-container {
                height: 450px;
            }
            
            .hours-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }
            
            .wellness-content h3 {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .wellness-section {
                padding: 60px 0;
            }
            
            .wellness-hero-content h1 {
                font-size: 42px;
            }
            
            .wellness-content h3 {
                font-size: 30px;
            }
            
            .slideshow-container {
                height: 350px;
            }
            
            .slideshow-prev, .slideshow-next {
                padding: 12px;
                font-size: 18px;
                width: 40px;
                height: 40px;
            }
            
            .wellness-content {
                padding: 30px 20px;
            }
            
            .wellness-feature-item {
                min-width: 240px;
                padding: 12px 20px;
            }
            
            .wellness-feature-item .material-symbols-outlined {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- MAIN HEADER - tiszta fehér háttér -->
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                url('wellness.png');;">
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
                        <a href="wellness.php" class="active">WELLNESS</a>
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <!-- WELLNESS HERO - CSAK SZÖVEG, NINCS KÉP -->
    <div class="wellness-hero">
        <div class="wellness-hero-content">
            <h1>WELLNESS & SPA</h1>
            <p>800 m² • Medencék • Szaunák • Jakuzzik • Masszázs</p>
        </div>
    </div>

    <!-- MEDENCÉK ÉS PEZSGŐFÜRDŐK -->
    <section class="wellness-section medence-section">
        <div class="container">
            <div class="wellness-grid">
                <div class="slideshow-container" id="slideshow1">
                    <div class="slide active">
                        <img src="img/medence1.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence3.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence4.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence5.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence6.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence7.jpg" alt="Medence">
                    </div>
                    <div class="slide">
                        <img src="img/medence8.jpg" alt="Medence">
                    </div>
                    
                    <div class="slideshow-prev" onclick="changeSlide(-1, 'slideshow1')">❮</div>
                    <div class="slideshow-next" onclick="changeSlide(1, 'slideshow1')">❯</div>
                    
                    <div class="slideshow-nav" id="nav-slideshow1">
                        <span class="slideshow-dot active" onclick="currentSlide(1, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(2, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(3, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(4, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(5, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(6, 'slideshow1')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(7, 'slideshow1')"></span>
                    </div>
                </div>
                
                <div class="wellness-content">
                    <div class="subtitle">VÍZI VILÁG</div>
                    <h3>MEDENCÉK & PEZSGŐFÜRDŐK</h3>
                    <p>Fedezze fel 350 m²-es vízi birodalmunkat! Beltéri sós vizű medencénk egész évben 28-30°C-os vízhőmérséklettel várja a pihenni vágyókat. A pezsgőfürdőkben 32-34°C-os vízben kényeztetheti magát, miközben a vízmasszázs gyógyító hatását élvezi.</p>
                    
                    <div class="wellness-features">
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">pool</span>
                            <span>Sós vizű medence (28-30°C)<br>Gyermekmedence (28-32°C)</span>
                        </div>
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">hot_tub</span>
                            <span>Jakuzzi (32-34°C)<br>Pezsgőfürdő (36-38°C)</span>
                        </div>
                    </div>
                    
                    <p><strong>Medencéink nyitvatartása:</strong> 08:00 - 21:00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SZAUNÁK ÉS GŐZKABIN -->
    <section class="wellness-section szauna-section">
        <div class="container">
            <div class="wellness-grid reverse">
                <div class="slideshow-container" id="slideshow2">
                    <div class="slide active">
                        <img src="img/szauna1.jpg" alt="Szauna">
                    </div>
                    <div class="slide">
                        <img src="img/szauna2.jpg" alt="Szauna">
                    </div>
                    <div class="slide">
                        <img src="img/szauna3.jpg" alt="Szauna">
                    </div>
                    <div class="slide">
                        <img src="img/szauna4.jpg" alt="Szauna">
                    </div>
                    <div class="slide">
                        <img src="img/szauna5.jpg" alt="Szauna">
                    </div>
                    
                    <div class="slideshow-next" onclick="changeSlide(-1, 'slideshow2')">❮</div>
                    <div class="slideshow-prev" onclick="changeSlide(1, 'slideshow2')">❯</div>
                    
                    <div class="slideshow-nav" id="nav-slideshow2">
                        <span class="slideshow-dot active" onclick="currentSlide(1, 'slideshow2')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(2, 'slideshow2')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(3, 'slideshow2')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(4, 'slideshow2')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(5, 'slideshow2')"></span>
                    </div>
                </div>
                
                <div class="wellness-content">
                    <div class="subtitle">HŐ ÉS GŐZ</div>
                    <h3>SZAUNÁK & GŐZKABINOK</h3>
                    <p>Hagyja magát hátradőlni és engedje, hogy a finn szauna forró levegője (80-90°C) kitisztítsa szervezetét. A gyógynövényes gőzkabin (45-50°C) légzőszerveire gyakorol jótékony hatást, míg a biószauna (60°C) enyhébb hőmérsékletével azoknak ajánlott, akik kevésbé kedvelik a magas hőfokot.</p>
                    
                    <div class="wellness-features">
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">sauna</span>
                            <span>Finn szauna (80-90°C)<br>Infraszauna (50-55°C)</span>
                        </div>
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">spa</span>
                            <span>Gőzkabin (45-50°C)</span>
                        </div>
                    </div>
                    
                    <p><strong>Szauna részleg nyitvatartása:</strong> 10:00 - 22:00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- MASSZÁZS ÉS KEZELÉSEK -->
    <section class="wellness-section masszazs-section">
        <div class="container">
            <div class="wellness-grid">
                <div class="slideshow-container" id="slideshow3">
                    <div class="slide active">
                        <img src="img/masszazs1.jpg" alt="Masszázs">
                    </div>
                    <div class="slide">
                        <img src="img/masszazs2.jpg" alt="Masszázs">
                    </div>
                    <div class="slide">
                        <img src="img/masszazs3.jpg" alt="Masszázs">
                    </div>
                    
                    <div class="slideshow-prev" onclick="changeSlide(-1, 'slideshow3')">❮</div>
                    <div class="slideshow-next" onclick="changeSlide(1, 'slideshow3')">❯</div>
                    
                    <div class="slideshow-nav" id="nav-slideshow3">
                        <span class="slideshow-dot active" onclick="currentSlide(1, 'slideshow3')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(2, 'slideshow3')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(3, 'slideshow3')"></span>
                    </div>
                </div>
                
                <div class="wellness-content">
                    <div class="subtitle">KÉNYEZTETÉS</div>
                    <h3>MASSZÁZSOK & KEZELÉSEK</h3>
                    <p>Szakértő masszőreink segítenek elfelejteni a mindennapok stresszét. Válasszon a klasszikus svédmasszázs, a frissítő aromaterápiás kezelés vagy a mélyre hatoló gyógymasszázs között. Minden kezelés előtt személyre szabott tanácsadáson vesz részt, hogy az Ön igényeinek legmegfelelőbb kezelést kaphassa.</p>
                    
                    <div class="wellness-features">
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">self_improvement</span>
                            <span>Svédmasszázs (60/90 perc)</span>
                        </div>
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">healing</span>
                            <span>Gyógymasszázs (60 perc)</span>
                        </div>
                    </div>
                    
                    <p><strong>Masszázs szalon:</strong> Előzetes bejelentkezés alapján</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FITNESS ÉS PIENŐ -->
    <section class="wellness-section fitness-section">
        <div class="container">
            <div class="wellness-grid reverse">
                <div class="slideshow-container" id="slideshow4">
                    <div class="slide active">
                        <img src="img/fitness1.png" alt="Fitness terem">
                    </div>
                    <div class="slide">
                        <img src="img/fitness2.png" alt="Fitness terem">
                    </div>
                    <div class="slide">
                        <img src="img/fitness3.png" alt="Fitness terem">
                    </div>
                    
                    <div class="slideshow-next" onclick="changeSlide(-1, 'slideshow4')">❮</div>
                    <div class="slideshow-prev" onclick="changeSlide(1, 'slideshow4')">❯</div>
                    
                    <div class="slideshow-nav" id="nav-slideshow4">
                        <span class="slideshow-dot active" onclick="currentSlide(1, 'slideshow4')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(2, 'slideshow4')"></span>
                        <span class="slideshow-dot" onclick="currentSlide(3, 'slideshow4')"></span>
                    </div>
                </div>
                
                <div class="wellness-content">
                    <div class="subtitle">AKTÍV PIHENÉS</div>
                    <h3>FITNESS & PIHENŐ</h3>
                    <p>Akár aktívan szeretné kipihenni a nap fáradalmait, akár csendes elvonulásra vágyik, nálunk mindkettőre lehetősége nyílik. Modern kardio- és súlyzógépekkel felszerelt fitnesztermünkben edzhet egy jót, majd a csendes pihenőszobában relaxálhat egy csésze teával.</p>
                    
                    <div class="wellness-features">
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">fitness_center</span>
                            <span>Súlyzók<br>Kardio gépek</span>
                        </div>
                        <div class="wellness-feature-item">
                            <span class="material-symbols-outlined">weekend</span>
                            <span>Pihenő szoba<br>Tea sarok</span>
                        </div>
                    </div>
                    
                    <p><strong>Fitness terem nyitvatartása:</strong> 06:00 - 22:00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- WELLNESS TÉRKÉP -->
    <section class="wellness-map">
        <div class="container">
            <h2>WELLNESS TÉRKÉP</h2>
            <div class="gold-divider"></div>
            <p style="margin-bottom: 40px; color: rgba(255,255,255,0.8);">800 m² relaxáció egy helyen</p>
            
            <div class="map-grid">
                <div class="map-item">
                    <span class="material-symbols-outlined">pool</span>
                    <h4>MEDENCÉK</h4>
                    <p>Sós vizű medence (28-30°C)<br>Pezsgőfürdő (36-38°C)<br>Gyermekmedence (28-32°C)</p>
                </div>
                
                <div class="map-item">
                    <span class="material-symbols-outlined">sauna</span>
                    <h4>SZAUNÁK</h4>
                    <p>Finn szauna (80-90°C)<br>Gőzkabin (45-50°C)<br>Infraszauna (50-55°C)</p>
                </div>
                
                <div class="map-item">
                    <span class="material-symbols-outlined">spa</span>
                    <h4>MASSZÁZS</h4>
                    <p>Svédmasszázs<br>Aromaterápia<br>Gyógymasszázs</p>
                </div>
                
                <div class="map-item">
                    <span class="material-symbols-outlined">fitness_center</span>
                    <h4>FITNESS</h4>
                    <p>Kardio gépek<br>Súlyzók<br>Nyújtó sarok</p>
                </div>
            </div>
        </div>
    </section>

    <!-- NYITVATARTÁS -->
    <section class="opening-hours">
        <div class="container">
            <h2>NYITVATARTÁS</h2>
            <div class="gold-divider"></div>
            
            <div class="hours-grid">
                <div class="hours-item">
                    <h4>HÉTFŐ - CSÜTÖRTÖK</h4>
                    <p>08:00 - 21:00</p>
                </div>
                
                <div class="hours-item">
                    <h4>PÉNTEK - SZOMBAT</h4>
                    <p>08:00 - 22:00</p>
                </div>
                
                <div class="hours-item">
                    <h4>VASÁRNAP</h4>
                    <p>08:00 - 20:00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- JAVASCRIPT A SLIDESHOW-HOZ -->
    <script>
        // Slideshow objektumok tárolása
        let slideIndices = {
            'slideshow1': 1,
            'slideshow2': 1,
            'slideshow3': 1,
            'slideshow4': 1
        };
        
        // Automatikus slideshow időzítők
        let autoSlideTimers = {};
        
        // Slideshow inicializálása
        function initSlideshows() {
            for (let slideshowId in slideIndices) {
                showSlide(slideIndices[slideshowId], slideshowId);
                startAutoSlide(slideshowId);
            }
        }
        
        // Automatikus slideshow indítása
        function startAutoSlide(slideshowId) {
            // Meglévő időzítő törlése
            if (autoSlideTimers[slideshowId]) {
                clearInterval(autoSlideTimers[slideshowId]);
            }
            // Új időzítő beállítása 4 másodpercre
            autoSlideTimers[slideshowId] = setInterval(function() {
                changeSlide(1, slideshowId);
            }, 4000);
        }
        
        // Automatikus slideshow leállítása (átmenetileg)
        function stopAutoSlide(slideshowId) {
            if (autoSlideTimers[slideshowId]) {
                clearInterval(autoSlideTimers[slideshowId]);
            }
        }
        
        // Következő/előző slide
        function changeSlide(n, slideshowId) {
            // Automatikus slideshow átmeneti leállítása
            stopAutoSlide(slideshowId);
            
            slideIndices[slideshowId] += n;
            showSlide(slideIndices[slideshowId], slideshowId);
            
            // Automatikus slideshow újraindítása
            startAutoSlide(slideshowId);
        }
        
        // Adott slide megjelenítése
        function currentSlide(n, slideshowId) {
            // Automatikus slideshow átmeneti leállítása
            stopAutoSlide(slideshowId);
            
            slideIndices[slideshowId] = n;
            showSlide(slideIndices[slideshowId], slideshowId);
            
            // Automatikus slideshow újraindítása
            startAutoSlide(slideshowId);
        }
        
        function showSlide(n, slideshowId) {
            const slideshow = document.getElementById(slideshowId);
            const slides = slideshow.getElementsByClassName('slide');
            const dots = document.getElementById('nav-' + slideshowId).getElementsByClassName('slideshow-dot');
            
            if (n > slides.length) {
                slideIndices[slideshowId] = 1;
            }
            if (n < 1) {
                slideIndices[slideshowId] = slides.length;
            }
            
            // Összes slide elrejtése
            for (let i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
            }
            
            // Összes dot inaktív
            for (let i = 0; i < dots.length; i++) {
                dots[i].classList.remove('active');
            }
            
            // Aktív slide megjelenítése
            slides[slideIndices[slideshowId] - 1].classList.add('active');
            dots[slideIndices[slideshowId] - 1].classList.add('active');
        }
        
        // Inicializálás oldalbetöltéskor
        document.addEventListener('DOMContentLoaded', function() {
            initSlideshows();
        });
    </script>

    <?php include 'footer.php'; ?>