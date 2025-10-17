// Mobil menü (hamburger) működtetése
const navToggle = document.getElementById('navToggle');
  const navbarMenu = document.getElementById('navCollapseContent'); // a collapse div-ed

  navToggle.addEventListener('click', () => {
    navToggle.classList.toggle('active'); // animáció
    navbarMenu.classList.toggle('show'); // menü nyit/zár
  });


// Scroll Reveal animáció
function reveal() {
  const reveals = document.querySelectorAll(".reveal");

  for (let i = 0; i < reveals.length; i++) {
    const windowHeight = window.innerHeight;
    const elementTop = reveals[i].getBoundingClientRect().top;
    const elementVisible = 150; // Milyen magasságnál jelenjen meg

    if (elementTop < windowHeight - elementVisible) {
      reveals[i].classList.add("active");
    } else {
      reveals[i].classList.remove("active"); // Ha azt akarod, hogy visszagörgetésnél újra eltűnjön
    }
  }
}

window.addEventListener("scroll", reveal);
// Oldal betöltésekor is fusson le, hogy a látható elemek megjelenjenek
reveal();