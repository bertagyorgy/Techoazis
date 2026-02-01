document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Téma váltó (Egyesített logika) ---
    const themeButtons = document.querySelectorAll(".theme-toggle");
    const htmlElement = document.documentElement;
    const storageKey = 'theme';

    // Segédfüggvény az ikonok és a téma szinkronizálásához
    function applyTheme(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        themeButtons.forEach(btn => {
            const icon = btn.querySelector("i");
            if (icon) {
                // Ha sötét, napocska kell (hogy világosra válthass), ha világos, hold
                if (isDark) {
                    icon.classList.replace("fa-moon", "fa-sun");
                } else {
                    icon.classList.replace("fa-sun", "fa-moon");
                }
            }
        });
    }

    // Kezdeti beállítás betöltése
    const savedTheme = localStorage.getItem(storageKey);
    applyTheme(savedTheme === 'dark');

    // Egyetlen eseményfigyelő a gombokra
    themeButtons.forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const newIsDark = !htmlElement.classList.contains('dark');
            
            localStorage.setItem(storageKey, newIsDark ? 'dark' : 'light');
            applyTheme(newIsDark);
        });
    });

    // --- 2. Mobil menü ---
    const navToggle = document.getElementById('navToggle');
    const navbarMenu = document.getElementById('navCollapseContent'); 
    if (navToggle && navbarMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navbarMenu.classList.toggle('show');
        });
    }

    // --- 3. Jelszó mutató ---
    document.querySelectorAll(".toggle-password").forEach(toggle => {
        toggle.addEventListener("click", function() {
            const passwordInput = this.closest(".password-wrapper")?.querySelector("input");
            if (passwordInput) {
                const isPassword = passwordInput.type === "password";
                passwordInput.type = isPassword ? "text" : "password";
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            }
        });
    });
});