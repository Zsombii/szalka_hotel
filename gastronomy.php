<?php
// gastronomy.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gasztronómia - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
    <style>
        /* ========== GASZTRONÓMIA OLDAL SPECIÁLIS STÍLUSAI ========== */
        .gastro-hero {
            background: var(--dark-blue);
            min-height: 30vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            border-bottom: 3px solid var(--gold);
        }

        .gastro-hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--gold);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .gastro-hero-content p {
            font-size: 18px;
            letter-spacing: 4px;
            text-transform: uppercase;
            max-width: 800px;
            margin: 0 auto;
            color: var(--white);
        }

        /* ========== BEVEZETŐ SZEKCIÓ ========== */
        .gastro-intro {
            padding: 80px 0;
            background: var(--cream);
            text-align: center;
        }

        .gastro-intro h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--dark-blue);
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .gastro-intro .subtitle {
            color: var(--gold);
            font-size: 16px;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .gastro-intro p {
            max-width: 900px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 1.8;
            color: #555;
        }

        /* ========== GASZTRONÓMIAI KÁRTYÁK ========== */
        .gastro-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            padding: 60px 20px;
            background: var(--white);
        }

        .gastro-item {
            background: var(--cream);
            border: 1px solid var(--gold);
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .gastro-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(197, 160, 89, 0.15);
            border-color: var(--gold);
        }

        .gastro-image {
            height: 500px;
            overflow: hidden;
        }

        .gastro-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .gastro-item:hover .gastro-image img {
            transform: scale(1.05);
        }

        .gastro-content {
            padding: 30px;
        }

        .gastro-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--dark-blue);
            margin-bottom: 10px;
            border-left: 4px solid var(--gold);
            padding-left: 15px;
        }

        .gastro-category {
            color: var(--gold);
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: block;
        }

        .gastro-description {
            font-size: 15px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
        }

        .gastro-highlight {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: rgba(197, 160, 89, 0.1);
            border-left: 3px solid var(--gold);
            font-weight: 600;
            color: var(--dark-blue);
        }

        .gastro-highlight .material-symbols-outlined {
            color: var(--gold);
        }

        /* ========== ÉTLAP ELŐZETES ========== */
        .gastro-menu-preview {
            padding: 80px 0;
            background: linear-gradient(rgba(10, 30, 60, 0.9), rgba(10, 30, 60, 0.9)), 
                        url('img/etterem_belso.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: var(--white);
            text-align: center;
        }

        .gastro-menu-preview h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 40px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .menu-category {
            background: rgba(255,255,255,0.05);
            padding: 30px 20px;
            border-bottom: 3px solid var(--gold);
            transition: 0.3s;
        }

        .menu-category:hover {
            background: rgba(197, 160, 89, 0.1);
            transform: translateY(-5px);
        }

        .menu-category h3 {
            font-size: 24px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .menu-item {
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(197, 160, 89, 0.3);
            text-align: left;
        }

        .menu-item-name {
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
        }

        .menu-item-price {
            color: var(--gold);
        }

        .menu-item-desc {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            margin-top: 5px;
            font-style: italic;
        }

        .menu-note {
            margin-top: 40px;
            font-size: 16px;
            color: rgba(255,255,255,0.8);
        }

        .menu-note a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
        }

        .menu-note a:hover {
            text-decoration: underline;
        }

        /* ========== KÜLÖNLEGESSÉGEK SZEKCIÓ ========== */
        .gastro-special {
            padding: 80px 0;
            background: var(--cream);
            text-align: center;
        }

        .gastro-special h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--dark-blue);
            margin-bottom: 40px;
        }

        .special-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .special-item {
            background: var(--white);
            padding: 40px 30px;
            border: 1px solid var(--gold);
            transition: 0.3s;
        }

        .special-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(197, 160, 89, 0.1);
        }

        .special-item .material-symbols-outlined {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .special-item h3 {
            font-size: 24px;
            color: var(--dark-blue);
            margin-bottom: 15px;
        }

        .special-item p {
            color: #666;
            line-height: 1.8;
        }

        /* ========== SÉF AJÁNLATA ========== */
        .gastro-chef {
            padding: 60px 0;
            background: var(--white);
        }

        .chef-grid {
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            gap: 50px;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
        }

        .chef-image {
            border: 3px solid var(--gold);
            overflow: hidden;
            height: 400px;
        }

        .chef-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chef-content {
            padding: 30px;
        }

        .chef-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: var(--dark-blue);
            margin-bottom: 20px;
        }

        .chef-name {
            font-size: 20px;
            color: var(--gold);
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .chef-quote {
            font-style: italic;
            font-size: 18px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
            padding-left: 20px;
            border-left: 4px solid var(--gold);
        }

        .chef-bio {
            font-size: 15px;
            line-height: 1.8;
            color: #666;
        }

        /* ========== NYITVATARTÁS ========== */
        .gastro-hours {
            padding: 60px 0;
            background: var(--dark-blue);
            color: var(--white);
            text-align: center;
        }

        .gastro-hours h2 {
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
        @media (max-width: 992px) {
            .gastro-hero-content h1 {
                font-size: 56px;
            }

            .gastro-grid {
                grid-template-columns: 1fr;
                padding: 40px 20px;
            }

            .menu-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }

            .special-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }

            .chef-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 0 20px;
            }

            .chef-image {
                height: 300px;
                max-width: 500px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .gastro-hero-content h1 {
                font-size: 42px;
            }

            .gastro-intro h2,
            .gastro-menu-preview h2,
            .gastro-special h2,
            .gastro-hours h2 {
                font-size: 36px;
            }

            .gastro-intro p {
                font-size: 16px;
                padding: 0 20px;
            }

            .gastro-content h3 {
                font-size: 24px;
            }

            .gastro-image {
                height: 250px;
            }

            .chef-content h3 {
                font-size: 30px;
            }

            .chef-quote {
                font-size: 16px;
            }

            .hours-box {
                padding: 30px 20px;
                margin: 0 20px;
            }

            .hours-row {
                font-size: 16px;
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Egységes header -->
    <section class="hero-unified" style="min-height: auto; background: var(--white);">
        <header class="main-header" style="background: linear-gradient(rgba(10, 30, 60, 0.7), rgba(10, 30, 60, 0.7)), 
                url('img/etterem1.jpg');">
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
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <!-- Gasztronómia hero -->
    <div class="gastro-hero">
        <div class="gastro-hero-content">
            <h1>GASZTRONÓMIA</h1>
            <p>ÍZEK ÉS HAGYOMÁNYOK TALÁLKOZÁSA</p>
        </div>
    </div>

    <!-- Bevezető szekció -->
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

    <!-- Gasztronómiai kártyák - 8 kép -->
    <div class="gastro-grid">
        <!-- 1. Reggeli büfé -->
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

        <!-- 2. La carte étterem -->
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

        <!-- 3. Borlap -->
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

        <!-- 4. Desszertek -->
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

        <!-- 5. ITALBÁR - COCKTAIL BAR -->
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

        <!-- 8. Kávézó -->
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

    <!-- Különlegességek szekció -->
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

    <!-- Nyitvatartás -->
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