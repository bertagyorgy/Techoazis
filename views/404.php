<?php
// Ha véletlenül közvetlenül hívnák meg, ne szálljon el
if (!defined('BASE_URL')) {
    // Visszaugrunk a configért, ha kell (de index.php-n keresztül ez nem fut le)
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis - Oldal nem található</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/static/modern_navbar.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="<?= BASE_URL ?>/static/index.js" defer></script>
    <style>
        .main-404 {
            margin: 0;
            height: 100vh;
            /* A háttérképnél is fontos a BASE_URL! */
            background: url('<?= BASE_URL ?>/images/desert_night2.jpeg');
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .main-404 h1 {
            font-size: 8rem;
            margin: 0;
            opacity: 0.7;
        }

        .main-404 p {
            font-size: 1.5rem;
            margin-top: 1rem;
            opacity: 0.7;
            color: #f0f0f0;
        }
        
        .home-btn {
            margin-top: 2rem;
            padding: 10px 20px;
            background-color: var(--primary-600, #007bff);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }

        .main-404 footer {
            position: absolute;
            bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php 
    // ROOT_PATH használata a biztos betöltésért
    include ROOT_PATH . '/views/navbar.php'; 
    ?>
    
    <div class="main-404">
        <h1>404</h1>
        <p>A keresett oldal nem található.</p>
        <a href="<?= BASE_URL ?>/index.php" class="home-btn">Vissza a főoldalra</a>

        <footer>
            &copy; <?php echo date("Y"); ?> Techoázis
        </footer>
    </div>
</body>
</html>