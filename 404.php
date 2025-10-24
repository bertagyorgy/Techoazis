<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            background: url(images/minimalist_background_notfound.jpg);
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

        h1 {
            font-size: 8rem;
            margin: 0;
            opacity: 0.7;
        }

        p {
            font-size: 1.5rem;
            margin-top: 1rem;
            opacity: 0.7;
        }

        footer {
            position: absolute;
            bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>A keresett oldal nem található.</p>

    <footer>
        &copy; <?php echo date("Y"); ?> Techoázis
    </footer>
</body>
</html>