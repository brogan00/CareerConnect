<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Verify recruiter is logged in
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: connexion/login.php");
    exit();
}

$recruiter_email = $_SESSION['user_email'];

// Handle job status updates
if (isset($_GET['action'])) {
    $job_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Verify job belongs to this recruiter
    $verify_stmt = $conn->prepare("SELECT id FROM job WHERE id = ? AND recruiter_id = (SELECT id FROM recruiter WHERE email = ?)");
    $verify_stmt->bind_param("is", $job_id, $recruiter_email);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        if ($action === 'delete') {
            // Delete job
            $delete_stmt = $conn->prepare("DELETE FROM job WHERE id = ?");
            $delete_stmt->bind_param("i", $job_id);
            $delete_stmt->execute();
            $_SESSION['success_message'] = "Job deleted successfully!";
        } elseif (in_array($action, ['activate', 'deactivate'])) {
            // Update job status
            $new_status = ($action === 'activate') ? 'approved' : 'pending';
            $update_stmt = $conn->prepare("UPDATE job SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_status, $job_id);
            $update_stmt->execute();
            $_SESSION['success_message'] = "Job status updated successfully!";
        }
    } else {
        $_SESSION['error_message'] = "Invalid job or unauthorized action";
    }
    
    header("Location: manage_jobs.php");
    exit();
}

// Get recruiter profile with company info
$recruiter_stmt = $conn->prepare("
    SELECT r.*, c.name AS company_name 
    FROM recruiter r
    LEFT JOIN company c ON r.company_id = c.id
    WHERE r.email = ?
");
$recruiter_stmt->bind_param("s", $recruiter_email);
$recruiter_stmt->execute();
$recruiter_result = $recruiter_stmt->get_result();
$recruiter_profile = $recruiter_result->fetch_assoc();
$recruiter_stmt->close();

if (!$recruiter_profile) {
    $_SESSION['error_message'] = "Recruiter profile not found";
    header("Location: connexion/login.php");
    exit();
}

$recruiter_id = $recruiter_profile['id'];

// Get recruiter's jobs with application counts
$jobs_stmt = $conn->prepare("
    SELECT j.*, 
           COUNT(a.id) AS application_count,
           c.name AS company_name
    FROM job j
    LEFT JOIN application a ON j.id = a.job_id
    LEFT JOIN company c ON j.recruiter_id = ?
    WHERE j.recruiter_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$jobs_stmt->bind_param("ii", $recruiter_id, $recruiter_id);
$jobs_stmt->execute();
$jobs = $jobs_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <style>
        .dashboard-card { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .profile-container { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; }
        .job-card { transition: transform 0.2s; }
        .job-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="profile-container mx-auto mb-2">
                                <?php if (!empty($recruiter_profile['profile_picture'])): ?>
                                    <img src="<?= htmlspecialchars($recruiter_profile['profile_picture']) ?>" 
                                         class="img-fluid" alt="Profile">
                                <?php else: ?>
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h5><?= htmlspecialchars(($recruiter_profile['first_name'] ?? '') . ' ' . ($recruiter_profile['last_name'] ?? '')) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($recruiter_profile['company_name'] ?? 'No company') ?></p>
                        </div>
                        <hr>
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item"><a class="nav-link" href="recruiter_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="post_a_job.php"><i class="fas fa-plus-circle me-2"></i> Post Job</a></li>
                            <li class="nav-item"><a class="nav-link active" href="manage_jobs.php"><i class="fas fa-briefcase me-2"></i> Manage Jobs</a></li>
                            <li class="nav-item"><a class="nav-link" href="connexion/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Your Jobs</h2>
                    <a href="post_a_job.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Post New Job
                    </a>
                </div>

                <?php if ($jobs->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($job = $jobs->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card job-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title"><?= htmlspecialchars($job['title'] ?? '') ?></h5>
                                                <p class="text-muted small mb-1">
                                                    <?= htmlspecialchars($job['company_name'] ?? '') ?>
                                                </p>
                                            </div>
                                            <span class="badge 
                                                <?= ($job['status'] ?? '') === 'approved' ? 'bg-success' : 
                                                   (($job['status'] ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                <?= ucfirst($job['status'] ?? 'pending') ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-2">
                                            <i class="fas fa-file-contract me-2"></i>
                                            <?= htmlspecialchars($job['type_contract'] ?? 'Not specified') ?>
                                        </p>
                                        
                                        <?php if (!empty($job['salary'])): ?>
                                            <p class="card-text text-muted small mb-2">
                                                <i class="fas fa-money-bill-wave me-2"></i>
                                                <?= number_format($job['salary'], 2) ?> DZD
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p class="card-text text-muted small mb-3">
                                            <i class="fas fa-calendar-day me-2"></i>
                                            Expires: <?= !empty($job['expiration_date']) ? date('M d, Y', strtotime($job['expiration_date'])) : 'Not set' ?>
                                        </p>
                                        
                                        <p class="card-text mb-3">
                                            <?= !empty($job['mission']) ? nl2br(htmlspecialchars(substr($job['mission'], 0, 150) . (strlen($job['mission']) > 150 ? '...' : ''))) : 'No description' ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="job_applications.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-users me-1"></i>
                                                <?= $job['application_count'] ?? 0 ?> Applications
                                            </a>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="jobActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="jobActionsDropdown">
                                                    <li><a class="dropdown-item" href="edit_job.php?id=<?= $job['id'] ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                                    <?php if ($job['status'] === 'approved'): ?>
                                                        <li><a class="dropdown-item" href="manage_jobs.php?action=deactivate&id=<?= $job['id'] ?>"><i class="fas fa-pause me-2"></i> Deactivate</a></li>
                                                    <?php else: ?>
                                                        <li><a class="dropdown-item" href="manage_jobs.php?action=activate&id=<?= $job['id'] ?>"><i class="fas fa-play me-2"></i> Activate</a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?= $job['id'] ?>)">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <small class="text-muted">
                                            Posted: <?= date('M d, Y', strtotime($job['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card dashboard-card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-briefcase fa-4x text-muted mb-4"></i>
                            <h4>No Jobs Posted Yet</h4>
                            <p class="text-muted mb-4">You haven't posted any jobs yet. Get started by posting your first job opening.</p>
                            <a href="post_a_job.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Post Your First Job
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>
    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(jobId) {
            if (confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
                window.location.href = `manage_jobs.php?action=delete&id=${jobId}`;
            }
        }
    </script>
</body>
</html>