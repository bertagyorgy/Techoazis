<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Techoazis | Adminpanel</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="index.css">
    <script src="index.js" defer></script>
</head>
<body>
    <h1>Üdv az Admin Panelben, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Itt kezelheted a felhasználókat, termékeket és bejegyzéseket.</p>
</body>
</html>
