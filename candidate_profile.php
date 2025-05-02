<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Get candidate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: candidate_search.php");
    exit();
}

$candidate_id = intval($_GET['id']);

// Get candidate basic info
$query = "SELECT * FROM users WHERE id = ? AND type = 'candidat'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    header("Location: candidate_search.php");
    exit();
}

// Get candidate education
$education = [];
$query = "SELECT * FROM education WHERE user_id = ? ORDER BY end_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $education[] = $row;
}
$stmt->close();

// Get candidate experience
$experience = [];
$query = "SELECT * FROM experience WHERE user_id = ? ORDER BY end_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $experience[] = $row;
}
$stmt->close();

// Get candidate skills
$skills = [];
$query = "SELECT * FROM skills WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $skills[] = $row['content'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?> - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <style>
        .profile-header {
            background: linear-gradient(135deg, #3a0ca3 0%, #480ca8 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .section-title {
            color: #3a0ca3;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .skill-badge {
            background-color: #e0e0e0;
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 25px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #3a0ca3;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 25px;
            width: 1px;
            height: calc(100% - 20px);
            background-color: #ddd;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .btn-primary {
            background: #3a0ca3;
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
        }

        .btn-primary:hover {
            background: #480ca8;
        }

        .btn-outline-primary {
            color: #3a0ca3;
            border-color: #3a0ca3;
            border-radius: 8px;
            padding: 10px 25px;
        }

        .btn-outline-primary:hover {
            background: #3a0ca3;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Profile Header -->
        <div class="profile-header text-center">
            <img src="<?= !empty($candidate['profile_picture']) ? htmlspecialchars($candidate['profile_picture']) : 'assets/icons/default-profile.png' ?>" 
                 alt="Profile Picture" class="profile-picture mb-3">
            <h1><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h1>
            <p class="lead mb-4">
                <?= !empty($education[0]['speciality']) ? htmlspecialchars($education[0]['speciality']) : 'No specialty specified' ?>
            </p>
            <p class="mb-4">
                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($candidate['address']) ?> | 
                <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($candidate['email']) ?> | 
                <i class="bi bi-phone-fill"></i> <?= htmlspecialchars($candidate['phone']) ?>
            </p>
            <div>
                <a href="<?= htmlspecialchars($candidate['cv']) ?>" class="btn btn-primary" download>
                    <i class="bi bi-download"></i> Download CV
                </a>
                <a href="candidate_search.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Search
                </a>
            </div>
        </div>

        <!-- About Section -->
        <div class="section-card">
            <h2 class="section-title">About</h2>
            <p><?= !empty($candidate['about']) ? nl2br(htmlspecialchars($candidate['about'])) : 'No information provided.' ?></p>
        </div>

        <!-- Skills Section -->
        <?php if (!empty($skills)): ?>
            <div class="section-card">
                <h2 class="section-title">Skills</h2>
                <div>
                    <?php foreach ($skills as $skill): ?>
                        <span class="skill-badge"><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Experience Section -->
        <?php if (!empty($experience)): ?>
            <div class="section-card">
                <h2 class="section-title">Work Experience</h2>
                <div class="timeline">
                    <?php foreach ($experience as $exp): ?>
                        <div class="timeline-item">
                            <h4><?= htmlspecialchars($exp['job_name']) ?></h4>
                            <h5><?= htmlspecialchars($exp['company_name']) ?></h5>
                            <p class="text-muted">
                                <?= date('M Y', strtotime($exp['start_date'])) ?> - 
                                <?= !empty($exp['end_date']) ? date('M Y', strtotime($exp['end_date'])) : 'Present' ?>
                            </p>
                            <?php if (!empty($exp['description'])): ?>
                                <p><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Education Section -->
        <?php if (!empty($education)): ?>
            <div class="section-card">
                <h2 class="section-title">Education</h2>
                <div class="timeline">
                    <?php foreach ($education as $edu): ?>
                        <div class="timeline-item">
                            <h4><?= htmlspecialchars($edu['level']) ?> in <?= htmlspecialchars($edu['speciality']) ?></h4>
                            <h5><?= htmlspecialchars($edu['univ_name']) ?></h5>
                            <p class="text-muted">
                                <?= date('M Y', strtotime($edu['start_date'])) ?> - 
                                <?= !empty($edu['end_date']) ? date('M Y', strtotime($edu['end_date'])) : 'Present' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Contact Button -->
        <div class="text-center mt-4">
            <a href="mailto:<?= htmlspecialchars($candidate['email']) ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-envelope-fill"></i> Contact Candidate
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.min.js"></script>
    <script src="assets/JS/jquery-3.7.1.js"></script>
</body>
</html>