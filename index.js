const reveals = document.querySelectorAll(".reveal");

window.addEventListener("scroll", () => {
  const windowHeight = window.innerHeight;

  reveals.forEach((el) => {
    const rect = el.getBoundingClientRect();
    if (rect.top < windowHeight - 100) {
      // a delay-t az elem bal offset-je alapján állítjuk
      const delay = rect.left / 5; // minél jobbra van, annál nagyobb delay
      setTimeout(() => el.classList.add("active"), delay);
    }
  });
});
