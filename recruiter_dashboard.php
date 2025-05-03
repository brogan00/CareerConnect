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

// Handle application approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $application_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // First get the recruiter's ID
    $recruiter_id_stmt = $conn->prepare("SELECT id FROM recruiter WHERE email = ?");
    $recruiter_id_stmt->bind_param("s", $recruiter_email);
    $recruiter_id_stmt->execute();
    $recruiter_id_result = $recruiter_id_stmt->get_result();
    
    if ($recruiter_id_result->num_rows > 0) {
        $recruiter_id = $recruiter_id_result->fetch_assoc()['id'];
        
        // Verify application belongs to this recruiter and get candidate details
        $verify_stmt = $conn->prepare("
            SELECT a.id, a.user_id, j.title, u.email AS candidate_email, 
                   u.first_name AS candidate_first_name, u.last_name AS candidate_last_name
            FROM application a 
            JOIN job j ON a.job_id = j.id 
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ? AND j.recruiter_id = ?
        ");
        $verify_stmt->bind_param("ii", $application_id, $recruiter_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows > 0) {
            $app_data = $verify_result->fetch_assoc();
            $status = ($action === 'accept') ? 'accepted' : 'rejected';
            
            // Update application status
            $update_stmt = $conn->prepare("UPDATE application SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $status, $application_id);
            $update_stmt->execute();
            
            // Create notification
            $message = "Your application for '{$app_data['title']}' has been $status";
            $notif_stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, related_id) 
                VALUES (?, ?, 'application', ?)
            ");
            $notif_type = ($action === 'accept') ? 'cv_approval' : 'cv_rejection';
            $notif_stmt->bind_param("isi", $app_data['user_id'], $message,  $application_id);
            $notif_stmt->execute();
            
            // Send email if accepted
            if ($action === 'accept') {
                $to = $app_data['candidate_email'];
                $subject = "Congratulations! Your application has been accepted";
                
                // HTML email content
                $message_body = "
                    <html>
                    <head>
                        <title>Application Accepted</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #4f46e5; color: white; padding: 20px; text-align: center; }
                            .content { padding: 20px; background-color: #f9fafb; }
                            .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
                            .button { 
                                display: inline-block; 
                                background-color: #4f46e5; 
                                color: white; 
                                padding: 10px 20px; 
                                text-decoration: none; 
                                border-radius: 5px; 
                                margin: 15px 0;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>Application Accepted!</h1>
                            </div>
                            <div class='content'>
                                <p>Dear {$app_data['candidate_first_name']},</p>
                                <p>We are pleased to inform you that your application for the position of <strong>{$app_data['title']}</strong> has been accepted!</p>
                                <p>Our team will contact you shortly to discuss the next steps in the hiring process.</p>
                                
                                <p>You can view your application status by logging into your account:</p>
                                <a href='http://yourwebsite.com/login.php' class='button'>Login to Your Account</a>
                                
                                <p>If you have any questions, please don't hesitate to contact us.</p>
                            </div>
                            <div class='footer'>
                                <p>Best regards,<br>
                                {$recruiter_profile['first_name']} {$recruiter_profile['last_name']}<br>
                                {$recruiter_profile['company_name']}</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                // Email headers
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: {$recruiter_profile['email']}" . "\r\n";
                $headers .= "Reply-To: {$recruiter_profile['email']}" . "\r\n";
                
                // Send the email
                mail($to, $subject, $message_body, $headers);
            }
            
            $_SESSION['success_message'] = "Application $status successfully!";
        } else {
            $_SESSION['error_message'] = "Invalid application";
        }
    } else {
        $_SESSION['error_message'] = "Recruiter not found";
    }
    
    header("Location: recruiter_dashboard.php");
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

// Get applications for recruiter's jobs
$applications_stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, 
           u.first_name, u.last_name, u.email, u.profile_picture, u.id as user_id,
           c.name AS company_name
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.recruiter_id = ?
    ORDER BY a.applied_at DESC
");
$applications_stmt->bind_param("i", $recruiter_id);
$applications_stmt->execute();
$applications = $applications_stmt->get_result();

// Get recruiter's jobs
$jobs_stmt = $conn->prepare("
    SELECT j.id, j.title, j.status, COUNT(a.id) AS application_count
    FROM job j
    LEFT JOIN application a ON j.id = a.job_id
    WHERE j.recruiter_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$jobs_stmt->bind_param("i", $recruiter_id);
$jobs_stmt->execute();
$jobs = $jobs_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Dashboard - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <style>
        .dashboard-card { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .candidate-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .profile-container { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; }
        .badge-pending { background-color: #f39c12; }
        .badge-accepted { background-color: #2ecc71; }
        .badge-rejected { background-color: #e74c3c; }
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
                            <li class="nav-item"><a class="nav-link active" href="recruiter_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="post_a_job.php"><i class="fas fa-plus-circle me-2"></i> Post Job</a></li>
                            <li class="nav-item"><a class="nav-link" href="manage_jobs.php"><i class="fas fa-briefcase me-2"></i> Manage Jobs</a></li>
                            <li class="nav-item"><a class="nav-link" href="connexion/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card dashboard-card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Job Applications</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($applications->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Job Title</th>
                                            <th>Company</th>
                                            <th>Applied On</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($app = $applications->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($app['profile_picture'])): ?>
                                                            <img src="<?= htmlspecialchars($app['profile_picture']) ?>" class="candidate-avatar me-2" alt="Candidate">
                                                        <?php else: ?>
                                                            <div class="candidate-avatar bg-light d-flex align-items-center justify-content-center me-2">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div><?= htmlspecialchars(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? '')) ?></div>
                                                            <div class="small text-muted"><?= htmlspecialchars($app['email'] ?? '') ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($app['job_title'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($app['company_name'] ?? '') ?></td>
                                                <td><?= !empty($app['applied_at']) ? date('M d, Y', strtotime($app['applied_at'])) : 'N/A' ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= ($app['status'] ?? '') === 'accepted' ? 'badge-accepted' : 
                                                           (($app['status'] ?? '') === 'rejected' ? 'badge-rejected' : 'badge-pending') ?>">
                                                        <?= ucfirst($app['status'] ?? 'pending') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (($app['status'] ?? '') === 'pending'): ?>
                                                            <a href="recruiter_dashboard.php?action=accept&id=<?= $app['id'] ?? '' ?>" 
                                                               class="btn btn-success" title="Accept">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="recruiter_dashboard.php?action=reject&id=<?= $app['id'] ?? '' ?>" 
                                                               class="btn btn-danger" title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="candidate_profile.php?id=<?= $app['user_id'] ?? '' ?>" 
                                                           class="btn btn-info" title="View Profile">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No applications received yet</p>
                                <a href="post_job.php" class="btn btn-primary">Post a Job</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Jobs -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h4 class="mb-0">Your Jobs</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($jobs->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($job = $jobs->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5><?= htmlspecialchars($job['title'] ?? '') ?></h5>
                                                <p class="small text-muted">
                                                    <?= $job['application_count'] ?? 0 ?> application(s)
                                                    <span class="badge 
                                                        <?= ($job['status'] ?? '') === 'approved' ? 'bg-success' : 
                                                           (($job['status'] ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning') ?> ms-2">
                                                        <?= ucfirst($job['status'] ?? 'unknown') ?>
                                                    </span>
                                                </p>
                                                <div class="d-flex justify-content-between">
                                                    <a href="job_details.php?id=<?= $job['id'] ?? '' ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    <a href="manage_job.php?id=<?= $job['id'] ?? '' ?>" class="btn btn-sm btn-outline-secondary">Manage</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">You haven't posted any jobs yet</p>
                                <a href="post_job.php" class="btn btn-primary">Post Your First Job</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>
    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm before rejecting
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to reject this application?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Confirm before accepting
        document.querySelectorAll('.btn-success').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to accept this application? An email will be sent to the candidate.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>