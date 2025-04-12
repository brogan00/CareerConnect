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

  <!-- Featured Jobs Section (Modernized) -->
  <div class="container mt-5">
    <h2 class="text-center mb-4">Featured Jobs</h2>
    <div class="row">
      <!-- Job 1 -->
      <div class="col-md-4 mb-4">
        <div class="card job-card animate__animated animate__fadeInUp">
          <div class="card-body">
            <h5 class="card-title">Software Engineer</h5>
            <p class="card-text">Company: TechCorp</p>
            <p class="card-text">Location: San Francisco, CA</p>
            <p class="card-text">Salary: $90,000 - $120,000</p>
            <a href="#" class="btn btn-primary">Apply Now</a>
          </div>
        </div>
      </div>

      <!-- Job 2 -->
      <div class="col-md-4 mb-4">
        <div class="card job-card animate__animated animate__fadeInUp animate__delay-1s">
          <div class="card-body">
            <h5 class="card-title">Data Scientist</h5>
            <p class="card-text">Company: DataWorks</p>
            <p class="card-text">Location: New York, NY</p>
            <p class="card-text">Salary: $80,000 - $110,000</p>
            <a href="#" class="btn btn-primary">Apply Now</a>
          </div>
        </div>
      </div>

      <!-- Job 3 -->
      <div class="col-md-4 mb-4">
        <div class="card job-card animate__animated animate__fadeInUp animate__delay-2s">
          <div class="card-body">
            <h5 class="card-title">Product Manager</h5>
            <p class="card-text">Company: Innovate Inc.</p>
            <p class="card-text">Location: Chicago, IL</p>
            <p class="card-text">Salary: $100,000 - $130,000</p>
            <a href="#" class="btn btn-primary">Apply Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>

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