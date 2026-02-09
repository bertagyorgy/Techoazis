document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Téma váltó (Egyesített logika) ---
    // A CSS-ben győződj meg róla, hogy az elemek a :root .dark szelektorra változnak
    const themeButtons = document.querySelectorAll(".theme-toggle");
    const htmlElement = document.documentElement;
    const storageKey = 'theme';

    function applyTheme(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        themeButtons.forEach(btn => {
            const icon = btn.querySelector("i");
            if (icon) {
                if (isDark) {
                    icon.classList.replace("fa-moon", "fa-sun");
                } else {
                    icon.classList.replace("fa-sun", "fa-moon");
                }
            }
        });
    }

    const savedTheme = localStorage.getItem(storageKey);
    // Ellenőrizzük a rendszerbeállítást is, ha még nincs mentett téma
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(savedTheme === 'dark' || (!savedTheme && prefersDark));

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

    // --- 4. Scroll Reveal (Hogy az index.php szekciói megjelenjenek) ---
    const revealElements = document.querySelectorAll('.reveal');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Ha egyszer megjelent, ne figyeljük tovább
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15 // 15% láthatóság után aktiválódik
    });

    revealElements.forEach(el => {
        revealObserver.observe(el);
    });

});