<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Redirect if not logged in as candidate
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: connexion/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get candidate applications with proper joins
$stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, c.name AS company_name, 
           a.status AS application_status, r.id AS recruiter_id
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result();

// Get candidate profile
$profile_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
$profile_stmt->close();

// Get notifications
$notifications_stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
    LIMIT 5
");
$notifications_stmt->bind_param("i", $user_id);
$notifications_stmt->execute();
$notifications = $notifications_stmt->get_result();

// Set default values
$first_name = $profile['first_name'] ?? 'User';
$last_name = $profile['last_name'] ?? '';
$profile_picture = $profile['profile_picture'] ?? null;
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
        .dashboard-card { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .profile-picture { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .notification-item { border-left: 3px solid #0d6efd; }
        .notification-unread { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <?php if ($profile_picture): ?>
                            <img src="<?= htmlspecialchars($profile_picture) ?>" class="profile-picture mb-3" alt="Profile">
                        <?php else: ?>
                            <div class="profile-picture mb-3 mx-auto bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h4><?= htmlspecialchars("$first_name $last_name") ?></h4>
                        <p class="text-muted">Candidate</p>
                        <hr>
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item"><a class="nav-link active" href="candidate_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="edit_profile.php"><i class="fas fa-user-edit me-2"></i> Edit Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="my_applications.php"><i class="fas fa-file-alt me-2"></i> My Applications</a></li>
                            <li class="nav-item"><a class="nav-link" href="connexion/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="card dashboard-card mt-4">
                    <div class="card-header"><h6>Recent Notifications</h6></div>
                    <div class="card-body p-0">
                        <?php if ($notifications->num_rows > 0): ?>
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
                                <div class="list-group-item notification-item <?= $notification['is_read'] ? '' : 'notification-unread' ?>">
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($notification['created_at'])) ?></small>
                                    <p class="mb-0 small"><?= htmlspecialchars($notification['message']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-3"><p class="text-muted small">No notifications</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card dashboard-card mb-4">
                    <div class="card-header">
                        <h4>Dashboard Overview</h4>
                    </div>
                    <div class="card-body">
                        <!-- Stats -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-start border-primary">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Applications</h6>
                                        <h3><?= $applications->num_rows ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-start border-warning">
                                    <div class="card-body">
                                        <h6 class="text-muted">Pending</h6>
                                        <?php
                                        $pending = 0;
                                        $applications->data_seek(0);
                                        while ($app = $applications->fetch_assoc()) {
                                            if ($app['application_status'] == 'pending') $pending++;
                                        }
                                        ?>
                                        <h3><?= $pending ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-start border-success">
                                    <div class="card-body">
                                        <h6 class="text-muted">Accepted</h6>
                                        <?php
                                        $accepted = 0;
                                        $applications->data_seek(0);
                                        while ($app = $applications->fetch_assoc()) {
                                            if ($app['application_status'] == 'accepted') $accepted++;
                                        }
                                        ?>
                                        <h3><?= $accepted ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Applications -->
                        <h5>Recent Applications</h5>
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
                                        <?php $applications->data_seek(0); ?>
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
                                                    <a href="job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No applications yet. <a href="job_search.php">Browse jobs</a></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recommended Jobs -->
                <div class="card dashboard-card">
                    <div class="card-header"><h5>Recommended Jobs</h5></div>
                    <div class="card-body">
                        <?php
                        // Safely get recommended jobs
                        $query = "
                            SELECT j.*, c.name AS company_name 
                            FROM job j
                            JOIN recruiter r ON j.recruiter_id = r.id
                            JOIN company c ON r.company_id = c.id
                            WHERE j.status = 'approved'
                        ";
                        
                        if ($user_id > 0) {
                            $query .= " AND j.id NOT IN (
                                SELECT job_id FROM application WHERE user_id = $user_id
                            )";
                        }
                        
                        $query .= " ORDER BY j.created_at DESC LIMIT 3";
                        $recommended_jobs = $conn->query($query);
                        
                        if ($recommended_jobs && $recommended_jobs->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($job = $recommended_jobs->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6><?= htmlspecialchars($job['title']) ?></h6>
                                                <p class="small text-muted"><?= htmlspecialchars($job['company_name']) ?></p>
                                                <p class="small"><?= htmlspecialchars($job['mission']) ?></p>
                                                <div class="d-flex justify-content-between">
                                                    <span class="badge bg-primary"><?= htmlspecialchars($job['type_contract']) ?></span>
                                                    <div>
                                                        <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        <a href="apply_job.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-primary">Apply</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No recommended jobs found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>
    <script src="assets/JS/bootstrap.bundle.min.js"></script>
</body>
</html>