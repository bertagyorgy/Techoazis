<?php
// profile_edit.php
// 1. Config betöltése relatív úton
require_once __DIR__ . '/../core/config.php';

// 2. Háttérlogika betöltése ROOT_PATH-szal
require_once ROOT_PATH . '/actions/profile_edit_logic.php';

// Alapértelmezett fül beállítása
if (empty($action) || $action === 'general') { $action = 'username'; }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kezeld fiókbeállításaidat, profilképedet és adataidat a Techoázison biztonságosan.">
    <title>Profil szerkesztése</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations_microinteractions.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/button_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/modern_navbar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/utility_classes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/reset&base_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/container&grid_system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile_edit_style.css">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/assets/js/index.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/forum.js" defer></script>

</head>
<body>
<?php include ROOT_PATH . '/views/navbar.php'; ?>

<div class="profile-edit-container">
    <div class="profile-edit-header">
        <h1>Profil szerkesztése</h1>
        <a href="<?= BASE_URL ?>/pages/profile.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Vissza
        </a>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>" style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: var(--dark-surface-alt); border: 1px solid var(--border-color);">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-edit-layout">
        <nav class="profile-edit-nav-container">
            <button class="nav-tab <?php echo $action === 'username' ? 'active' : ''; ?>" data-section="username">
                <i class="fas fa-id-card"></i> Felhasználónév
            </button>
            <button class="nav-tab <?php echo $action === 'email' ? 'active' : ''; ?>" data-section="email">
                <i class="fas fa-envelope"></i> Email cím
            </button>
            <button class="nav-tab <?php echo $action === 'image' ? 'active' : ''; ?>" data-section="image">
                <i class="fas fa-image"></i> Profilkép
            </button>
            <button class="nav-tab <?php echo $action === 'password' ? 'active' : ''; ?>" data-section="password">
                <i class="fas fa-lock"></i> Jelszó
            </button>
            <button class="nav-tab <?php echo $action === 'security' ? 'active' : ''; ?>" data-section="security">
                <i class="fas fa-shield-alt"></i> Biztonság
            </button>
        </nav>

        <main class="profile-edit-content">
            <section id="username-section" class="edit-section <?php echo $action === 'username' ? 'active' : ''; ?>">
                <div class="form-card">
                    <div class="card-header"><i class="fas fa-user-edit"></i> Felhasználónév</div>
                    <form method="POST" onsubmit="return validateUsername()">
                        <input type="hidden" name="update_username" value="1">
                        <div class="form-group">
                            <label>Jelenlegi név: <span style="color:var(--primary-500)"><?= htmlspecialchars($user['username']) ?></span></label>
                            <input type="text" id="new_username" name="new_username" class="form-control" 
                                placeholder="Új név" required minlength="3" pattern=".*\S.*">
                            <div class="form-hint" style="font-size: 0.8rem; color: var(--neutral-500); margin-top: 0.5rem;">
                                A névnek legalább 3 karakterből kell állnia (szóközök nélkül).
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Név mentése</button>
                    </form>
                </div>
            </section>

            <section id="email-section" class="edit-section <?php echo $action === 'email' ? 'active' : ''; ?>">
                <div class="form-card">
                    <div class="card-header"><i class="fas fa-at"></i> Email cím</div>
                    <form method="POST">
                        <input type="hidden" name="update_email" value="1">
                        <div class="form-group">
                            <label>Jelenlegi email: <span style="color:var(--primary-500)"><?= htmlspecialchars($user['email']) ?></span></label>
                            <input type="email" name="new_email" class="form-control" placeholder="Új email" required>
                        </div>
                        <button type="submit" class="btn-primary">Email mentése</button>
                    </form>
                </div>
            </section>

            <section id="image-section" class="edit-section <?php echo $action === 'image' ? 'active' : ''; ?>">
                <div class="form-card">
                    <div class="card-header"><i class="fas fa-camera"></i> Profilkép</div>
                    <div class="image-edit-wrapper">
                        <img src="<?= $profile_image ?>" class="image-preview" onerror="this.src='<?= BASE_URL ?>/uploads/profile_images/anonymous.png'">
                        <form method="POST" enctype="multipart/form-data" class="upload-form-group">
                            <input type="hidden" name="update_image" value="1">
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-upload"></i> Kép kiválasztása
                                    <input type="file" name="profile_image" accept="image/*" required onchange="previewImage(this)">
                                </label>
                            </div>
                            <button type="submit" class="btn-primary">Feltöltés indítása</button>
                        </form>
                    </div>
                </div>
            </section>

            <section id="password-section" class="edit-section <?php echo $action === 'password' ? 'active' : ''; ?>">
                <div class="form-card">
                    <div class="card-header"><i class="fas fa-key"></i> Jelszó módosítása</div>
                    <form method="POST" onsubmit="return validatePasswords()">
                        <input type="hidden" name="update_password" value="1">
                        <div class="form-group">
                            <label>Jelenlegi jelszó</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Új jelszó</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Megerősítés</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Jelszó frissítése</button>
                    </form>
                </div>
            </section>

            <section id="security-section" class="edit-section <?php echo $action === 'security' ? 'active' : ''; ?>">
                <div class="form-card" style="border-color: var(--danger);">
                    <div class="card-header" style="color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> Fiók törlése</div>
                    <p style="margin-bottom: 1.5rem; color: var(--neutral-500);">A fiók törlése nem vonható vissza.</p>
                    <button type="button" class="btn-primary" style="background: var(--danger);" onclick="confirmDeleteAccount()">Fiók végleges törlése</button>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.edit-section').forEach(s => s.classList.remove('active'));
        document.getElementById(this.dataset.section + '-section').classList.add('active');
        history.pushState(null, null, `?action=${this.dataset.section}`);
    });
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.querySelector('.image-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function validateUsername() {
    const usernameInput = document.getElementById('new_username');
    // Trimmelés: eltávolítjuk a szóközöket az elejéről és a végéről
    const trimmedValue = usernameInput.value.trim();
    
    if (trimmedValue.length < 3) {
        alert("A felhasználónévnek legalább 3 karakter hosszúnak kell lennie (szóközök nélkül)!");
        return false;
    }
    
    // Frissítjük az input értékét a trimmelt változatra küldés előtt
    usernameInput.value = trimmedValue;
    return true;
}

function validatePasswords() {
    if (document.getElementById('new_password').value !== document.getElementById('confirm_password').value) {
        alert("A két új jelszó nem egyezik!");
        return false;
    }
    return true;
}

function confirmDeleteAccount() {
    if (confirm('BIZTOSAN TÖRÖLNI SZERETNÉD?')) { window.location.href = '<?= BASE_URL ?>/app/delete_account.php'; }
}
</script>
</body>
</html>