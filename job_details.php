<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Handle job application
if (isset($_POST['apply_job']) && isset($_SESSION['user_email']) && $_SESSION['user_type'] === 'candidat') {
    $job_id = intval($_POST['job_id']);
    $user_id = $_SESSION['user_id'];
    
    // Get recruiter_id for this job
    $recruiter_stmt = $conn->prepare("SELECT recruiter_id FROM job WHERE id = ?");
    $recruiter_stmt->bind_param("i", $job_id);
    $recruiter_stmt->execute();
    $recruiter_result = $recruiter_stmt->get_result();
    
    if ($recruiter_result->num_rows > 0) {
        $job = $recruiter_result->fetch_assoc();
        $recruiter_id = $job['recruiter_id'];
        
        // Check if already applied
        $check_stmt = $conn->prepare("SELECT id FROM application WHERE user_id = ? AND job_id = ?");
        $check_stmt->bind_param("ii", $user_id, $job_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            // Insert application
            $insert_stmt = $conn->prepare("INSERT INTO application (user_id, job_id, status) VALUES (?, ?, 'pending')");
            $insert_stmt->bind_param("ii", $user_id, $job_id);
            
            if ($insert_stmt->execute()) {
                // Create notification
                $message = "New application from " . $_SESSION['first_name'] . " " . $_SESSION['last_name'];
                $notif_stmt = $conn->prepare("INSERT INTO notifications (recruiter_id, message, type, related_id) VALUES (?, ?, 'application', ?)");
                $notif_stmt->bind_param("isi", $recruiter_id, $message, $insert_stmt->insert_id);
                $notif_stmt->execute();
                
                $_SESSION['success_message'] = "Application submitted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to submit application";
            }
        } else {
            $_SESSION['error_message'] = "You've already applied to this job";
        }
    }
    
    // Redirect back to avoid form resubmission
    header("Location: job_details.php?id=" . $job_id);
    exit();
}

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get job details with company info
$stmt = $conn->prepare("
    SELECT j.*, c.name AS company_name, c.location AS company_location, 
           c.website AS company_website, c.description AS company_description,
           r.first_name AS recruiter_name, r.last_name AS recruiter_last_name, r.id AS recruiter_id, r.email AS recruiter_email
    FROM job j
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE j.id = ? AND j.status = 'approved'
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$job) {
    header("Location: job_search.php");
    exit();
}

// Check application status if user is candidate
$application_status = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'candidat') {
    $status_stmt = $conn->prepare("SELECT status FROM application WHERE user_id = ? AND job_id = ?");
    $status_stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    if ($status_result->num_rows > 0) {
        $application_status = $status_result->fetch_assoc()['status'];
    }
    $status_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <style>
        .job-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 3rem 0;
            border-radius: 10px;
            margin-bottom: 3rem;
        }
        .job-highlights {
            background-color: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .highlight-item {
            margin-bottom: 1.5rem;
        }
        .highlight-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: #0d6efd;
        }
        .company-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .company-logo {
            height: 120px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .job-description {
            line-height: 1.8;
        }
        .apply-btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="job-header text-center">
            <div class="container">
                <h1 class="display-5 mb-3"><?= htmlspecialchars($job['title']) ?></h1>
                <h3 class="text-muted mb-4"><?= htmlspecialchars($job['company_name']) ?></h3>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <span class="badge bg-primary px-3 py-2"><?= htmlspecialchars($job['type_contract']) ?></span>
                    <span class="badge bg-secondary px-3 py-2"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($job['company_location']) ?></span>
                    <span class="badge bg-success px-3 py-2"><i class="fas fa-money-bill-wave me-1"></i> <?= number_format($job['salary']) ?> DZ</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="job-highlights">
                    <h4 class="mb-4">Job Highlights</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Job Type</h6>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($job['type_contract']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Salary</h6>
                                    <p class="text-muted mb-0"><?= number_format($job['salary']) ?> DZ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Location</h6>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($job['company_location']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Recruiter</h6>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($job['recruiter_name'] . ' ' . $job['recruiter_last_name']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-4">Job Description</h4>
                        <div class="job-description">
                            <?= nl2br(htmlspecialchars($job['mission'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card company-card mb-4">
                    <div class="company-logo">
                        <i class="fas fa-building fa-3x text-muted"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($job['company_name']) ?></h5>
                        <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($job['company_location']) ?></p>
                        <?php if (!empty($job['company_website'])): ?>
                            <p><i class="fas fa-globe me-2"></i> <a href="<?= htmlspecialchars($job['company_website']) ?>" target="_blank">Company Website</a></p>
                        <?php endif; ?>
                        <?php if (!empty($job['company_description'])): ?>
                            <p class="card-text"><?= nl2br(htmlspecialchars($job['company_description'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_email']) && $_SESSION['user_type'] === 'candidat'): ?>
                        <?php if ($application_status): ?>
                            <?php if ($application_status == 'pending'): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-clock me-2"></i> Your application is pending review
                                </div>
                            <?php elseif ($application_status == 'accepted'): ?>
                                <div class="alert alert-success text-center">
                                    <i class="fas fa-check-circle me-2"></i> Your application has been accepted!
                                    <p class="mt-2 mb-0">Contact the recruiter at: <a href="mailto:<?= htmlspecialchars($job['recruiter_email']) ?>"><?= htmlspecialchars($job['recruiter_email']) ?></a></p>
                                </div>
                            <?php elseif ($application_status == 'rejected'): ?>
                                <div class="alert alert-danger text-center">
                                    <i class="fas fa-times-circle me-2"></i> Your application was not accepted
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="job_id" value="<?= $job_id ?>">
                                <button type="submit" name="apply_job" class="btn btn-primary apply-btn w-100">
                                    <i class="fas fa-paper-plane me-2"></i> Apply Now
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php elseif (!isset($_SESSION['user_email'])): ?>
                        <a href="connexion/login.php" class="btn btn-primary apply-btn">
                            <i class="fas fa-sign-in-alt me-2"></i> Login to Apply
                        </a>
                    <?php endif; ?>
                    <a href="job_search.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
</body>
</html>