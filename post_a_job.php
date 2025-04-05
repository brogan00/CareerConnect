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
    <title>Post a Job - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
</head>

<body>
    <!-- Navbar -->

    <?php include "templates/header.php" ?>

    <!-- Post a Job Form -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Post a Job</h2>
        <form>
            <!-- Company Information -->
            <div class="mb-3">
                <label for="company-name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company-name" placeholder="Enter your company name">
            </div>
            <div class="mb-3">
                <label for="company-industry" class="form-label">Industry</label>
                <input type="text" class="form-control" id="company-industry" placeholder="e.g., Technology, Healthcare, Finance">
            </div>
            <div class="mb-3">
                <label for="company-description" class="form-label">Company Description</label>
                <textarea class="form-control" id="company-description" rows="3" placeholder="Brief description about your company"></textarea>
            </div>
            <div class="mb-3">
                <label for="company-website" class="form-label">Company Website</label>
                <input type="url" class="form-control" id="company-website" placeholder="https://www.example.com">
            </div>
            <div class="mb-3">
                <label for="contact-email" class="form-label">Contact Email</label>
                <input type="email" class="form-control" id="contact-email" placeholder="contact@example.com">
            </div>
            <div class="mb-3">
                <label for="contact-phone" class="form-label">Contact Phone</label>
                <input type="tel" class="form-control" id="contact-phone" placeholder="+213 XXX XXX XXX">
            </div>

            <!-- Job Information -->
            <div class="mb-3">
                <label for="job-title" class="form-label">Job Title</label>
                <input type="text" class="form-control" id="job-title" placeholder="e.g., Web Developer">
            </div>
            <div class="mb-3">
                <label for="job-location" class="form-label">Job Location</label>
                <div class="row">
                    <div class="col">
                        <select class="form-select" id="country">
                            <option selected>Algeria</option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" id="city" placeholder="Commune (City)">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" id="state" placeholder="Wilaya (State)">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="contract-type" class="form-label">Contract Type</label>
                <select class="form-select" id="contract-type">
                    <option selected>Any</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="working-hours" class="form-label">Working Hours</label>
                <select class="form-select" id="working-hours">
                    <option selected>Any</option>
                    <option value="9-5">9 AM - 5 PM</option>
                    <option value="flexible">Flexible</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <div class="row">
                    <div class="col">
                        <input type="text" class="form-control" id="salary-amount" placeholder="Amount">
                    </div>
                    <div class="col">
                        <select class="form-select" id="currency">
                            <option selected>DZD (Algerian Dinar)</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" id="time-period">
                            <option selected>per year</option>
                            <option value="month">per month</option>
                            <option value="hour">per hour</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="job-description" class="form-label">Job Offer Description</label>
                <textarea class="form-control" id="job-description" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Job</button>
        </form>
    </div>

    <?php include "templates/footer.php" ?>

    <script>
        function getLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        let lat = position.coords.latitude;
                        let lon = position.coords.longitude;

                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                if (data.address && data.address.country === "Algeria") {
                                    let wilaya = data.address.state || "Unknown Wilaya";
                                    let commune = data.address.city || data.address.town || data.address.village || "Unknown Commune";

                                    document.getElementById("city").value = commune;
                                    document.getElementById("state").value = wilaya;
                                } else {
                                    document.getElementById("city").value = "Location not in Algeria";
                                    document.getElementById("state").value = "Unknown";
                                }
                            })
                            .catch(error => console.error("Error fetching location:", error));
                    },
                    function(error) {
                        console.error("Geolocation error:", error);
                    }
                );
            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        }

        window.onload = getLocation;
    </script>
</body>

</html>