<?php
// profile_edit.php
// 1. Config betöltése relatív úton
require_once __DIR__ . '/../core/config.php';

// 2. Háttérlogika betöltése ROOT_PATH-szal
require_once ROOT_PATH . '/actions/profile_edit_logic.php';
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
    <div class="profile-edit-card">
    <div class="profile-edit-header">
        <h1>Profil szerkesztése</h1>
        <a href="<?= BASE_URL ?>/pages/profile.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Vissza a profilhoz
        </a>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-edit-nav">
        <button class="nav-tab <?php echo $action === 'general' ? 'active' : ''; ?>" data-section="general">
            <i class="fas fa-user"></i> Alapadatok
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
    </div>

    <section id="general-section" class="edit-section <?php echo $action === 'general' ? 'active' : ''; ?>">
        
        <form method="POST" class="edit-form">
            <input type="hidden" name="update_username" value="1">
            
            <div class="form-group">
                <label for="current_username">Jelenlegi felhasználónév:</label>
                <input type="text" id="current_username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="new_username">Új felhasználónév:</label>
                <input type="text" id="new_username" name="new_username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3" maxlength="100">
                <div class="form-hint">Legalább 3 karakter</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Felhasználónév mentése
                </button>
            </div>
        </form>
        
        <hr style="margin: 2rem 0; border-color: var(--border-color);">

        <form method="POST" class="edit-form">
            <input type="hidden" name="update_email" value="1">
            
            <div class="form-group">
                <label for="current_email">Jelenlegi email cím:</label>
                <input type="email" id="current_email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="new_email">Új email cím:</label>
                <input type="email" id="new_email" name="new_email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <div class="form-hint">Érvényes email címet adj meg</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Email cím mentése
                </button>
            </div>
        </form>
    </section>

    <section id="image-section" class="edit-section <?php echo $action === 'image' ? 'active' : ''; ?>">
        
        <div class="edit-form" style="text-align: center;">
            <img src="<?php echo $profile_image; ?>" alt="Profilkép előnézet" class="image-preview" 
                 onerror="this.src='<?= BASE_URL ?>/uploads/profile_images/anonymous.png'">
            
            <form method="POST" enctype="multipart/form-data">
                <div class="file-upload">
                    <label class="file-upload-label">
                        <i class="fas fa-upload"></i> Kép kiválasztása
                        <input type="file" name="profile_image" accept="image/*" required onchange="previewImage(this)">
                    </label>
                </div>
                
                <div class="form-hint">Max. 5MB, JPG, PNG, WEBP vagy GIF formátum</div>
                
                <div class="form-actions" style="justify-content: center;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Feltöltés
                    </button>
                </div>
            </form>
            
        </div>
    </section>

    <section id="password-section" class="edit-section <?php echo $action === 'password' ? 'active' : ''; ?>">
        
        <form method="POST" class="edit-form">
            <input type="hidden" name="update_password" value="1">
            
            <div class="form-group">
                <label for="current_password">Jelenlegi jelszó:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">Új jelszó:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-hint">Legalább 6 karakter</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Új jelszó megerősítése:</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-key"></i> Jelszó megváltoztatása
                </button>
            </div>
        </form>
    </section>

    <section id="security-section" class="edit-section <?php echo $action === 'security' ? 'active' : ''; ?>">
        
        <div class="edit-form">
            <div class="form-group">
                <h3 style="color: var(--danger); margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i> Veszélyes műveletek
                </h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4>Funkciók:</h4>
                    <p style="color: var(--text-light); margin-bottom: 1rem;">
                        Ezen funkciók végrehajtása előtt gondosan gondold át döntésedet!
                    </p>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button type="button" class="delete-image-btn" onclick="confirmDeleteAccount()">
                        <i class="fas fa-user-slash"></i> Fiók végleges törlése
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>
</div>

<script>
// Tab váltás
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const sectionId = this.dataset.section + '-section';
        
        // Tabok aktív állapotának frissítése
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Section-ök megjelenítése/elrejtése
        document.querySelectorAll('.edit-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');
        
        // URL frissítése
        history.pushState(null, null, `?action=${this.dataset.section}`);
    });
});

// Profilkép előnézet
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.image-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Jelszó mutatása/elrejtése
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        toggle.className = 'fas fa-eye';
    }
}

// Fiók törlés megerősítése
function confirmDeleteAccount() {
    if (confirm('⚠️ VIGYÁZAT!\n\nA fiók törlésével:\n• Minden adatod véglegesen törlődik\n• Termékeid eltűnnek\n• Beszélgetéseid törlődnek\n• Nem vonható vissza!\n\nBiztos, hogy folytatod?')) {
        window.location.href = '<?= BASE_URL ?>/app/delete_account.php';
    }
}
</script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close(); 
}
?>