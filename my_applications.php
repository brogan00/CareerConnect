<?php
include "connexion/config.php";
session_start();

// Check if user is logged in and is a candidate
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all applications
$apps_stmt = $conn->prepare("SELECT a.*, j.title as job_title, c.name as company_name, j.status as job_status
                           FROM application a
                           JOIN job j ON a.job_id = j.id
                           JOIN recruiter r ON j.recruiter_id = r.id
                           JOIN company c ON r.company_id = c.id
                           WHERE a.user_id = ?
                           ORDER BY a.applied_at DESC");
$apps_stmt->bind_param("i", $user_id);
$apps_stmt->execute();
$applications = $apps_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Job Applications</h2>
            <a href="job_search.php" class="btn btn-primary">Find More Jobs</a>
        </div>
        
        <?php if ($applications->num_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Job Status</th>
                                    <th>My Status</th>
                                    <th>Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($app = $applications->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                                        <td><?= htmlspecialchars($app['company_name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $app['job_status'] == 'approved' ? 'success' : 
                                                ($app['job_status'] == 'pending' ? 'warning' : 'danger') 
                                            ?>">
                                                <?= ucfirst($app['job_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $app['status'] == 'pending' ? 'warning' : 
                                                ($app['status'] == 'approved' ? 'success' : 'danger') 
                                            ?>">
                                                <?= ucfirst($app['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= time_elapsed_string($app['applied_at']) ?></td>
                                        <td>
                                            <a href="job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-sm btn-outline-primary">View Job</a>
                                            <?php if ($app['status'] == 'pending'): ?>
                                                <a href="withdraw_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-danger">Withdraw</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <img src="assets/images/no-applications.svg" alt="No applications" style="max-width: 300px;" class="mb-4">
                <h4 class="mb-3">You haven't applied to any jobs yet</h4>
                <p class="text-muted">Start your job search now to find your dream job</p>
                <a href="job_search.php" class="btn btn-primary mt-3">Browse Jobs</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
</body>
</html>