// Várunk, amíg a teljes HTML dokumentum betöltődik
document.addEventListener("DOMContentLoaded", function() {

    // === 1. Mobil menü (hamburger) ===
    const navToggle = document.getElementById('navToggle');
    const navbarMenu = document.getElementById('navCollapseContent'); 

    // Csak akkor futtatjuk, ha ezek az elemek LÉTEZNEK ezen az oldalon
    if (navToggle && navbarMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active'); // animáció
            navbarMenu.classList.toggle('show'); // menü nyit/zár
        });
    }

    // === 2. Scroll Reveal animáció ===
    const reveals = document.querySelectorAll(".reveal");

    // Csak akkor futtatjuk, ha LÉTEZNEK .reveal elemek ezen az oldalon
    if (reveals.length > 0) {
        function reveal() {
            const windowHeight = window.innerHeight;
            const elementVisible = 150; 

            for (let i = 0; i < reveals.length; i++) {
                const elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("active");
                } else {
                    reveals[i].classList.remove("active");
                }
            }
        }
        window.addEventListener("scroll", reveal);
        reveal(); // Oldal betöltésekor is fusson le
    }


    // === 3. Jelszó Váltó (A te kódod) ===
    const toggles = document.querySelectorAll(".toggle-password");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", function() {
            // Az ikonhoz tartozó input a legközelebbi password-wrapper-en belül van
            const passwordInput = this.closest(".password-wrapper").querySelector("input");

            if (passwordInput) {
                const isPassword = passwordInput.type === "password";
                passwordInput.type = isPassword ? "text" : "password";

                // Ikon váltás
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            }
        });
    });

    // === 4. Támaváltó (A te kódod) ===
    const themes = document.querySelectorAll(".theme-toggle");

    themes.forEach(theme => {
        theme.addEventListener("click", function() {

            // Az ikon az aktuális gombon belül van
            const icon = this.querySelector("i");

            icon.classList.toggle("fa-sun");
            icon.classList.toggle("fa-moon");
        });
    });

}); // Itt a DOMContentLoaded eseményfigyelő vége