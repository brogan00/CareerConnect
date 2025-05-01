<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Redirect if not logged in as recruiter
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: connexion/login.php");
    exit();
}

// Get applications for recruiter's jobs with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

$sql = "
    SELECT a.*, j.title AS job_title, u.first_name, u.last_name, u.email, a.status AS application_status
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.recruiter_id = ?
";

$params = [$_SESSION['user_id']];
$types = 'i';

if ($status_filter !== 'all') {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($job_filter > 0) {
    $sql .= " AND j.id = ?";
    $params[] = $job_filter;
    $types .= 'i';
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$applications = $stmt->get_result();

// Get recruiter's jobs for filter dropdown
$jobs_stmt = $conn->prepare("SELECT id, title FROM job WHERE recruiter_id = ? ORDER BY title");
$jobs_stmt->bind_param("i", $_SESSION['user_id']);
$jobs_stmt->execute();
$jobs = $jobs_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - CareerConnect</title>
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
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
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
                                <a class="nav-link" href="recruiter_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="edit_recruiter_profile.php"><i class="fas fa-user-edit me-2"></i> Edit Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="post_job.php"><i class="fas fa-plus-circle me-2"></i> Post New Job</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manage_jobs.php"><i class="fas fa-briefcase me-2"></i> Manage Jobs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="manage_applications.php"><i class="fas fa-file-alt me-2"></i> Applications</a>
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
                            <h4 class="mb-0">Manage Applications</h4>
                            <a href="recruiter_dashboard.php" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filter-card">
                            <form method="get" action="manage_applications.php">
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label for="job_id" class="form-label">Job</label>
                                        <select class="form-select" id="job_id" name="job_id">
                                            <option value="0" <?= $job_filter === 0 ? 'selected' : '' ?>>All Jobs</option>
                                            <?php while ($job = $jobs->fetch_assoc()): ?>
                                                <option value="<?= $job['id'] ?>" <?= $job_filter === $job['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($job['title']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if ($applications->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Job Title</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($app = $applications->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                                    <div class="small text-muted"><?= htmlspecialchars($app['email']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                                <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $app['application_status'] === 'accepted' ? 'bg-success' : 
                                                           ($app['application_status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                        <?= ucfirst($app['application_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <?php if ($app['application_status'] == 'pending'): ?>
                                                            <a href="process_application.php?action=accept&id=<?= $app['id'] ?>" class="btn btn-sm btn-success">Accept</a>
                                                            <a href="process_application.php?action=reject&id=<?= $app['id'] ?>" class="btn btn-sm btn-danger">Reject</a>
                                                        <?php endif; ?>
                                                        <a href="view_application.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <img src="assets/images/no-applications.svg" alt="No applications" style="max-width: 300px;" class="mb-4">
                                <h4 class="mb-3">No applications found</h4>
                                <p class="text-muted">Try adjusting your filters or check back later</p>
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