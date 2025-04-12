<?php
include "../../connexion/config.php";
session_start();

// Check admin permissions
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    $notes = $_POST['notes'] ?? '';
    
    // Update user status
    if ($action === 'approve') {
        $status = 'active';
        $message = "Your CV has been approved and is now visible to recruiters.";
    } else {
        $status = 'inactive';
        $message = "Your CV has been rejected. " . (!empty($notes) ? "Reason: $notes" : "");
    }
    
    $stmt = $conn->prepare("UPDATE users SET 
                          status = ?,
                          cv_approval_notes = ?,
                          cv_approved_by = ?,
                          cv_approved_at = CURRENT_TIMESTAMP
                          WHERE id = ?");
    $stmt->bind_param("ssii", $status, $notes, $admin_id, $user_id);
    $stmt->execute();
    
    // Add admin comment
    if (!empty($notes)) {
        $stmt = $conn->prepare("INSERT INTO cv_comments (cv_id, admin_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $admin_id, $notes);
        $stmt->execute();
    }
    
    // Notify user
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, admin_id, message, type, related_id) 
                          VALUES (?, ?, ?, 'cv_approval', ?)");
    $stmt->bind_param("iisi", $user_id, $admin_id, $message, $user_id);
    $stmt->execute();
    
    // Log the action
    $action_text = $action === 'approve' ? "CV Approved" : "CV Rejected";
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, admin_id, action, table_name, record_id, new_value) 
                          VALUES (?, ?, ?, 'users', ?, ?)");
    $new_value = "CV $action by admin ID: $admin_id";
    $stmt->bind_param("iissi", $user_id, $admin_id, $action_text, $user_id, $new_value);
    $stmt->execute();
    
    $_SESSION['success'] = "CV $action successfully";
    header("Location: admin/approve_cvs.php");
    exit();
}

// Fetch pending CVs with their details
$pending_cvs = [];
$query = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.cv, u.created_at, 
                 e.speciality AS degree, e.univ_name AS institution
          FROM users u
          LEFT JOIN education e ON u.id = e.user_id
          WHERE u.status = 'pending' AND u.cv IS NOT NULL
          ORDER BY u.created_at DESC";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    $pending_cvs = $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get CV comments
function getCVComments($conn, $cv_id) {
    $comments = [];
    $stmt = $conn->prepare("SELECT c.*, u.first_name, u.last_name 
                          FROM cv_comments c
                          JOIN users u ON c.admin_id = u.id
                          WHERE c.cv_id = ?
                          ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $cv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $comments = $result->fetch_all(MYSQLI_ASSOC);
    }
    return $comments;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve CVs - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/CSS/admin.css">
</head>
<body>
    <?php include "../../templates/admin_header.php" ?>
    
    <div class="container mt-5">
        <h2 class="mb-4">CV Approval Dashboard</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (empty($pending_cvs)): ?>
            <div class="alert alert-info">No pending CVs for approval</div>
        <?php else: ?>
            <div class="accordion" id="cvAccordion">
                <?php foreach ($pending_cvs as $cv): ?>
                    <?php $comments = getCVComments($conn, $cv['id']); ?>
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading<?= $cv['id'] ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= $cv['id'] ?>" aria-expanded="true" 
                                    aria-controls="collapse<?= $cv['id'] ?>">
                                <?= htmlspecialchars($cv['first_name'] . ' ' . $cv['last_name']) ?>
                                <span class="badge bg-secondary ms-2">New</span>
                            </button>
                        </h2>
                        <div id="collapse<?= $cv['id'] ?>" class="accordion-collapse collapse show" 
                             aria-labelledby="heading<?= $cv['id'] ?>" data-bs-parent="#cvAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Candidate Information</h5>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($cv['first_name'] . ' ' . $cv['last_name']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($cv['email']) ?></p>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($cv['phone']) ?></p>
                                        <p><strong>Education:</strong> <?= htmlspecialchars($cv['degree']) ?> at <?= htmlspecialchars($cv['institution']) ?></p>
                                        <p><strong>Submitted:</strong> <?= date('M j, Y', strtotime($cv['created_at'])) ?></p>
                                        
                                        <?php if ($cv['cv']): ?>
                                            <a href="../../<?= htmlspecialchars($cv['cv']) ?>" target="_blank" class="btn btn-primary mt-2">
                                                View CV
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5>Approval Action</h5>
                                        <form method="POST" class="mb-4">
                                            <input type="hidden" name="user_id" value="<?= $cv['id'] ?>">
                                            <div class="mb-3">
                                                <label for="notes" class="form-label">Comments (optional)</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                          placeholder="Provide feedback for the candidate..."></textarea>
                                            </div>
                                            <div class="d-grid gap-2 d-md-block">
                                                <button type="submit" name="action" value="approve" class="btn btn-success me-2">
                                                    Approve CV
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger">
                                                    Reject CV
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <?php if (!empty($comments)): ?>
                                            <h5>Previous Comments</h5>
                                            <div class="comments-container" style="max-height: 200px; overflow-y: auto;">
                                                <?php foreach ($comments as $comment): ?>
                                                    <div class="card mb-2">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title"><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></h6>
                                                            <p class="card-text small"><?= htmlspecialchars($comment['comment']) ?></p>
                                                            <p class="card-text text-muted small">
                                                                <?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/JS/bootstrap.bundle.min.js"></script>
</body>
</html>