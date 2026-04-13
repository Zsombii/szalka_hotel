<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gasztronómia - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <link rel="stylesheet" href="css/gastronomy.css">
</head>
<body>
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), url('img/etterem1.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
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
                        <a href="kids.php">SZALKALAND GYEREKVILÁG</a>
                        <a href="gastronomy.php" class="active">GASZTRONÓMIA</a>
                        <a href="gallery.php">GALÉRIA</a>
                        <a href="#doc-links">DOKUMENTÁCIÓ</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <div class="gastro-hero">
        <div class="gastro-hero-content">
            <h1>GASZTRONÓMIA</h1>
            <p>ÍZEK ÉS HAGYOMÁNYOK TALÁLKOZÁSA</p>
        </div>
    </div>

    <section class="gastro-intro">
        <div class="container">
            <h2>ÉLMÉNY A TÁNYÉRON</h2>
            <div class="subtitle">KONYHÁNK KINCSEI</div>
            <p>
                Hotel Szalkánk éttermében a magyar konyha klasszikusai és a nemzetközi gasztronómia trendjei találkoznak. 
                Séfünk, Kovács Péter és csapata a legfrissebb, helyi alapanyagokból készíti el az ínycsiklandó fogásokat. 
                Reggelitől a vacsoráig minden étkezés egy felejthetetlen kulináris utazás.
            </p>
        </div>
    </section>

    <div class="gastro-grid">
        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/reggeli1.jpg" alt="Reggeli">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">REGGELI</span>
                <h3>SVÉDASZTALOS REGGELI</h3>
                <p class="gastro-description">
                    Ébredjen a nap első ízeire! Svédasztalos reggelink friss pékáruval, helyi sajtokkal, felvágottakkal, 
                    tojásételekkel, gyümölcsökkel és számos meleg étellel várja. A frissen főzött kávé illata garantáltan felébreszti érzékeit.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">coffee</span>
                    <span>06:30 - 10:30 • Minden nap</span>
                </div>
            </div>
        </div>

        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/etterem1.jpg" alt="Étterem belső">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">EBÉD • VACSORA</span>
                <h3>LA CARTE ÉTTEREM</h3>
                <p class="gastro-description">
                    Elegáns, mégis meghitt környezetben kóstolhatja meg étlapunk válogatott fogásait. 
                    Séfünk ajánlatai mellett klasszikus magyar ételeket és nemzetközi specialitásokat is rendelhet.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">restaurant</span>
                    <span>12:00 - 22:00 • Előzetes asztalfoglalás ajánlott</span>
                </div>
            </div>
        </div>

        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/borlap1.jpg" alt="Borlap">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">BOROK</span>
                <h3>SZÁRAZ, ÉDES, GYÜMÖLCSÖS</h3>
                <p class="gastro-description">
                    Borlapunkon a legjobb magyar borvidékek kincsei sorakoznak. Villányi cabernet, egri bikavér, 
                    tokaji aszú és friss, könnyed rosék – sommelier-nk segít kiválasztani a tökéletes italt fogásaihoz.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">wine_bar</span>
                    <span>Minőségi magyar borok • Sörök • Röviditalok</span>
                </div>
            </div>
        </div>

        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/desszert1.jpg" alt="Desszertek">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">ÉDES KÍSÉRTÉS</span>
                <h3>DESSZERTEK ÉS SÜTEMÉNYEK</h3>
                <p class="gastro-description">
                    Saját cukrászdánkban készülnek a mennyei sütemények, torták és desszertek. 
                    Gundel palacsinta, somlói galuska, forró csokoládészuflé – aki édesszájú, nálunk garantáltan megtalálja a számításait.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">cake</span>
                    <span>Házi készítésű desszertek • 0-24</span>
                </div>
            </div>
        </div>

        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/bar1.jpg" alt="Italbár - Cocktail bar">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">ITALBÁR</span>
                <h3>COCKTAIL BAR</h3>
                <p class="gastro-description">
                    Kóstolja meg mixereink különleges kreációit! Klasszikus koktélok, saját fejlesztésű italok, 
                    prémium whiskey-k, rumok és gin-ek széles választéka várja a bárpultnál.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">local_bar</span>
                    <span>Klasszikus és exkluzív koktélok • 18:00 - 02:00</span>
                </div>
            </div>
        </div>

        <div class="gastro-item">
            <div class="gastro-image">
                <img src="img/kavezo1.jpg" alt="Kávézó">
            </div>
            <div class="gastro-content">
                <span class="gastro-category">KÁVÉZÓ</span>
                <h3>LA COFFEE LOUNGE</h3>
                <p class="gastro-description">
                    A nap bármely szakában betérhet La Coffee Lounge-ba egy csésze kiváló minőségű kávéra, 
                    teára vagy forró csokoládéra. Kávékülönlegességeink mellé apró sütemények és snackek is választhatók.
                </p>
                <div class="gastro-highlight">
                    <span class="material-symbols-outlined">local_cafe</span>
                    <span>08:00 - 22:00 • Kávékülönlegességek</span>
                </div>
            </div>
        </div>
    </div>

    <section class="gastro-special">
        <div class="container">
            <h2>SPECIALITÁSAINK</h2>
            <div class="special-grid">
                <div class="special-item">
                    <span class="material-symbols-outlined">grocery</span>
                    <h3>Helyi alapanyagok</h3>
                    <p>Friss, helyi alapanyagok a környékbeli gazdaságokból</p>
                </div>
                <div class="special-item">
                    <span class="material-symbols-outlined">bakery_dining</span>
                    <h3>Házi kenyér</h3>
                    <p>Ropogós, frissen sült kenyér minden nap</p>
                </div>
                <div class="special-item">
                    <span class="material-symbols-outlined">local_dining</span>
                    <h3>Séf ajánlata</h3>
                    <p>Napi váltakozó menü a legfrissebb alapanyagokból</p>
                </div>
            </div>
        </div>
    </section>

    <section class="gastro-hours">
        <div class="container">
            <h2>NYITVATARTÁS</h2>
            <div class="hours-box">
                <div class="hours-row">
                    <span class="hours-day">Reggeli (H-V)</span>
                    <span class="hours-time">06:30 - 10:30</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">Ebéd (H-V)</span>
                    <span class="hours-time">12:00 - 15:00</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">Vacsora (H-V)</span>
                    <span class="hours-time">18:00 - 22:00</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">Kávézó</span>
                    <span class="hours-time">08:00 - 22:00</span>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>