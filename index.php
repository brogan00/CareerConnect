<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CareerConnect</title>
  <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/icons/all.min.css" />
  <link rel="stylesheet" href="assets/CSS/style.css" />
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
    -->
  <link
    rel="icon"
    type="image/png"
    href="./assets/images/hamidou.png"
    width="8" />
</head>

<body>

  <?php include "templates/header.php"; ?>

  <!-- Hero Section -->
  <div class="hero-section text-center py-5">
    <div class="container">
      <h1 class="fw-bold display-4 mb-4">Find Your Dream Job</h1>
      <p class="fs-5 mb-4">
        Connect with top companies and explore endless opportunities.
      </p>
      <div class="d-flex justify-content-center mt-4 row">
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 py-1 text-white">
          <a href="job_search.php" class="btn btn-primary btn-lg scale">Search Jobs</a>
        </div>
        <div class="col-12 col-sm-3 col-md-3 col-lg-3 py-1">
          <a
            href="post_a_job.php"
            class="btn btn-outline-primary btn-lg scale">Post a Job</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Search Bar -->
  <div id="search-bar" class="container search-container mt-5 w-75">
    <form action="job_search.php" method="POST">
      <div class="row align-items-center justify-content-center">
        <div class="col-10 col-md-6 col-lg-5">
          <img src="assets/icons/home.png" class="col-1" alt="Job Icon" />
          <input name="keywords"
            class="col-9 col-md-10"
            type="text"
            placeholder="Intitulé du poste, mots-clés, entreprise" />
        </div>
        <div class="col-10 col-md-6 col-lg-4">
          <img
            src="assets/icons/location.png"
            class="col-1"
            alt="Location Icon" />
          <input name="Location"
            class="col-9 col-md-10"
            type="text"
            placeholder="Ville ou wilaya" />
        </div>
        <button type="submit" class="btn search-button col-10 col-md-4 col-lg-3 mt-md-2 mt-sm-2">
          Rechercher
        </button>
      </div>
    </form>
  </div>

