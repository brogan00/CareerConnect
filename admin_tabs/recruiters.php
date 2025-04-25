<?php
// admin_tabs/recruiters.php

// Handle recruiter deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recruiter'])) {
    $recruiter_id = intval($_POST['recruiter_email']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Delete associated jobs first
        $delete_jobs = $conn->prepare("DELETE FROM job WHERE recruiter_email = ?");
        $delete_jobs->bind_param("i", $recruiter_email);
        $delete_jobs->execute();
        
        // 2. Delete notifications related to this recruiter
        $delete_notifications = $conn->prepare("DELETE FROM notifications WHERE recruiter_email = ?");
        $delete_notifications->bind_param("i", $recruiter_email);
        $delete_notifications->execute();
        
        // 3. Finally delete the recruiter
        $delete_recruiter = $conn->prepare("DELETE FROM recruiter WHERE email = ?");
        $delete_recruiter->bind_param("i", $recruiter_email);
        $delete_recruiter->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = 'Recruiter deleted successfully';
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = 'Error deleting recruiter: ' . $e->getMessage();
    }
    
    header("Location: ?tab=recruiters");
    exit();
}

// Fetch all recruiters with their company information
$recruiters_query = "SELECT r.*, c.name AS company_name 
                    FROM recruiter r 
                    LEFT JOIN company c ON r.company_id = c.id 
                    ORDER BY r.created_at DESC";
$recruiters_result = $conn->query($recruiters_query);
$recruiters = $recruiters_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manage Recruiters</h5>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control search-input" placeholder="Search recruiters...">
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recruiters)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No recruiters found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recruiters as $recruiter): ?>
                            <tr>
                                <td><?= htmlspecialchars($recruiter['id']) ?></td>
                                <td>
                                    <img src="<?= !empty($recruiter['profile_picture']) ? 
                                        htmlspecialchars($recruiter['profile_picture']) : 
                                        'assets/images/default-profile.png' ?>" 
                                        alt="Profile" class="rounded-circle" width="40" height="40">
                                </td>
                                <td><?= htmlspecialchars($recruiter['first_name'] . ' ' . $recruiter['last_name']) ?></td>
                                <td><?= htmlspecialchars($recruiter['email']) ?></td>
                                <td><?= htmlspecialchars($recruiter['company_name'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($recruiter['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex">
                                        <a href="?tab=edit-recruiter&id=<?= $recruiter['id'] ?>" 
                                           class="btn btn-sm btn-primary me-2" 
                                           data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="delete-form">
                                            <input type="hidden" name="recruiter_id" value="<?= $recruiter['id'] ?>">
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete"
                                                    data-recruiter-name="<?= htmlspecialchars($recruiter['first_name'] . ' ' . $recruiter['last_name']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><span id="recruiterName"></span></strong>?</p>
                <p class="text-danger"><small>This action will also delete all jobs posted by this recruiter and cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize delete confirmation modal
    let deleteForm;
    let recruiterName;
    
    $('.delete-btn').on('click', function() {
        deleteForm = $(this).closest('form');
        recruiterName = $(this).data('recruiter-name');
        $('#recruiterName').text(recruiterName);
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').on('click', function() {
        // Add hidden input to indicate deletion request
        deleteForm.append('<input type="hidden" name="delete_recruiter" value="1">');
        deleteForm.submit();
    });
    
    // Search functionality
    $('.search-input').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>