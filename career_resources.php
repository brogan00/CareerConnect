<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in as recruiter
if (!isset($_SESSION['user_email'])){
    header("Location: login.php");
    exit();
}

// Get filter parameters
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$experience = isset($_GET['experience']) ? $_GET['experience'] : '';
$skills = isset($_GET['skills']) ? $_GET['skills'] : '';
$education = isset($_GET['education']) ? $_GET['education'] : '';

// Build SQL query with filters
$query = "SELECT u.id, u.first_name, u.last_name, u.address, u.profile_picture, 
                 GROUP_CONCAT(DISTINCT e.speciality) AS specialities,
                 GROUP_CONCAT(DISTINCT s.content) AS skills_list
          FROM users u
          LEFT JOIN education e ON u.id = e.user_id
          LEFT JOIN skills s ON u.id = s.user_id
          WHERE u.type = 'candidat' AND u.status = 'active' AND u.cv IS NOT NULL";

$conditions = [];
$params = [];

if (!empty($keyword)) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR e.speciality LIKE ? OR s.content LIKE ?)";
    $params = array_merge($params, ["%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"]);
}

if (!empty($location)) {
    $conditions[] = "u.address LIKE ?";
    $params[] = "%$location%";
}

if (!empty($experience)) {
    // This would need to be adjusted based on how you store experience in your database
    $conditions[] = "e.level IN (?)";
    $params[] = $experience == 'senior' ? 'Master,Doctorat,Ingeniorat' : ($experience == 'mid' ? 'Licence,Master' : 'BAC');
}

if (!empty($education)) {
    $conditions[] = "e.level = ?";
    $params[] = $education == 'bachelor' ? 'Licence' : ($education == 'master' ? 'Master' : 'Doctorat');
}

if (!empty($skills)) {
    $skillsArray = explode(',', $skills);
    $skillConditions = [];
    foreach ($skillsArray as $skill) {
        $skillConditions[] = "s.content LIKE ?";
        $params[] = "%$skill%";
    }
    $conditions[] = "(" . implode(' OR ', $skillConditions) . ")";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

$query .= " GROUP BY u.id";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $candidates = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $candidates = [];
}
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

        .candidate-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
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

        .skill-badge {
            background-color: #e0e0e0;
            color: #333;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
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
                    <form id="filterForm" method="GET" action="">
                        <!-- Keyword Search -->
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keywords</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" 
                                   value="<?= htmlspecialchars($keyword) ?>" placeholder="e.g., Python, React, Marketing">
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($location) ?>" placeholder="e.g., New York, Remote">
                        </div>

                        <!-- Experience Level -->
                        <div class="mb-3">
                            <label for="experience" class="form-label">Experience Level</label>
                            <select class="form-select" id="experience" name="experience">
                                <option value="">Any</option>
                                <option value="entry" <?= $experience == 'entry' ? 'selected' : '' ?>>Entry Level</option>
                                <option value="mid" <?= $experience == 'mid' ? 'selected' : '' ?>>Mid Level</option>
                                <option value="senior" <?= $experience == 'senior' ? 'selected' : '' ?>>Senior Level</option>
                            </select>
                        </div>

                        <!-- Skills -->
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills (comma separated)</label>
                            <input type="text" class="form-control" id="skills" name="skills" 
                                   value="<?= htmlspecialchars($skills) ?>" placeholder="e.g., JavaScript, Data Analysis">
                        </div>

                        <!-- Education -->
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <select class="form-select" id="education" name="education">
                                <option value="">Any</option>
                                <option value="bachelor" <?= $education == 'bachelor' ? 'selected' : '' ?>>Bachelor's Degree</option>
                                <option value="master" <?= $education == 'master' ? 'selected' : '' ?>>Master's Degree</option>
                                <option value="phd" <?= $education == 'phd' ? 'selected' : '' ?>>PhD</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <?php if (!empty($_GET)): ?>
                            <a href="?" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Candidate List -->
            <div class="col-lg-8">
                <h2 class="mb-4">Candidates</h2>
                <?php if (empty($candidates)): ?>
                    <div class="alert alert-info">No candidates found matching your criteria.</div>
                <?php else: ?>
                    <div class="row" id="candidateList">
                        <?php foreach ($candidates as $candidate): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card candidate-card">
                                    <div class="card-body text-center">
                                        <img src="<?= !empty($candidate['profile_picture']) ? htmlspecialchars($candidate['profile_picture']) : 'assets/icons/default-profile.png' ?>" 
                                             alt="Candidate Image" class="mb-3">
                                        <h5 class="card-title"><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h5>
                                        <p class="card-text">
                                            <?= !empty($candidate['specialities']) ? htmlspecialchars(explode(',', $candidate['specialities'])[0]) : 'No specialty specified' ?>
                                        </p>
                                        <p class="card-text"><small class="text-muted"><?= htmlspecialchars($candidate['address']) ?></small></p>
                                        
                                        <?php if (!empty($candidate['skills_list'])): ?>
                                            <div class="mb-3">
                                                <?php 
                                                $skills = explode(',', $candidate['skills_list']);
                                                foreach (array_slice($skills, 0, 3) as $skill): ?>
                                                    <span class="skill-badge"><?= htmlspecialchars(trim($skill)) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($skills) > 3): ?>
                                                    <span class="skill-badge">+<?= count($skills) - 3 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="candidate_profile.php?id=<?= $candidate['id'] ?>" class="btn btn-primary">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>