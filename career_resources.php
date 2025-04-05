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
    <title>Search for a Candidate - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <script src="assets/JS/jquery-3.7.1.js"></script>
    <style>
        /* Custom Styles */
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-section h5 {
            color: #3a0ca3;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .filter-section .form-label {
            font-weight: 500;
            color: #333;
        }

        .filter-section .form-control,
        .filter-section .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .filter-section .form-range {
            width: 100%;
        }

        .candidate-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
        }

        .candidate-card img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .candidate-card .btn-primary {
            background: #3a0ca3;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .candidate-card .btn-primary:hover {
            background: #480ca8;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Filter Section -->
            <div class="col-lg-4 mb-4">
                <div class="filter-section">
                    <h5>Filter Candidates</h5>
                    <form id="filterForm">
                        <!-- Keyword Search -->
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keywords</label>
                            <input type="text" class="form-control" id="keyword" placeholder="e.g., Python, React, Marketing">
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" placeholder="e.g., New York, Remote">
                        </div>

                        <!-- Experience Level -->
                        <div class="mb-3">
                            <label for="experience" class="form-label">Experience Level</label>
                            <select class="form-select" id="experience">
                                <option value="">Any</option>
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                            </select>
                        </div>

                        <!-- Salary Range -->
                        <div class="mb-3">
                            <label for="salary" class="form-label">Salary Range</label>
                            <input type="range" class="form-range" id="salary" min="0" max="200000" step="1000">
                            <div class="d-flex justify-content-between">
                                <span>0 DZ</span>
                                <span id="salaryValue">100000 DZ</span>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" placeholder="e.g., JavaScript, Data Analysis">
                        </div>

                        <!-- Education -->
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <select class="form-select" id="education">
                                <option value="">Any</option>
                                <option value="bachelor">Bachelor's Degree</option>
                                <option value="master">Master's Degree</option>
                                <option value="phd">PhD</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
            </div>

            <!-- Candidate List
                Candidate 2
            <div class="col-lg-8">
                <h2 class="mb-4">Candidates</h2>
                <div class="row" id="candidateList">
                    Candidate 1
                <div class="col-md-6 mb-4">
                    <div class="card candidate-card">
                        <div class="card-body text-center">
                            <img src="/assets/icons/recherche.png" alt="Candidate Image" class="mb-3">
                            <h5 class="card-title">John Doe</h5>
                            <p class="card-text">Software Engineer</p>
                            <p class="card-text"><small class="text-muted">San Francisco, CA</small></p>
                            <a href="profile.html" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                </div>

                Candidate 2
            <div class="col-md-6 mb-4">
                <div class="card candidate-card">
                    <div class="card-body text-center">
                        <img src="/assets/icons/poste-vacant.png" alt="Candidate Image" class="mb-3">
                        <h5 class="card-title">Jane Smith</h5>
                        <p class="card-text">Data Scientist</p>
                        <p class="card-text"><small class="text-muted">New York, NY</small></p>
                        <a href="profile.html" class="btn btn-primary">View Profile</a>
                    </div>
                </div>
            </div>

                Candidate 3
            <div class="col-md-6 mb-4">
                <div class="card candidate-card">
                    <div class="card-body text-center">
                        <img src="assets/icons/batiment.png" alt="Candidate Image" class="mb-3">
                        <h5 class="card-title">Alice Johnson</h5>
                        <p class="card-text">Product Manager</p>
                        <p class="card-text"><small class="text-muted">Chicago, IL</small></p>
                        <a href="profile.html" class="btn btn-primary">View Profile</a>
                    </div>
                </div>
            </div>
            -->
        </div>
    </div>
    </div>
    </div>

    <!-- Footer -->

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.min.js"></script>
    <script>
        $(function() {
            $('#salary').on('input', function() {
                $('#salaryValue').html(`${this.value} DZ`);
            });
        });
        /*
        // Update Salary Range Value
        const salaryRange = document.getElementById('salary');
        const salaryValue = document.getElementById('salaryValue');

        salaryRange.addEventListener('input', function() {
            salaryValue.textContent = `${this.value} DZ`;
        });

        // Filter Candidates (Example Functionality)
        const filterForm = document.getElementById('filterForm');
        const candidateList = document.getElementById('candidateList');

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            // Get filter values
            const keyword = document.getElementById('keyword').value.toLowerCase();
            const location = document.getElementById('location').value.toLowerCase();
            const experience = document.getElementById('experience').value;
            const salary = salaryRange.value;
            const skills = document.getElementById('skills').value.toLowerCase();
            const education = document.getElementById('education').value;

            // Filter candidates (example logic)
            const candidates = candidateList.querySelectorAll('.col-md-6');
            candidates.forEach(candidate => {
                const title = candidate.querySelector('.card-title').textContent.toLowerCase();
                const loc = candidate.querySelector('.card-text small').textContent.toLowerCase();
                const exp = candidate.querySelector('.card-text').textContent.toLowerCase();

                const matchKeyword = title.includes(keyword) || loc.includes(keyword);
                const matchLocation = loc.includes(location);
                const matchExperience = experience === '' || exp.includes(experience);
                const matchSalary = true; // Add salary logic if needed
                const matchSkills = true; // Add skills logic if needed
                const matchEducation = true; // Add education logic if needed

                if (matchKeyword && matchLocation && matchExperience && matchSalary && matchSkills && matchEducation) {
                    candidate.style.display = 'block';
                } else {
                    candidate.style.display = 'none';
                }
            });
        });
        */
    </script>
</body>

</html>