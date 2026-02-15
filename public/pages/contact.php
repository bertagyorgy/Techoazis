<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../core/config.php';

?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoázis | Kapcsolat</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/comments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/create_post.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forum.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/group_view.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/post_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive_adjustments.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom_card.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/feature_cards.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/hero_section.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/contact_style.css">

    <!-- Inter font hozzáadása -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>

</head>
<body>
<?php include VIEWS_PATH . '/navbar.php'; ?>

<main class="contact-page">
  <div class="contact-container">

    <header class="contact-head">
      <h2 class="section-title">Kapcsolat</h2>
      <p class="contact-lead">
        Kérdésed van, hibát találtál, vagy javaslatod lenne? Írj nekünk, és 24-48 órán belül válaszolunk.
      </p>
    </header>
      <section class="contact-card">
      <div class="contact-card-titlebar">
        <h2><i class="fas fa-envelope"></i> Üzenet küldése</h2>
      </div>

      <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success">
          ✅ Köszönjük! Az üzenetedet megkaptuk, hamarosan válaszolunk.
        </div>
      <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert alert-danger">
          ❌ Hiba történt: <?= htmlspecialchars($_GET['msg'] ?? 'Ismeretlen hiba', ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/pages/send_contact" method="POST">
        <div class="form-group">
          <label for="name">Név</label>
          <input id="name" name="name" type="text" class="form-control" required minlength="2" />
        </div>

        <div class="form-group">
          <label for="email">Email cím</label>
          <input id="email" name="email" type="email" class="form-control" required />
        </div>

        <div class="form-group">
          <label for="title">Tárgy</label>
          <input id="title" name="title" type="text" class="form-control"
                 required minlength="3" maxlength="120"
                 placeholder="Pl.: Profil probléma" />
          <div class="form-hint">Add meg röviden, miről szól az üzenet.</div>
        </div>

        <div class="form-group">
          <label for="message">Üzenet</label>
          <textarea id="message" name="message" rows="6" class="form-control" required minlength="10"></textarea>
        </div>
        <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div style="margin-top: 2rem;">
          <button type="submit" class="btn-message-seller">
            <i class="fas fa-paper-plane"></i> Üzenet küldése
          </button>
        </div>
        <?php else: ?>
          <p style="color: var(--text-light)">Jelentkezz be, hogy üzenetet küldhess</p>
        <?php endif; ?>
      </form>
    </section>
  </div>
</main>

<footer class="footer">
    <div class="custom-container">
        <div class="grid-row">
            <div class="grid-col-4">
                <div class="footer-brand">
                    <h3 class="footer-subtitle">Techoázis</h3>
                    <p class="footer-description">
                        A hely, ahol a technológia, a közösség és az innováció találkozik.
                    </p>
                </div>
            </div>
            <div class="grid-col-4 footer-nav">
                <h3 class="footer-title">Navigáció</h3>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index" class="footer-link"><i class="fas fa-home"></i> Főoldal</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/shop" class="footer-link"><i class="fas fa-shopping-cart"></i> Webshop</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/forum" class="footer-link"><i class="fas fa-comments"></i> Csevegés</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/articles" class="footer-link"><i class="fa-solid fa-pen"></i>Cikkek</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/about_us" class="footer-link"><i class="fa-solid fa-address-card"></i>Rólunk</a></li>
                    <?php
                    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'A'): ?>
                        <li><a href="<?= BASE_URL ?>/admin/admin" class="footer-link"><i class="fas fa-cog"></i> Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="grid-col-4 footer-social">
                <h3 class="footer-title">Kövess minket</h3>
                <div class="social-icons-wrapper">
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
        </div>
    </div>
</footer>
</body>
</html>
