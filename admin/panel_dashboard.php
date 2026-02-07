<?php
// /opt/lampp/htdocs/Techoazis/admin/panel_dashboard.php

// 1. Biztonsági ellenőrzés és környezet betöltése
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/app/auth_check.php';

function getCount($conn, $sql) {
    $res = $conn->query($sql);
    return $res ? (int)$res->fetch_row()[0] : 0;
}

// Statisztikák lekérése
$stats = [
    'users'    => ['val' => getCount($conn, "SELECT COUNT(*) FROM users"), 'icon' => 'fa-users', 'bg' => 'linear-gradient(135deg, #4f46e5, #818cf8)', 'label' => 'Felhasználók'],
    'posts'    => ['val' => getCount($conn, "SELECT COUNT(*) FROM posts"), 'icon' => 'fa-paper-plane', 'bg' => 'linear-gradient(135deg, #0891b2, #22d3ee)', 'label' => 'Posztok'],
    'comments' => ['val' => getCount($conn, "SELECT COUNT(*) FROM comments"), 'icon' => 'fa-comments', 'bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'label' => 'Kommentek'],
    'articles' => ['val' => getCount($conn, "SELECT COUNT(*) FROM articles WHERE article_status='published'"), 'icon' => 'fa-newspaper', 'bg' => 'linear-gradient(135deg, #3b82f6, #60a5fa)', 'label' => 'Cikkek'],
    'products' => ['val' => getCount($conn, "SELECT COUNT(*) FROM products"), 'icon' => 'fa-box', 'bg' => 'linear-gradient(135deg, #10b981, #34d399)', 'label' => 'Termékek'],
];

// Legutóbbi aktivitások
$latest = [];
$latest['user']    = $conn->query("SELECT username, registration_date FROM users ORDER BY registration_date DESC LIMIT 1")->fetch_assoc();
$latest['post']    = $conn->query("SELECT title, created_at FROM posts ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['comment'] = $conn->query("SELECT content, created_at FROM comments ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['article'] = $conn->query("SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['product'] = $conn->query("SELECT product_name, created_at FROM products ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Grafikon adatok (utolsó 14 nap)
$loginLabels = []; $loginData = [];
$res = $conn->query("SELECT DATE(login_date) AS day, COUNT(*) AS cnt FROM login WHERE login_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY DATE(login_date) ORDER BY day");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $loginLabels[] = $row['day'];
        $loginData[] = (int)$row['cnt'];
    }
}
?>

<div class="dashboard-wrap">

    <div class="top-bar">
        <h1><i class="fa-solid fa-chart-simple"></i> Üdv az Admin Panelben, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h1>
    </div>

    <section class="section-card">
        <div class="section-head">
            <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Hozzáadás gyorsmenü</h2>
        </div>
        <div class="section-body">
            <div class="quick-actions">
                <a href="<?= BASE_URL ?>/admin/admin?page=panel_products&action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új termék</a>
                <a href="<?= BASE_URL ?>/admin/admin?page=panel_posts&action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új poszt</a>
                <a href="<?= BASE_URL ?>/admin/admin?page=panel_articles&action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új cikk</a>
                <a href="<?= BASE_URL ?>/admin/admin?page=panel_users&action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új felhasználó</a>
            </div>
        </div>
    </section>

    <section class="section-card">
        <div class="section-head">
            <h2 class="section-title"><i class="fa-solid fa-chart-line"></i> Rendszer statisztika</h2>
        </div>
        <div class="section-body">
            <div class="stat-grid">
                <?php foreach ($stats as $key => $s): ?>
                    <div class="stat-card stat-card-flex">
                        <div class="icon-box" style="background: <?= $s['bg'] ?>">
                            <i class="fas <?= $s['icon'] ?>"></i>
                        </div>
                        <div class="stat-info">
                            <span><?= htmlspecialchars($s['label']) ?></span>
                            <strong><?= (int)$s['val'] ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-card">
        <div class="section-head">
            <h2 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Legutóbbi aktivitások</h2>
        </div>
        <div class="section-body">
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Legutóbbi felhasználó</h3>
                    <div class="latest-box"><?= htmlspecialchars($latest['user']['username'] ?? '-') ?></div>
                    <span class="latest-date"><?= htmlspecialchars($latest['user']['registration_date'] ?? '') ?></span>
                </div>
                <div class="stat-card">
                    <h3>Legutóbbi poszt</h3>
                    <div class="latest-box"><?= htmlspecialchars($latest['post']['title'] ?? '-') ?></div>
                    <span class="latest-date"><?= htmlspecialchars($latest['post']['created_at'] ?? '') ?></span>
                </div>
                <div class="stat-card">
                    <h3>Legutóbbi komment</h3>
                    <div class="latest-box"><?= htmlspecialchars(mb_strimwidth($latest['comment']['content'] ?? '-', 0, 70, '…')) ?></div>
                    <span class="latest-date"><?= htmlspecialchars($latest['comment']['created_at'] ?? '') ?></span>
                </div>
                <div class="stat-card">
                    <h3>Legutóbbi cikk</h3>
                    <div class="latest-box"><?= htmlspecialchars($latest['article']['title'] ?? '-') ?></div>
                    <span class="latest-date"><?= htmlspecialchars($latest['article']['created_at'] ?? '') ?></span>
                </div>
                <div class="stat-card">
                    <h3>Legutóbbi termék</h3>
                    <div class="latest-box"><?= htmlspecialchars($latest['product']['product_name'] ?? '-') ?></div>
                    <span class="latest-date"><?= htmlspecialchars($latest['product']['created_at'] ?? '') ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="section-card">
        <div class="section-head">
            <h2 class="section-title"><i class="fa-solid fa-signal"></i> Bejelentkezési trendek (14 nap)</h2>
        </div>
        <div class="section-body">
            <div class="stat-card chart-wrap">
                <canvas id="loginChart" height="110"></canvas>
            </div>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart konfiguráció marad eredeti
new Chart(document.getElementById('loginChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($loginLabels) ?>,
        datasets: [{
            label: 'Bejelentkezések',
            data: <?= json_encode($loginData) ?>,
            borderColor: '#ff6b35',
            backgroundColor: 'rgba(255, 107, 53, 0.10)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#ff6b35'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>