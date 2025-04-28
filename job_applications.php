<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['job_id'])) {
    header("Location: recruiter_dashboard.php");
    exit();
}

$job_id = intval($_GET['job_id']);
$recruiter_id = $_SESSION['user_id'];

// Verify the job belongs to this recruiter
$job_check = $conn->prepare("SELECT id FROM job WHERE id = ? AND recruiter_id = ?");
$job_check->bind_param("ii", $job_id, $recruiter_id);
$job_check->execute();
$job_check->store_result();

if ($job_check->num_rows == 0) {
    header("Location: recruiter_dashboard.php");
    exit();
}

// Get job details
$job_stmt = $conn->prepare("SELECT title FROM job WHERE id = ?");
$job_stmt->bind_param("i", $job_id);
$job_stmt->execute();
$job_result = $job_stmt->get_result();
$job = $job_result->fetch_assoc();

// Get applications
$apps_stmt = $conn->prepare("SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.cv 
                           FROM application a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.job_id = ?");
$apps_stmt->bind_param("i", $job_id);
$apps_stmt->execute();
$applications = $apps_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Applications for: <?= htmlspecialchars($job['title']) ?></h2>
            <a href="recruiter_dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        
        <?php if ($applications->num_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($app = $applications->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></td>
                                        <td>
                                            <div><?= htmlspecialchars($app['email']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($app['phone']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $app['status'] == 'pending' ? 'warning' : ($app['status'] == 'approved' ? 'success' : 'danger') ?>">
                                                <?= ucfirst($app['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= time_elapsed_string($app['applied_at']) ?></td>
                                        <td>
                                            <a href="view_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                            <a href="download_cv.php?file=<?= urlencode($app['cv']) ?>" class="btn btn-sm btn-secondary">Download CV</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No applications for this job yet.
            </div>
        <?php endif; ?>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
</body>
</html>