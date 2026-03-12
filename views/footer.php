<footer class="footer">
    <div class="custom-container">
        <!-- FELSŐ RÉSZ -->
        <div class="footer-top">
            <!-- BRAND -->
            <div class="footer-brand">
                <h3 class="footer-subtitle">Techoázis</h3>
                <p class="footer-description">
                    A hely, ahol a technológia, a közösség és az innováció találkozik.
                </p>
            </div>
            <!-- JOBB OLDALI BLOKK -->
            <div class="footer-right">
                <!-- NAV -->
                <div class="footer-section">
                    <h3 class="footer-title">Navigáció</h3>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/index.php" class="footer-link">Főoldal</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop.php" class="footer-link">Vásárlás</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/forum.php" class="footer-link">Közösség</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/articles.php" class="footer-link">Tudástár</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/about_us.php" class="footer-link">Rólunk</a></li>
                    </ul>
                </div>
                <!-- CONTACT -->
                <div class="footer-section">
                    <h3 class="footer-title">Elérhetőség</h3>
                    <ul class="footer-links">
                        <li>
                            <a href="mailto:support@techoazis.hu" class="footer-link">
                                support@techoazis.hu
                            </a>
                        </li>
                        <li class="footer-link">Budapest, Magyarország</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- ALSÓ RÉSZ -->
        <div class="footer-bottom">
            <div class="social-icons-wrapper">
                <a href="#" class="social-icon" aria-label="facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon" aria-label="instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon" aria-label="x"><i class="fab fa-x-twitter"></i></a>
                <a href="#" class="social-icon" aria-label="linkedin"><i class="fab fa-linkedin-in"></i></a>
            </div>

            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
            </div>
        </div>
    </div>
</footer>