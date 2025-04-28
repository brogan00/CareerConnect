<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: index.php");
    exit();
}

$application_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get application details
$stmt = $conn->prepare("SELECT a.*, j.title as job_title, j.mission as job_description, 
                       j.type_contract, j.salary, c.name as company_name, c.location as company_location,
                       r.first_name as recruiter_first, r.last_name as recruiter_last
                       FROM application a
                       JOIN job j ON a.job_id = j.id
                       JOIN recruiter r ON j.recruiter_id = r.id
                       JOIN company c ON r.company_id = c.id
                       WHERE a.id = ? AND a.user_id = ?");
$stmt->bind_param("ii", $application_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_applications.php");
    exit();
}

$application = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Application Status</h2>
            <a href="my_applications.php" class="btn btn-outline-secondary">Back to Applications</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Job Details</h5>
            </div>
            <div class="card-body">
                <h4><?= htmlspecialchars($application['job_title']) ?></h4>
                <p class="text-muted mb-3">
                    <i class="fas fa-building me-2"></i><?= htmlspecialchars($application['company_name']) ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($application['company_location']) ?>
                </p>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Contract Type</small>
                            <strong><?= htmlspecialchars($application['type_contract']) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Salary</small>
                            <strong><?= number_format($application['salary']) ?> DZD</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Application Status</small>
                            <span class="badge bg-<?= 
                                $application['status'] == 'pending' ? 'warning' : 
                                ($application['status'] == 'approved' ? 'success' : 'danger') 
                            ?>">
                                <?= ucfirst($application['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <h5>Job Description</h5>
                <p><?= nl2br(htmlspecialchars($application['job_description'])) ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Application Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-point"></div>
                        <div class="timeline-content">
                            <h6>Application Submitted</h6>
                            <p class="text-muted"><?= time_elapsed_string($application['applied_at']) ?></p>
                        </div>
                    </div>
                    
                    <?php if ($application['status'] != 'pending'): ?>
                        <div class="timeline-item">
                            <div class="timeline-point"></div>
                            <div class="timeline-content">
                                <h6>Application <?= ucfirst($application['status']) ?></h6>
                                <p class="text-muted"><?= time_elapsed_string($application['updated_at']) ?></p>
                                <?php if (!empty($application['feedback'])): ?>
                                    <div class="alert alert-info mt-2">
                                        <strong>Feedback:</strong> <?= htmlspecialchars($application['feedback']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($application['status'] == 'pending'): ?>
                    <div class="alert alert-warning mt-3">
                        Your application is under review. The recruiter will contact you if you're selected.
                    </div>
                    <a href="withdraw_application.php?id=<?= $application['id'] ?>" class="btn btn-outline-danger">Withdraw Application</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-point {
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #0d6efd;
            border: 3px solid white;
        }
        .timeline-content {
            padding-left: 15px;
        }
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -22px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }
    </style>
</body>
</html>