<!-- Featured Jobs Section with Sliding Animation -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Featured Jobs</h2>
    <div class="job-slider-container">
        <div class="job-slider-track" id="jobSliderTrack">
            <!-- Job 1 -->
            <div class="job-slide">
                <div class="card job-card">
                    <div class="card-body">
                        <h5 class="card-title">Ingénieur Civil</h5>
                        <p class="card-text"><i class="fas fa-building"></i> Société: Cosider</p>
                        <p class="card-text"><i class="fas fa-map-marker-alt"></i> Localisation: Tizi Ouzou, Algérie</p>
                        <p class="card-text"><i class="fas fa-money-bill-wave"></i> Salaire: 120,000 - 180,000 DA/mois</p>
                        <div class="skills-tags mt-3">
                            <span class="badge bg-primary">AutoCAD</span>
                            <span class="badge bg-primary">BTP</span>
                            <span class="badge bg-primary">Gestion de projet</span>
                        </div>
                        <a href="#" class="btn btn-primary mt-3">Postuler Maintenant</a>
                    </div>
                </div>
            </div>

            <!-- Job 2 -->
            <div class="job-slide">
                <div class="card job-card">
                    <div class="card-body">
                        <h5 class="card-title">Comptable</h5>
                        <p class="card-text"><i class="fas fa-building"></i> Société: Sonatrach</p>
                        <p class="card-text"><i class="fas fa-map-marker-alt"></i> Localisation: Hassi Messaoud, Algérie</p>
                        <p class="card-text"><i class="fas fa-money-bill-wave"></i> Salaire: 70,000 - 100,000 DA/mois</p>
                        <div class="skills-tags mt-3">
                            <span class="badge bg-primary">Comptabilité</span>
                            <span class="badge bg-primary">Fiscalité</span>
                            <span class="badge bg-primary">Sage</span>
                        </div>
                        <a href="#" class="btn btn-primary mt-3">Postuler Maintenant</a>
                    </div>
                </div>
            </div>

            <!-- Job 3 -->
            <div class="job-slide">
                <div class="card job-card">
                    <div class="card-body">
                        <h5 class="card-title">Médecin Généraliste</h5>
                        <p class="card-text"><i class="fas fa-building"></i> Société: EPH Boufarik</p>
                        <p class="card-text"><i class="fas fa-map-marker-alt"></i> Localisation: Blida, Algérie</p>
                        <p class="card-text"><i class="fas fa-money-bill-wave"></i> Salaire: 150,000 - 250,000 DA/mois</p>
                        <div class="skills-tags mt-3">
                            <span class="badge bg-primary">Médecine</span>
                            <span class="badge bg-primary">Urgences</span>
                            <span class="badge bg-primary">Diagnostic</span>
                        </div>
                        <a href="#" class="btn btn-primary mt-3">Postuler Maintenant</a>
                    </div>
                </div>
            </div>

            <!-- Job 4 -->
            <div class="job-slide">
                <div class="card job-card">
                    <div class="card-body">
                        <h5 class="card-title">Enseignant de Français</h5>
                        <p class="card-text"><i class="fas fa-building"></i> Société: Lycée Ibn Khaldoun</p>
                        <p class="card-text"><i class="fas fa-map-marker-alt"></i> Localisation: Béjaïa, Algérie</p>
                        <p class="card-text"><i class="fas fa-money-bill-wave"></i> Salaire: 60,000 - 90,000 DA/mois</p>
                        <div class="skills-tags mt-3">
                            <span class="badge bg-primary">Pédagogie</span>
                            <span class="badge bg-primary">Littérature</span>
                            <span class="badge bg-primary">Education</span>
                        </div>
                        <a href="#" class="btn btn-primary mt-3">Postuler Maintenant</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="slider-controls text-center mt-3">
        <button class="btn btn-sm btn-outline-primary me-2" id="prevJob"><i class="fas fa-chevron-left"></i></button>
        <button class="btn btn-sm btn-outline-primary" id="nextJob"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<style>
    .job-slider-container {
        overflow: hidden;
        position: relative;
    }
    
    .job-slider-track {
        display: flex;
        transition: transform 0.5s ease;
        gap: 20px;
    }
    
    .job-slide {
        min-width: calc(33.333% - 14px);
        flex: 0 0 auto;
    }
    
    .job-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    @media (max-width: 992px) {
        .job-slide {
            min-width: calc(50% - 10px);
        }
    }
    
    @media (max-width: 768px) {
        .job-slide {
            min-width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('jobSliderTrack');
    const slides = document.querySelectorAll('.job-slide');
    const prevBtn = document.getElementById('prevJob');
    const nextBtn = document.getElementById('nextJob');
    const slideWidth = slides[0].offsetWidth + 20; // including gap
    let currentPosition = 0;
    let autoSlideInterval;
    
    function moveToSlide(position) {
        track.style.transform = `translateX(-${position}px)`;
        currentPosition = position;
    }
    
    function nextSlide() {
        const maxPosition = (slides.length - 3) * slideWidth;
        if (currentPosition < maxPosition) {
            moveToSlide(currentPosition + slideWidth);
        } else {
            moveToSlide(0);
        }
    }
    
    function prevSlide() {
        if (currentPosition > 0) {
            moveToSlide(currentPosition - slideWidth);
        } else {
            const maxPosition = (slides.length - 3) * slideWidth;
            moveToSlide(maxPosition);
        }
    }
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 3000);
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Initialize
    startAutoSlide();
    
    // Event listeners
    nextBtn.addEventListener('click', function() {
        stopAutoSlide();
        nextSlide();
        startAutoSlide();
    });
    
    prevBtn.addEventListener('click', function() {
        stopAutoSlide();
        prevSlide();
        startAutoSlide();
    });
    
    // Pause on hover
    track.addEventListener('mouseenter', stopAutoSlide);
    track.addEventListener('mouseleave', startAutoSlide);
    
    // Responsive adjustments
    window.addEventListener('resize', function() {
        const newSlideWidth = slides[0].offsetWidth + 20;
        if (newSlideWidth !== slideWidth) {
            slideWidth = newSlideWidth;
            moveToSlide(currentPosition);
        }
    });
});
</script>
  <!-- Upload CV Section - Card Style -->
  <div class="bg-light py-5">
    <div class="container">
      <div class="card shadow-sm p-4 mx-auto" style="max-width: 800px;">
        <h2 class="fw-bold text-primary mb-3">Upload Your CV</h2>
        <p class="text-muted mb-4">
          Let top employers find you. Upload your CV and get discovered by the best companies.
        </p>
        <a href="upload_cv.php" class="btn btn-outline-primary btn-lg w-100">
          Upload Your CV
        </a>
      </div>
    </div>
  </div>
  <!-- Post a Job Section - Modern Design -->
  <div class="bg-gradient py-5" style="background: linear-gradient(135deg, #e0eafc, #cfdef3);">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h2 class="fw-bold text-primary mb-3">Find the Perfect Candidate</h2>
          <p class="text-muted mb-4">
            Are you looking to hire top talent for your team? CareerConnect makes it easy to post job openings and connect with skilled professionals. Whether you're a startup or an established company, we help you find the right fit for your organization.
          </p>
          <p class="text-muted mb-4">
            Post your job today and reach thousands of qualified candidates actively searching for their next opportunity.
          </p>
          <a href="post_a_job.php" class="btn btn-outline-primary btn-lg">
            Post a Job Now
          </a>
        </div>
        <div class="col-md-6 text-center">
          <!-- Placeholder Image -->
          <img src="assets/images/searchcC.jpg" alt="Search Candidate Illustration" class="img-fluid">
        </div>
      </div>
    </div>
  </div>

  <?php include "templates/footer.php" ?>
  <script src="assets/JS/bootstrap.min.js"></script>
    <script src="assets/icons/all.min.js"></script>

  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.js"></script> -->
</body>

</html>