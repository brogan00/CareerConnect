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
    <title>Company Search - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <div class="container mt-5">
        <h2 class="text-center">Find Companies</h2>
        <div class="container search-container mt-5 w-75">
            <form action="" method="POST">
                <div class="row align-items-center justify-content-center">
                    <div class="col-11 col-md-8 col-lg-9 mb-2 mb-md-0">
                        <img src="assets/icons/batiment.png" class="col-1" alt="Company Icon">
                        <input class="col-10" type="text" placeholder="Search for companies...">
                    </div>
                    <button type="submit" class="btn search-button col-11 col-md-4 col-lg-3 mt-md-2 mt-sm-2">Search</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Example Companies -->
    <div class="container mt-5">
        <h3 class="text-center mb-4">Featured Companies</h3>
        <div class="row">
            <!-- Company 1 -->
            <div class="col-md-4 mb-4">
                <div class="card company-card">
                    <div class="card-body text-center">
                        <img src="https://via.placeholder.com/100" alt="TechCorp Logo" class="mb-3">
                        <h5 class="card-title">TechCorp</h5>
                        <p class="card-text">Industry: Technology</p>
                        <p class="card-text">Location: San Francisco, CA</p>
                        <a href="#" class="btn btn-primary">View Jobs</a>
                    </div>
                </div>
            </div>

            <!-- Company 2 -->
            <div class="col-md-4 mb-4">
                <div class="card company-card">
                    <div class="card-body text-center">
                        <img src="https://via.placeholder.com/100" alt="DataWorks Logo" class="mb-3">
                        <h5 class="card-title">DataWorks</h5>
                        <p class="card-text">Industry: Data Analytics</p>
                        <p class="card-text">Location: New York, NY</p>
                        <a href="#" class="btn btn-primary">View Jobs</a>
                    </div>
                </div>
            </div>

            <!-- Company 3 -->
            <div class="col-md-4 mb-4">
                <div class="card company-card">
                    <div class="card-body text-center">
                        <img src="https://via.placeholder.com/100" alt="GreenEnergy Logo" class="mb-3">
                        <h5 class="card-title">GreenEnergy</h5>
                        <p class="card-text">Industry: Renewable Energy</p>
                        <p class="card-text">Location: Austin, TX</p>
                        <a href="#" class="btn btn-primary">View Jobs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>
</body>

</html>