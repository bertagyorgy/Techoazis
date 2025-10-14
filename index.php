<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous" defer></script>
    <script src="index.js" defer></script>
    <title>Techoazis | Home</title>
    <link rel="stylesheet" href="index.css">
  </head>
  <body>
  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

  <!-- Hero section -->
  <div class="hero-section d-flex align-items-center">
    <div class="container text-center text-md-start">
      <div class="hero-text">
        <h1>Csevegés, vásárlás, olvasás, meg persze a tech. Mind egy helyen.</h1>
        <p>Fedezze fel oldalunk nyújtotta szolgáltatásokat.</p>
        <a href="shop.php">
          <button type="button" class="shopnow">Vásárolj most</button>
        </a>
      </div>
    </div>
  </div>


  <!-- Feature section-->
  <section class="container py-5">
    <div class="container text-center">
      <h2 class="text-center mb-5 fw-bold">Mit találsz nálunk?</h2>
      <div class="row text-center g-4">
        
        <div class="col-12 col-md-6 col-lg-3 reveal">
          <div class="p-4 shadow-sm rounded feature-card h-100">
            <i class="fa-solid fa-users fa-3x mb-3 text-primary"></i>
            <h4>Közösség</h4>
            <p>Csevegj, kérdezz, oszd meg tapasztalataid más techrajongókkal.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 reveal">
          <div class="p-4 shadow-sm rounded feature-card h-100">
            <i class="fa-solid fa-cart-shopping fa-3x mb-3 text-success"></i>
            <h4>Vásárlás</h4>
            <p>Fedezd fel a legújabb technológiai termékeket a webshopunkban.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 reveal">
          <div class="p-4 shadow-sm rounded feature-card h-100">
            <i class="fa-solid fa-book fa-3x mb-3 text-info"></i>
            <h4>Tudástár</h4>
            <p>Olvass cikkeket, útmutatókat és fejleszd a tudásod.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3 reveal">
          <div class="p-4 shadow-sm rounded feature-card h-100">
            <i class="fa-solid fa-diagram-project fa-3x mb-3 text-warning"></i>
            <h4>Projektek</h4>
            <p>Nézd meg, min dolgoznak mások vagy mutasd be a saját munkád.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <div class="gap"></div>

  <!-- Latest content -->
  <section class="container-section py-5">
    <div class="container text-center">
      <h2 class="text-center mb-5 fw-bold">Friss tartalmak előnézete</h2>
      <div class="row g-4">

        <!-- Itt majd SQL-ből jönnek a cikkek / projektek / termékek -->
        <div class="col-md-4">
          <div class="card h-100 feature-card reveal">
            <img src="images/nikon_z50.jpg" class="card-img-top" alt="Cikk 1">
            <div class="card-body">
              <h5 class="card-title">Új projekt: SmartHub</h5>
              <p class="card-text">Egy közösségi okos eszköz kezelő, amely forradalmasítja az IoT-t.</p>
              <a href="#" class="shopnow-small">Tovább</a>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card h-100 feature-card reveal">
            <img src="images/ipad_air.jpg" class="card-img-top" alt="Cikk 2">
            <div class="card-body">
              <h5 class="card-title">Legújabb TechCikk</h5>
              <p class="card-text">Miként változtatja meg az AI a mindennapi vásárlást?</p>
              <a href="#" class="shopnow-small">Olvass tovább</a>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card h-100 feature-card reveal">
            <img src="images/macbook_air_m2.jpg" class="card-img-top" alt="Cikk 3">
            <div class="card-body">
              <h5 class="card-title">Új termék a shopban</h5>
              <p class="card-text">Fedezd fel a legújabb tech kiegészítőket kedvező áron!</p>
              <a href="#" class="shopnow-small">Vásárolj most</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <div class="gap"></div>

  <!-- Why choose us -->
  <section class="container-section py-5">
    <div class="container text-center">
      <h2 class="text-center mb-5 fw-bold">Miért válassz minket?</h2>
      <div class="row g-4 justify-content-center">

        <div class="col-6 col-md-3 reveal">
          <div class="whyus-icon">
            <i class="bi bi-lightning-charge-fill"></i>
            <h5>Gyors</h5>
            <p>Villámgyors oldalbetöltés és optimalizált élmény minden eszközön.</p>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="whyus-icon">
            <i class="bi bi-people-fill"></i>
            <h5>Közösségi</h5>
            <p>Beszélgess, ossz meg projekteket, és tanulj másoktól.</p>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="whyus-icon">
            <i class="bi bi-cpu-fill"></i>
            <h5>Modern</h5>
            <p>A legfrissebb technológiákkal és eszközökkel építve.</p>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="whyus-icon">
            <i class="bi bi-shield-lock-fill"></i>
            <h5>Biztonságos</h5>
            <p>Adatvédelem és biztonság a legmagasabb szinten.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <div class="gap"></div>
  
  <!-- Footer -->
  <footer class="footer mt-5 py-5 text-white">
    <div class="container">
      <div class="row gy-4">
        
        <!-- Logo / Rövid leírás -->
        <div class="col-md-4">
          <h3 class="fw-bold text-uppercase">Techoázis</h3>
          <p class="small mb-0">
            A hely, ahol a technológia, a közösség és az innováció találkozik.
          </p>
        </div>

        <!-- Gyors linkek -->
        <div class="col-md-4 text-md-center">
          <h5 class="fw-bold mb-3">Navigáció</h5>
          <ul class="list-unstyled">
            <li><a href="index.php" class="footer-link">Főoldal</a></li>
            <li><a href="shop.php" class="footer-link">Webshop</a></li>
            <li><a href="forum.php" class="footer-link">Csevegés</a></li>
            <li><a href="articles.php" class="footer-link">Cikkek</a></li>
            <li><a href="contact.php" class="footer-link">Kapcsolat</a></li>
          </ul>
        </div>

        <!-- Közösségi ikonok -->
        <div class="col-md-4 text-md-end">
          <h5 class="fw-bold mb-3">Kövess minket</h5>
          <div class="d-flex justify-content-md-end justify-content-center gap-3">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>

      </div>

      <hr class="my-4 bg-light opacity-25">

      <div class="text-center small">
        &copy; <?php echo date('Y'); ?> Techoázis. Minden jog fenntartva.
      </div>
    </div>
  </footer>

  </body>
</html>
