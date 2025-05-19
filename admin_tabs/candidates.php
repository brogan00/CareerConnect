<?php
// Check if cv_status column exists, if not add it
$checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'cv_status'");
if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN cv_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_cv'])) {
        $candidateId = intval($_POST['candidate_id']);
        $conn->query("UPDATE users SET cv_status = 'approved' WHERE id = $candidateId");
        
        // Create notification
        $conn->query("INSERT INTO notifications (user_id, message, type) 
                     VALUES ($candidateId, 'Your CV has been approved by admin', 'cv_approval')");
        
        $_SESSION['success'] = 'CV approved successfully';
        header("Location: ?tab=candidates");
        exit;
    } 
    elseif (isset($_POST['reject_cv'])) {
        $candidateId = intval($_POST['candidate_id']);
        $conn->query("UPDATE users SET cv_status = 'rejected' WHERE id = $candidateId");
        
        // Create notification
        $conn->query("INSERT INTO notifications (user_id, message, type) 
                     VALUES ($candidateId, 'Your CV has been rejected by admin', 'cv_rejection')");
        
        $_SESSION['success'] = 'CV rejected successfully';
        header("Location: ?tab=candidates");
        exit;
    }
    elseif (isset($_POST['toggle_status'])) {
        $candidateId = intval($_POST['candidate_id']);
        $current = $conn->query("SELECT status FROM users WHERE id = $candidateId")->fetch_assoc()['status'];
        $newStatus = $current == 'active' ? 'inactive' : 'active';
        
        $conn->query("UPDATE users SET status = '$newStatus' WHERE id = $candidateId");
        $_SESSION['success'] = 'Candidate status updated';
        header("Location: ?tab=candidates");
        exit;
    }
}

// Get all candidates with their CV status
$candidates = $conn->query("
    SELECT u.* 
    FROM users u
    WHERE u.type = 'candidat'
    ORDER BY u.created_at DESC
");

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">';
    echo $_SESSION['success'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show">';
    echo $_SESSION['error'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['error']);
}
?>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manage Candidates</h5>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control search-input" placeholder="Search candidates...">
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>CV Status</th>
                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($candidate = $candidates->fetch_assoc()): 
                        // Get additional details for modal
                        $education = $conn->query("SELECT * FROM education WHERE user_id = {$candidate['id']}");
                        $experience = $conn->query("SELECT * FROM experience WHERE user_id = {$candidate['id']}");
                        $skills = $conn->query("SELECT * FROM skills WHERE user_id = {$candidate['id']}");
                    ?>
                    <tr>
                        <td><?= $candidate['id'] ?></td>
                        <td>
                            <img src="<?= !empty($candidate['profile_picture']) ? 
                                htmlspecialchars($candidate['profile_picture']) : 
                                'assets/images/default-profile.png' ?>" 
                                alt="Profile" class="rounded-circle" width="40" height="40">
                        </td>
                        <td><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></td>
                        <td><?= htmlspecialchars($candidate['email']) ?></td>
                        <td><?= htmlspecialchars($candidate['phone'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($candidate['cv']): ?>
                                <span class="status-badge badge-<?= $candidate['cv_status'] ?>">
                                    <?= ucfirst($candidate['cv_status'] ?? 'pending') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No CV</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge bg-<?= $candidate['status'] == 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($candidate['status']) ?>
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <div class="d-flex">
                                <!-- View CV Button -->
                                <?php if ($candidate['cv']): ?>
                                    <a href="<?= $candidate['cv'] ?>" target="_blank" class="btn btn-sm btn-primary me-2" 
                                       data-bs-toggle="tooltip" title="View CV">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    
                                    <!-- Approve/Reject Buttons -->
                                    <?php if ($candidate['cv_status'] != 'approved'): ?>
                                        <form method="POST" class="me-2">
                                            <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                            <button type="submit" name="approve_cv" class="btn btn-sm btn-success" 
                                                    data-bs-toggle="tooltip" title="Approve CV">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($candidate['cv_status'] != 'rejected'): ?>
                                        <form method="POST" class="me-2">
                                            <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                            <button type="submit" name="reject_cv" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="tooltip" title="Reject CV">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Toggle Status Button -->
                                <form method="POST" class="me-2">
                                    <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $candidate['status'] == 'active' ? 'warning' : 'success' ?>" 
                                            data-bs-toggle="tooltip" title="<?= $candidate['status'] == 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-<?= $candidate['status'] == 'active' ? 'ban' : 'check' ?>"></i>
                                    </button>
                                </form>
                                
                                <!-- View Details Button -->
                                <button class="btn btn-sm btn-info view-details" 
                                        data-id="<?= $candidate['id'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#candiddate_profile"
                                        data-bs-toggle="tooltip" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal (Single modal for all candidates) -->
<div class="modal fade" id="candidateDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Candidate Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Search functionality
    $('.search-input').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Confirm before rejecting CV
    $('form[action*="reject_cv"]').on('submit', function() {
        return confirm('Are you sure you want to reject this CV?');
    });
    
    // Load candidate details via AJAX when modal is shown
    $('#candidateDetailsModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var candidateId = button.data('id'); // Extract info from data-* attributes
        var modal = $(this);
        
        // Show loading spinner
        modal.find('#modalDetailsContent').html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        // Load content via AJAX
        $.ajax({
            url: 'get_candidate_details.php',
            type: 'GET',
            data: { id: candidateId },
            success: function(response) {
                modal.find('#modalDetailsContent').html(response);
            },
            error: function() {
                modal.find('#modalDetailsContent').html(`
                    <div class="alert alert-danger">
                        Error loading candidate details. Please try again.
                    </div>
                `);
            }
        });
    });
});
</script>