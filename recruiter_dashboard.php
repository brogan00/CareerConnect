<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Check if user is a recruiter
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: index.php");
    exit();
}

$recruiter_id = $_SESSION['user_email'];

// Get recruiter's jobs
$jobs_stmt = $conn->prepare("SELECT j.*, COUNT(a.id) as applications 
                           FROM job j 
                           LEFT JOIN application a ON j.id = a.job_id 
                           WHERE j.recruiter_id = ? 
                           GROUP BY j.id");
$jobs_stmt->bind_param("i", $recruiter_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

// Get notifications
$notif_stmt = $conn->prepare("SELECT * FROM notifications 
                             WHERE recruiter_id = ? 
                             ORDER BY created_at DESC 
                             LIMIT 5");
$notif_stmt->bind_param("i", $recruiter_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Dashboard</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <style>
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
        }
        .notification-item.unread {
            background-color: #f8f9fa;
        }
        .job-card {
            transition: transform 0.2s;
        }
        .job-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'assets/images/default-profile.jpg') ?>" 
                             class="rounded-circle mb-3" width="100" height="100">
                        <h5><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></h5>
                        <p class="text-muted">Recruiter</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Quick Actions
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="post_job.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus me-2"></i> Post New Job
                        </a>
                        <a href="my_jobs.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-briefcase me-2"></i> My Jobs
                        </a>
                        <a href="applications.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2"></i> Applications
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary h-100">
                            <div class="card-body">
                                <h5 class="card-title">Active Jobs</h5>
                                <h2 class="mb-0"><?= $jobs_result->num_rows ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total Applications</h5>
                                <?php
                                $total_apps = 0;
                                while ($job = $jobs_result->fetch_assoc()) {
                                    $total_apps += $job['applications'];
                                }
                                $jobs_result->data_seek(0); // Reset pointer
                                ?>
                                <h2 class="mb-0"><?= $total_apps ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info h-100">
                            <div class="card-body">
                                <h5 class="card-title">New Notifications</h5>
                                <?php
                                $new_notifs = $conn->query("SELECT COUNT(*) as count FROM notifications 
                                                           WHERE recruiter_id = $recruiter_id AND is_read = 0")->fetch_assoc()['count'];
                                ?>
                                <h2 class="mb-0"><?= $new_notifs ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Notifications</h5>
                        <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($notifications->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($notif = $notifications->fetch_assoc()): ?>
                                    <a href="job_applications.php?job_id=<?= $notif['related_id'] ?>" 
                                       class="list-group-item list-group-item-action <?= $notif['is_read'] ? '' : 'unread' ?>">
                                        <div class="d-flex justify-content-between">
                                            <p class="mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                                            <small class="text-muted"><?= time_elapsed_string($notif['created_at']) ?></small>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="p-3 mb-0 text-muted">No notifications yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Jobs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Recent Jobs</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($jobs_result->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card job-card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
                                                <p class="card-text text-truncate"><?= htmlspecialchars($job['mission']) ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-<?= $job['status'] == 'approved' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($job['status']) ?>
                                                    </span>
                                                    <a href="job_applications.php?job_id=<?= $job['id'] ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        View Applications (<?= $job['applications'] ?>)
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't posted any jobs yet.</p>
                            <a href="post_job.php" class="btn btn-primary">Post Your First Job</a>
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