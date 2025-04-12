<?php
include "connexion/config.php";
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';

// Get user data
$user = [];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

// Get notifications
$notifications = [];
$result = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
if ($result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
}

// Mark notifications as read
$conn->query("UPDATE notifications SET status = 'read' WHERE user_id = $user_id AND status = 'unread'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
</head>
<body>
    <?php include "templates/header.php" ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'assets/images/default-profile.png') ?>" 
                             class="rounded-circle mb-3" width="150" height="150" alt="Profile Picture">
                        <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                        <p class="text-muted"><?= ucfirst($user['type']) ?></p>
                        
                        <div class="d-grid gap-2">
                            <a href="upload_cv.php" class="btn btn-primary">
                                <?= empty($user['cv']) ? 'Upload CV' : 'Update CV' ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>CV Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($user['status'] === 'pending'): ?>
                            <div class="alert alert-warning">
                                <strong>Pending Approval</strong>
                                <p>Your CV is under review by our team.</p>
                            </div>
                        <?php elseif ($user['status'] === 'active'): ?>
                            <div class="alert alert-success">
                                <strong>Approved</strong>
                                <p>Your CV is visible to recruiters.</p>
                                <?php if (!empty($user['cv_approved_at'])): ?>
                                    <small>Approved on: <?= date('M j, Y', strtotime($user['cv_approved_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($user['status'] === 'inactive'): ?>
                            <div class="alert alert-danger">
                                <strong>Rejected</strong>
                                <p>Your CV needs modifications.</p>
                                <?php if (!empty($user['cv_approval_notes'])): ?>
                                    <div class="mt-2">
                                        <strong>Admin Notes:</strong>
                                        <p><?= htmlspecialchars($user['cv_approval_notes']) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($user['cv_approved_at'])): ?>
                                    <small>Reviewed on: <?= date('M j, Y', strtotime($user['cv_approved_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Notifications</h5>
                        <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="text-muted">No new notifications</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                            <small><?= date('M j', strtotime($notification['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Your CV Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($user['cv'])): ?>
                            <div class="alert alert-info">
                                You haven't uploaded a CV yet. <a href="upload_cv.php">Upload now</a> to apply for jobs.
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <h6>CV File:</h6>
                                <a href="<?= htmlspecialchars($user['cv']) ?>" target="_blank" class="btn btn-outline-primary">
                                    View/Download CV
                                </a>
                            </div>
                            
                            <?php if (!empty($user['skills'])): ?>
                                <div class="mb-3">
                                    <h6>Skills:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php 
                                        $skills = explode(',', $user['skills']);
                                        foreach ($skills as $skill): 
                                        ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars(trim($skill)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['experience'])): ?>
                                <div class="mb-3">
                                    <h6>Experience:</h6>
                                    <p><?= nl2br(htmlspecialchars($user['experience'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['education'])): ?>
                                <div class="mb-3">
                                    <h6>Education:</h6>
                                    <p><?= nl2br(htmlspecialchars($user['education'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "templates/footer.php" ?>
    
    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/JS/script.js"></script>
</body>
</html>