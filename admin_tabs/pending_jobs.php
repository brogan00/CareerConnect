<?php
// Get pending jobs with recruiter and company info
$pending_jobs = [];
$stmt = $conn->prepare("
   SELECT j.*, r.first_name, r.last_name, r.email, c.name AS company_name
    FROM job j
    JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.status = 'pending'
    ORDER BY j.created_at DESC
");
/*SELECT j.*, r.first_name, r.last_name, r.email, c.name AS company_name
    FROM job j
    JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.status = 'pending'
    ORDER BY j.created_at DESC
*/
$stmt->execute();
$result = $stmt->get_result();
$pending_jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close(); 
// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $job_id = intval($_POST['job_id']);
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_email'];

    try {
        // Update job status
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE job SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $job_id);
        $stmt->execute();
        $stmt->close();

        // Get job details for notification
        $stmt = $conn->prepare("SELECT title, recruiter_id FROM job WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        header("Location: pending_jobs.php");
        exit();
    }
}
?>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pending Job Approvals</h5>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control search-input" placeholder="Search jobs...">
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($pending_jobs)): ?>
            <div class="alert alert-info">No jobs pending approval.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Recruiter</th>
                            <th>Posted On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_jobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                            data-bs-target="#jobModal<?= $job['id'] ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Job Details Modal -->
                            <div class="modal fade" id="jobModal<?= $job['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($job['title']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name'] ?? 'Not specified') ?></p>
                                                    <p><strong>Posted by:</strong> <?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></p>
                                                    <p><strong>Email:</strong> <?= htmlspecialchars($job['email']) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Contract Type:</strong> <?= htmlspecialchars($job['type_contract']) ?></p>
                                                    <p><strong>Salary:</strong> 
                                                        <?= $job['salary'] ? number_format($job['salary'], 2) . ' DZD' : 'Not specified' ?>
                                                    </p>
                                                    <p><strong>Posted on:</strong> <?= date('M d, Y', strtotime($job['created_at'])) ?></p>
                                                </div>
                                            </div>
                                            
                                            <h6>Job Description:</h6>
                                            <div class="border p-3 mb-3">
                                                <?= nl2br(htmlspecialchars($job['mission'])) ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times"></i> Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>