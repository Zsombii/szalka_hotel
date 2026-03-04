<?php
// gallery.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galéria - Hotel Szalka Mátészalka ****</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Egyszerű galéria stílus */
        .gallery-hero {
            background: var(--dark-blue);
            min-height: 30vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            border-bottom: 3px solid var(--gold);
        }

        .gallery-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            margin-bottom: 20px;
            color: var(--gold);
        }

        .gallery-container {
            padding: 60px 20px;
            background: var(--cream);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .gallery-item {
            background: var(--white);
            border: 1px solid var(--gold);
            overflow: hidden;
            transition: 0.3s;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
        }

        .gallery-item p {
            padding: 10px 15px;
            font-size: 12px;
            color: var(--gold);
            background: var(--dark-blue);
            margin: 0;
            text-align: center;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .gallery-hero h1 {
                font-size: 42px;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
            
            .gallery-item img {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Egységes header -->
    <section class="hero-unified" style="min-height: auto;">
        <header class="main-header">
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
                        <a href="kids.php">SZALKALAND</a>
                        <a href="gastronomy.php">GASZTRONÓMIA</a>
                        <a href="gallery.php" class="active">GALÉRIA</a>
                    </nav>
                </div>
            </div>
        </header>
    </section>

    <!-- Hero -->
    <div class="gallery-hero">
        <div>
            <h1>GALÉRIA</h1>
            <p style="color: var(--white); letter-spacing: 4px;">KÉPEK HOTELÜNKBŐL</p>
        </div>
    </div>

    <!-- Galéria -->
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