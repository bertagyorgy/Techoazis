<?php
// panel_dashboard.php - Techoázis card layout

function getCount($conn, $sql) {
    return (int)$conn->query($sql)->fetch_row()[0];
}

$stats = [
    'users'    => ['val' => getCount($conn, "SELECT COUNT(*) FROM users"), 'icon' => 'fa-users', 'bg' => 'linear-gradient(135deg, #4f46e5, #818cf8)', 'label' => 'Felhasználók'],
    'posts'    => ['val' => getCount($conn, "SELECT COUNT(*) FROM posts"), 'icon' => 'fa-paper-plane', 'bg' => 'linear-gradient(135deg, #0891b2, #22d3ee)', 'label' => 'Posztok'],
    'comments' => ['val' => getCount($conn, "SELECT COUNT(*) FROM comments"), 'icon' => 'fa-comments', 'bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'label' => 'Kommentek'],
    'articles' => ['val' => getCount($conn, "SELECT COUNT(*) FROM articles WHERE status='published'"), 'icon' => 'fa-newspaper', 'bg' => 'linear-gradient(135deg, #3b82f6, #60a5fa)', 'label' => 'Cikkek'],
    'products' => ['val' => getCount($conn, "SELECT COUNT(*) FROM products"), 'icon' => 'fa-box', 'bg' => 'linear-gradient(135deg, #10b981, #34d399)', 'label' => 'Termékek'],
];

$latest = [];
$latest['user']    = $conn->query("SELECT username, registration_date FROM users ORDER BY registration_date DESC LIMIT 1")->fetch_assoc();
$latest['post']    = $conn->query("SELECT title, created_at FROM posts ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['comment'] = $conn->query("SELECT content, created_at FROM comments ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['article'] = $conn->query("SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$latest['product'] = $conn->query("SELECT product_name, created_at FROM products ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

$loginLabels = []; $loginData = [];
$res = $conn->query("SELECT DATE(login_date) AS day, COUNT(*) AS cnt FROM login WHERE login_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY DATE(login_date) ORDER BY day");
while ($row = $res->fetch_assoc()) {
    $loginLabels[] = $row['day'];
    $loginData[] = (int)$row['cnt'];
}
?>

<style>
/* ===== Techoázis dashboard skin ===== */
:root{
    --tx-card: #ffffff;
    --tx-soft: #f8fafc;
    --tx-shadow: 0 12px 30px rgba(2, 6, 23, 0.08);
    --tx-shadow2: 0 10px 25px rgba(2, 6, 23, 0.06);
    --tx-radius: 16px;
}

/* wrapper */
.dashboard-wrap{
    max-width: 95%;
    margin: 0 auto;
    padding: 10px 5px 30px;
}

/* top bar finomabb */
.top-bar{
    border-radius: var(--tx-radius);
    background: linear-gradient(135deg, rgba(255,107,53,.12), rgba(34,211,238,.10));
    border: 1px solid var(--admin-border);
    box-shadow: var(--tx-shadow2);
    padding: 18px 18px;
    margin-bottom: 18px;
}
.top-bar h1{
    margin: 0;
    font-size: 2rem;
    color: var(--admin-secondary);
    display:flex;
    align-items:center;
    gap:10px;
}
.top-bar h1 i{
    color: var(--admin-accent);
}

/* szekció card */
.section-card{
    background: var(--tx-card);
    border: 1px solid var(--admin-border);
    border-radius: var(--tx-radius);
    box-shadow: var(--tx-shadow2);
    margin: 16px 0;
    overflow: hidden;
}
.section-head{
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.02), rgba(15, 23, 42, 0));
    border-bottom: 1px solid var(--admin-border);
}
.section-title{
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--admin-secondary);
    display:flex;
    align-items:center;
    gap:10px;
}
.section-title i{ color: var(--admin-accent); }

.section-body{
    padding: 16px;
}

/* gyorsgombok */
.quick-actions{
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-quick{
    padding: 10px 14px;
    background: var(--tx-card);
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    color: var(--admin-text);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 700;
    transition: all .18s ease;
    display:flex;
    align-items:center;
    gap: 8px;
}
.btn-quick:hover{
    border-color: var(--admin-accent);
    color: var(--admin-accent);
    transform: translateY(-2px);
    box-shadow: 0 10px 18px rgba(2,6,23,.08);
}

/* stat grid */
.stat-grid{
    display:grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
}
@media (max-width: 1100px){ .stat-grid{ grid-template-columns: repeat(3, 1fr);} }
@media (max-width: 700px){ .stat-grid{ grid-template-columns: repeat(2, 1fr);} }

/* a te stat-cardod kompatibilis marad, csak adunk neki “techo” kártya stílust */
.stat-card{
    background: var(--tx-card);
    border: 1px solid var(--admin-border);
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 10px 18px rgba(2,6,23,.06);
}
.stat-card-flex{
    display:flex;
    align-items:center;
    gap: 12px;
}
.icon-box{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 10px 18px rgba(2,6,23,.12);
}
.stat-info span{
    font-size: .82rem;
    color: var(--admin-text-light);
    font-weight: 700;
}
.stat-info strong{
    display:block;
    font-size: 1.35rem;
    color: var(--admin-secondary);
    line-height: 1.1;
}

/* latest grid */
.dashboard-grid{
    display:grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
}
@media (max-width: 1100px){ .dashboard-grid{ grid-template-columns: repeat(3, 1fr);} }
@media (max-width: 700px){ .dashboard-grid{ grid-template-columns: repeat(1, 1fr);} }

.stat-card h3{
    margin: 0 0 10px 0;
    font-size: .95rem;
    color: var(--admin-secondary);
    font-weight: 900;
}

.latest-box{
    background: var(--tx-soft);
    border: 1px dashed rgba(15,23,42,.15);
    padding: 10px 12px;
    border-radius: 12px;
    font-weight: 700;
    color: var(--admin-secondary);
    font-size: .95rem;
}
.latest-date{
    font-size: .78rem;
    color: var(--admin-text-light);
    display:block;
    margin-top: 7px;
    font-weight: 600;
}

/* chart card fix */
.chart-wrap{
    max-width: 980px;
    margin: 0 auto;
}
</style>

<div class="dashboard-wrap">

    <div class="top-bar">
        <h1><i class="fa-solid fa-chart-simple"></i> Üdv az Admin Panelben, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
    </div>

    <!-- ===== GYORS HOZZÁADÁS ===== -->
    <section class="section-card">
        <div class="section-head">
            <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Hozzáadás gyorsmenü</h2>
        </div>
        <div class="section-body">
            <div class="quick-actions">
                <a href="panel_products.php?action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új termék</a>
                <a href="panel_posts.php?action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új poszt</a>
                <a href="panel_articles.php?action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új cikk</a>
                <a href="panel_users.php?action=add" class="btn-quick"><i class="fa-solid fa-plus"></i> Új felhasználó</a>
            </div>
        </div>
    </section>

    <!-- ===== STATISZTIKA ===== -->
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

    <!-- ===== LEGUTÓBBI AKTIVITÁSOK ===== -->
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

    <!-- ===== LOGIN TREND ===== -->
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
