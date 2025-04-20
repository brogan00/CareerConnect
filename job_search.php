<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

$stmt = $conn->prepare("SELECT * FROM job WHERE status = 'active'");
$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Job Search - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/icons/all.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
</head>

<body>

    <?php include "templates/header.php" ?>

    <!-- Job Search Content -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Job Search</h2>
        <div class="row">
            <!-- Filters -->
            <div class="col-md-3">
                <div class="card p-3">
                    <h5 class="mb-3">Filters</h5>
                    <div class="mb-3">
                        <label for="job-name" class="form-label">Job Name</label>
                        <input type="text" class="form-control" id="job-name" placeholder="Job title, keywords, company"
                            value="<?php
                                    if (isset($_POST['keywords'])) echo $_POST['keywords']
                                    ?>">
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" placeholder="City or State"
                            value="<?php
                                    if (isset($_POST['Location'])) echo $_POST['Location']
                                    ?>" />
                    </div>
                    <div class="mb-3">
                        <label for="job-type" class="form-label">Job Type</label>
                        <select class="form-select" id="job-type">
                            <option>Full-Time</option>
                            <option>Part-Time</option>
                            <option>Remote</option>
                        </select>
                    </div>
                    <div class="mb-3">
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
                </div>
                <button class="btn btn-primary w-100">Apply Filters</button>
            </div>

            <!-- Job Listings -->

            <div id="jobs_list" class="col-md-9">
                <?php
                while ($row = $jobs->fetch_assoc()) {
                ?> <form action="treat.php" method="post">
                        <input hidden name="job_id" value="<?php echo $row['id'] ?>">
                        <input hidden name="person_id" value="<?php echo "12" ?>">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><?php echo $row['title'] ?></h5>
                                <p class="card-text">Company : <?php echo $row['company'] ?></p>
                                <p class="card-text">Location : <?php echo $row['location'] ?></p>
                                <p class="card-text">Salary : <?php echo $row['salary'] ?> DZ</p>
                                <button type="submit" class="btn btn-primary">Apply Now</button>
                            </div>
                        </div>
                    </form>
                <?php } ?>
            </div>
        

        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
    <script>
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
    </script>
</body>

</html>