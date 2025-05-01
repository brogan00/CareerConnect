<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Redirect if not logged in as candidate
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: connexion/login.php");
    exit();
}

// Get candidate applications
$stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, c.name AS company_name, a.status AS application_status
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .application-status {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="candidate_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="edit_profile.php"><i class="fas fa-user-edit me-2"></i> Edit Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="my_applications.php"><i class="fas fa-file-alt me-2"></i> My Applications</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="connexion/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">My Job Applications</h4>
                            <a href="job_search.php" class="btn btn-primary btn-sm">Browse Jobs</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($applications->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Company</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($app = $applications->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                                <td><?= htmlspecialchars($app['company_name']) ?></td>
                                                <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $app['application_status'] === 'accepted' ? 'bg-success' : 
                                                           ($app['application_status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                        <?= ucfirst($app['application_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-sm btn-outline-primary">View Job</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <img src="assets/images/no-applications.svg" alt="No applications" style="max-width: 300px;" class="mb-4">
                                <h4 class="mb-3">You haven't applied to any jobs yet</h4>
                                <p class="text-muted">Browse available jobs and apply to get started</p>
                                <a href="job_search.php" class="btn btn-primary">Browse Jobs</a>
                            </div>
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