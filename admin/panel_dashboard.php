<?php
// panel_dashboard.php

// Összesítők
function getCount($conn, $sql) {
    return (int)$conn->query($sql)->fetch_row()[0];
}

$stats = [
    'users'    => getCount($conn, "SELECT COUNT(*) FROM users"),
    'posts'    => getCount($conn, "SELECT COUNT(*) FROM posts"),
    'comments' => getCount($conn, "SELECT COUNT(*) FROM comments"),
    'articles' => getCount($conn, "SELECT COUNT(*) FROM articles WHERE status='published'"),
    'products' => getCount($conn, "SELECT COUNT(*) FROM products"),
];

// Legutóbbi elemek
$latest = [];
$latest['user']    = $conn->query("SELECT username, registration_date FROM users ORDER BY registration_date DESC LIMIT 1")->fetch_assoc();
$latest['post']    = $conn->query("SELECT title, created_at FROM posts ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['comment'] = $conn->query("SELECT content, created_at FROM comments ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['article'] = $conn->query("SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['product'] = $conn->query("SELECT product_name, created_at FROM products ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Login chart
$loginLabels = [];
$loginData   = [];

$res = $conn->query("
    SELECT DATE(login_date) AS day, COUNT(*) AS cnt
    FROM login
    WHERE login_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(login_date)
    ORDER BY day
");

while ($row = $res->fetch_assoc()) {
    $loginLabels[] = $row['day'];
    $loginData[]   = (int)$row['cnt'];
}
?>

<div class="top-bar">
  <h1><i class="fa-solid fa-chart-simple"></i> Üdv az Admin Panelben, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
</div>

<div class="stat-grid">
    <div class="stat-card">Felhasználók<br><strong class="data"><?= $stats['users'] ?></strong></div>
    <div class="stat-card">Posztok<br><strong class="data"><?= $stats['posts'] ?></strong></div>
    <div class="stat-card">Kommentek<br><strong class="data"><?= $stats['comments'] ?></strong></div>
    <div class="stat-card">Cikkek<br><strong class="data"><?= $stats['articles'] ?></strong></div>
    <div class="stat-card">Termékek<br><strong class="data"><?= $stats['products'] ?></strong></div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Legutóbbi felhasználó</h3>
        <?= htmlspecialchars($latest['user']['username']) ?><br>
        <hr>
        <small><?= $latest['user']['registration_date'] ?></small>
    </div>

    <div class="stat-card">
        <h3>Legutóbbi poszt</h3>
        <?= htmlspecialchars($latest['post']['title']) ?><br>
        <hr>
        <small><?= $latest['post']['created_at'] ?></small>
    </div>

    <div class="stat-card">
        <h3>Legutóbbi komment</h3>
        <?= htmlspecialchars(mb_strimwidth($latest['comment']['content'], 0, 80, '…')) ?><br>
        <hr>
        <small><?= $latest['comment']['created_at'] ?></small>
    </div>

    <div class="stat-card">
        <h3>Legutóbbi cikk</h3>
        <?= htmlspecialchars($latest['article']['title']) ?><br>
        <hr>
        <small><?= $latest['article']['created_at'] ?></small>
    </div>

    <div class="stat-card">
        <h3>Legutóbbi termék</h3>
        <?= htmlspecialchars($latest['product']['product_name']) ?><br>
        <hr>
        <small><?= $latest['product']['created_at'] ?></small>
    </div>
</div>

<div class="stat-card">
    <h3>Bejelentkezések (utolsó 14 nap)</h3>
    <canvas id="loginChart" height="120"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('loginChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($loginLabels) ?>,
        datasets: [{
            label: 'Bejelentkezések',
            data: <?= json_encode($loginData) ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
