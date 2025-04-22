<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}

$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get application details
$stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, j.type_contract, j.salary,
           c.name AS company_name, c.location AS company_location
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $application_id, $_SESSION['user_id']);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$application) {
    header("Location: job_search.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <style>
        .success-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
        }
        .application-card {
            border-left: 4px solid #28a745;
            border-radius: 8px;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #28a745;
            border: 3px solid #fff;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="success-container text-center">
            <div class="success-icon mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="mb-3">Application Submitted!</h1>
            <p class="lead mb-5">Your application has been successfully sent to the recruiter.</p>
            
            <div class="card application-card mb-5 text-start">
                <div class="card-body">
                    <h4 class="card-title"><?= htmlspecialchars($application['job_title']) ?></h4>
                    <p class="card-text text-muted"><?= htmlspecialchars($application['company_name']) ?> • <?= htmlspecialchars($application['company_location']) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong>Application ID:</strong> #<?= $application['id'] ?></p>
                            <p><strong>Applied on:</strong> <?= date('F j, Y', strtotime($application['applied_at'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Contract Type:</strong> <?= htmlspecialchars($application['type_contract']) ?></p>
                            <p><strong>Salary:</strong> <?= number_format($application['salary']) ?> DZ</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 text-start">
                <div class="card-header bg-light">
                    <h5 class="mb-0">What Happens Next?</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <h6>Application Received</h6>
                            <p class="text-muted">The recruiter has received your application.</p>
                        </div>
                        <div class="timeline-item">
                            <h6>Application Review</h6>
                            <p class="text-muted">The recruiter will review your qualifications.</p>
                        </div>
                        <div class="timeline-item">
                            <h6>Interview Stage</h6>
                            <p class="text-muted">If selected, you'll be contacted for an interview.</p>
                        </div>
                        <div class="timeline-item">
                            <h6>Final Decision</h6>
                            <p class="text-muted">You'll be notified of the final hiring decision.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-3 d-md-block">
                <a href="job_search.php" class="btn btn-primary px-4">
                    <i class="fas fa-briefcase me-2"></i> Browse More Jobs
                </a>
                <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
</body>
</html>