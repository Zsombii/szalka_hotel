<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness & Spa - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/wellness.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                url('img/wellness.png');;">
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
                        <a href="#doc-links">DOKUMENTÁCIÓ</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="wellness-hero">
        <div class="wellness-hero-content">
            <h1>WELLNESS & SPA</h1>
            <p>800 m² • Medencék • Szaunák • Jakuzzik • Masszázs</p>
        </div>
    </div>

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

<script src="js/wellness.js"></script>

    <?php include 'footer.php'; ?>