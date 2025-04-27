<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Redirect if not logged in as candidate
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: connexion/login.php");
    exit();
}

$user_id = $_SESSION['user_email'];

// Get user applications with job details
$stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, j.type_contract, j.salary,
           c.name AS company_name, r.first_name AS recruiter_first, 
           r.last_name AS recruiter_last, r.email AS recruiter_email,
           a.status AS application_status
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unread notifications
$notif_stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notif_stmt->close();

// Mark notifications as read when viewing dashboard
$mark_read = $conn->prepare("
    UPDATE notifications SET is_read = 1 
    WHERE user_id = ? AND is_read = 0
");
$mark_read->bind_param("i", $user_id);
$mark_read->execute();
$mark_read->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            border: none;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-accepted {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .notification-item {
            border-left: 3px solid #0d6efd;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            background-color: #f8f9fa;
        }
        .job-card {
            transition: transform 0.2s;
        }
        .job-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">My Applications</h5>
                        <h1 class="display-4"><?= count($applications) ?></h1>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <span class="badge bg-success"><?= count(array_filter($applications, fn($app) => $app['application_status'] == 'accepted')) ?> Accepted</span>
                            <span class="badge bg-warning text-dark"><?= count(array_filter($applications, fn($app) => $app['application_status'] == 'pending')) ?> Pending</span>
                            <span class="badge bg-danger"><?= count(array_filter($applications, fn($app) => $app['application_status'] == 'rejected')) ?> Rejected</span>
                        </div>
                    </div>
                </div>
                
                <div class="card dashboard-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Recent Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="notification-item">
                                    <p class="mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                                    <small class="text-muted"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                            <a href="notifications.php" class="btn btn-sm btn-outline-primary w-100 mt-2">View All</a>
                        <?php else: ?>
                            <p class="text-muted">No new notifications</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card dashboard-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">My Applications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($applications) > 0): ?>
                            <div class="row">
                                <?php foreach ($applications as $app): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card job-card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($app['job_title']) ?></h5>
                                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($app['company_name']) ?></h6>
                                                
                                                <p class="card-text mt-3">
                                                    <span class="badge bg-primary"><?= htmlspecialchars($app['type_contract']) ?></span>
                                                    <span class="badge bg-success"><?= number_format($app['salary']) ?> DZ</span>
                                                </p>
                                                
                                                <?php 
                                                $badge_class = '';
                                                if ($app['application_status'] == 'accepted') $badge_class = 'badge-accepted';
                                                elseif ($app['application_status'] == 'rejected') $badge_class = 'badge-rejected';
                                                else $badge_class = 'badge-pending';
                                                ?>
                                                <p class="card-text">
                                                    Status: <span class="badge rounded-pill status-badge <?= $badge_class ?>">
                                                        <?= ucfirst($app['application_status']) ?>
                                                    </span>
                                                </p>
                                                
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Applied on <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                                    </small>
                                                </p>
                                                
                                                <?php if ($app['application_status'] == 'accepted'): ?>
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <p class="mb-1"><strong>Recruiter Contact:</strong></p>
                                                        <p class="mb-1">
                                                            <i class="fas fa-user-tie me-2"></i>
                                                            <?= htmlspecialchars($app['recruiter_first'] . ' ' . $app['recruiter_last']) ?>
                                                        </p>
                                                        <p class="mb-0">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            <a href="mailto:<?= htmlspecialchars($app['recruiter_email']) ?>">
                                                                <?= htmlspecialchars($app['recruiter_email']) ?>
                                                            </a>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't applied to any jobs yet.</p>
                            <a href="job_search.php" class="btn btn-primary">Browse Jobs</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
</body>
</html>