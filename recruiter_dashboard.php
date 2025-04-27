<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: connexion/login.php");
    exit();
}

$recruiter_id = $_SESSION['user_email'];

// Handle application status update
if (isset($_POST['update_status'])) {
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status'];
    $message = $_POST['message'] ?? '';
    
    // Update application status
    $update_stmt = $conn->prepare("UPDATE application SET status = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("si", $status, $app_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Get application details for notification
    $app_info = $conn->prepare("
        SELECT a.user_id, a.job_id, j.title AS job_title, 
               u.first_name, u.last_name, u.email AS candidate_email
        FROM application a
        JOIN job j ON a.job_id = j.id
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
    ");
    $app_info->bind_param("i", $app_id);
    $app_info->execute();
    $app_data = $app_info->get_result()->fetch_assoc();
    $app_info->close();
    
    // Prepare notification message
    $notif_message = "Your application for '{$app_data['job_title']}' has been {$status}";
    if (!empty($message)) {
        $notif_message .= ". Message from recruiter: " . htmlspecialchars($message);
    }
    
    $notif_type = ($status == 'accepted') ? 'cv_approval' : 'cv_rejection';
    
    // Create notification for candidate
    $notif_stmt = $conn->prepare("
        INSERT INTO notifications (user_id, recruiter_id, message, type, related_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $notif_stmt->bind_param("iissi", 
        $app_data['user_id'], 
        $recruiter_id, 
        $notif_message, 
        $notif_type, 
        $app_data['job_id']
    );
    $notif_stmt->execute();
    $notif_stmt->close();
    
    $_SESSION['success_message'] = "Application status updated successfully!";
    header("Location: recruiter_dashboard.php");
    exit();
}

// Get pending applications
$apps_stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, 
           u.first_name AS candidate_first, u.last_name AS candidate_last,
           u.email AS candidate_email, u.phone AS candidate_phone,
           u.cv AS candidate_cv
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.recruiter_id = ? AND a.status = 'pending'
    ORDER BY a.applied_at DESC
");
$apps_stmt->bind_param("i", $recruiter_id);
$apps_stmt->execute();
$applications = $apps_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$apps_stmt->close();

// Get other applications
$other_apps_stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, 
           u.first_name AS candidate_first, u.last_name AS candidate_last,
           u.email AS candidate_email, u.phone AS candidate_phone
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.recruiter_id = ? AND a.status != 'pending'
    ORDER BY a.updated_at DESC
");
$other_apps_stmt->bind_param("i", $recruiter_id);
$other_apps_stmt->execute();
$other_applications = $other_apps_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$other_apps_stmt->close();

// Get unread notifications
$notif_stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE recruiter_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
");
$notif_stmt->bind_param("i", $recruiter_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notif_stmt->close();

// Mark notifications as read
$mark_read = $conn->prepare("
    UPDATE notifications SET is_read = 1 
    WHERE recruiter_id = ? AND is_read = 0
");
$mark_read->bind_param("i", $recruiter_id);
$mark_read->execute();
$mark_read->close();
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
        .application-card {
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        .application-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Notifications</h5>
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
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Pending Applications</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($applications) > 0): ?>
                            <div class="row">
                                <?php foreach ($applications as $app): ?>
                                    <div class="col-12 mb-3">
                                        <div class="card application-card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($app['job_title']) ?></h5>
                                                <h6 class="card-subtitle mb-2 text-muted">
                                                    <?= htmlspecialchars($app['candidate_first'] . ' ' . $app['candidate_last']) ?>
                                                </h6>
                                                <p class="card-text">
                                                    <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($app['candidate_email']) ?><br>
                                                    <?php if (!empty($app['candidate_phone'])): ?>
                                                        <i class="fas fa-phone me-2"></i> <?= htmlspecialchars($app['candidate_phone']) ?>
                                                    <?php endif; ?>
                                                </p>
                                                
                                                <?php if (!empty($app['candidate_cv'])): ?>
                                                    <a href="<?= htmlspecialchars($app['candidate_cv']) ?>" class="btn btn-sm btn-outline-primary mb-3" target="_blank">
                                                        <i class="fas fa-file-pdf me-1"></i> View CV
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Applied on <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                                    </small>
                                                </p>
                                                
                                                <form method="post" class="mt-3">
                                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="message_<?= $app['id'] ?>" class="form-label">Optional Message:</label>
                                                        <textarea class="form-control" id="message_<?= $app['id'] ?>" name="message" rows="2" placeholder="Add a personal message..."></textarea>
                                                    </div>
                                                    
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" name="update_status" value="accepted" class="btn btn-success flex-grow-1">
                                                            <i class="fas fa-check me-1"></i> Accept
                                                        </button>
                                                        <button type="submit" name="update_status" value="rejected" class="btn btn-danger flex-grow-1">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No pending applications</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0">Processed Applications</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($other_applications) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Job Title</th>
                                            <th>Status</th>
                                            <th>Applied On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($other_applications as $app): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($app['candidate_first'] . ' ' . $app['candidate_last']) ?><br>
                                                    <small><?= htmlspecialchars($app['candidate_email']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = '';
                                                    if ($app['status'] == 'accepted') $badge_class = 'badge-accepted';
                                                    elseif ($app['status'] == 'rejected') $badge_class = 'badge-rejected';
                                                    else $badge_class = 'badge-pending';
                                                    ?>
                                                    <span class="badge rounded-pill status-badge <?= $badge_class ?>">
                                                        <?= ucfirst($app['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                                <td>
                                                    <a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-envelope me-1"></i> Contact
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No processed applications</p>
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