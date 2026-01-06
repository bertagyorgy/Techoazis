<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="static/index.css">
    <link rel="stylesheet" href="static/reset&base_styles.css">
    <link rel="stylesheet" href="static/animations_microinteractions.css">
    <link rel="stylesheet" href="static/button_system.css">
    <link rel="stylesheet" href="static/login_page.css">
    <link rel="stylesheet" href="static/modern_navbar.css">


    <style>
        .main {
            margin: 0;
            height: 100vh;
            background: url(images/desert_night2.jpeg);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            color: #fff;
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .main h1 {
            font-size: 8rem;
            margin: 0;
            opacity: 0.7;
        }

        .main p {
            font-size: 1.5rem;
            margin-top: 1rem;
            opacity: 0.7;
            color: var(--neutral-100);
        }

        .main footer {
            position: absolute;
            bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php
    include 'views/navbar.php';
    ?>
    <div class="main">
        <h1>404</h1>
        <p>A keresett oldal nem található.</p>

        <footer>
            &copy; <?php echo date("Y"); ?> Techoázis
        </footer>
    </div>
</body>
</html>