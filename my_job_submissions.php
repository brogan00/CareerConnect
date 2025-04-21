<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Authentication check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: connexion/login.php");
    exit();
}

// Get recruiter ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch ALL submitted jobs with full details
$submitted_jobs = [];
$stmt = $conn->prepare("
    SELECT 
        j.*,
        c.name AS company_name,
        c.description AS company_description,
        c.website AS company_website,
        c.location AS company_location,
        r.email AS contact_email,
    FROM job j
    LEFT JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.recruiter_id = ?
    ORDER BY j.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$submitted_jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count jobs by status
$status_counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach ($submitted_jobs as $job) {
    $status_counts[$job['status']]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Submissions - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .job-card {
            transition: all 0.3s;
            border-left: 4px solid;
        }
        .job-card.pending { border-left-color: #ffc107; }
        .job-card.approved { border-left-color: #28a745; }
        .job-card.rejected { border-left-color: #dc3545; }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .detail-label {
            font-weight: 500;
            color: #6c757d;
        }
        .company-logo-placeholder {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="display-5 fw-bold">My Job Submissions</h1>
                <p class="lead">View and track all jobs you've submitted</p>
                
                <!-- Status Summary -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="px-4 py-3 bg-white rounded shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-hourglass text-warning fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Pending</h6>
                                <h2 class="mb-0"><?= $status_counts['pending'] ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-white rounded shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Approved</h6>
                                <h2 class="mb-0"><?= $status_counts['approved'] ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-white rounded shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-x-circle text-danger fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Rejected</h6>
                                <h2 class="mb-0"><?= $status_counts['rejected'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs List -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($submitted_jobs)): ?>
                    <div class="text-center py-5 bg-light rounded">
                        <i class="bi bi-briefcase fs-1 text-muted"></i>
                        <h4 class="mt-3">No jobs submitted yet</h4>
                        <p class="text-muted">Get started by posting your first job opportunity</p>
                        <a href="post_a_job.php" class="btn btn-primary px-4">Post a Job</a>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="jobsAccordion">
                        <?php foreach ($submitted_jobs as $job): ?>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#job-<?= $job['id'] ?>">
                                    <div class="d-flex w-100 align-items-center">
                                        <div class="company-logo-placeholder me-3">
                                            <i class="bi bi-building text-muted fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-column flex-md-row justify-content-between">
                                                <h5 class="mb-1"><?= htmlspecialchars($job['title']) ?></h5>
                                                <div>
                                                    <span class="badge rounded-pill 
                                                        <?= $job['status'] == 'pending' ? 'bg-warning text-dark' : 
                                                           ($job['status'] == 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                                        <?= ucfirst($job['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-muted">
                                                <?= htmlspecialchars($job['company_name']) ?> • 
                                                Submitted <?= date('M j, Y', strtotime($job['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div id="job-<?= $job['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#jobsAccordion">
                                <div class="accordion-body pt-0">
                                    <div class="row">
                                        <!-- Left Column - Job Details -->
                                        <div class="col-md-7">
                                            <h5 class="mb-3">Job Details</h5>
                                            
                                            <div class="mb-3">
                                                <div class="detail-label">Description</div>
                                                <p><?= nl2br(htmlspecialchars($job['mission'])) ?></p>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6 col-md-4 mb-3">
                                                    <div class="detail-label">Contract Type</div>
                                                    <div><?= htmlspecialchars($job['type_contract']) ?></div>
                                                </div>
                                                <div class="col-6 col-md-4 mb-3">
                                                    <div class="detail-label">Salary</div>
                                                    <div>
                                                        <?= $job['salary'] > 0 ? 
                                                            number_format($job['salary']) . ' DZD' : 'Not specified' ?>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-4 mb-3">
                                                    <div class="detail-label">Expires On</div>
                                                    <div><?= date('M j, Y', strtotime($job['expiration_date'])) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Right Column - Company & Contact -->
                                        <div class="col-md-5 border-start ps-md-4">
                                            <h5 class="mb-3">Company Information</h5>
                                            
                                            <div class="mb-3">
                                                <div class="detail-label">Company Name</div>
                                                <p><?= htmlspecialchars($job['company_name']) ?></p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="detail-label">Location</div>
                                                <p><?= htmlspecialchars($job['company_location']) ?></p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="detail-label">Description</div>
                                                <p><?= nl2br(htmlspecialchars($job['company_description'])) ?></p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="detail-label">Website</div>
                                                <p>
                                                    <?= $job['company_website'] ? 
                                                        '<a href="'.htmlspecialchars($job['company_website']).'" target="_blank">'.htmlspecialchars($job['company_website']).'</a>' : 
                                                        'Not specified' ?>
                                                </p>
                                            </div>
                                            
                                            <h5 class="mt-4 mb-3">Contact Information</h5>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <div class="detail-label">Email</div>
                                                    <p><?= htmlspecialchars($job['contact_email']) ?></p>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="detail-label">Phone</div>
                                                    <p><?= htmlspecialchars($job['contact_phone']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Message -->
                                    <div class="alert mt-4 
                                        <?= $job['status'] == 'pending' ? 'alert-warning' : 
                                           ($job['status'] == 'approved' ? 'alert-success' : 'alert-danger') ?>">
                                        <?php if ($job['status'] == 'pending'): ?>
                                            <i class="bi bi-hourglass"></i> This job is awaiting admin approval. 
                                            Typical review time is 1-2 business days.
                                        <?php elseif ($job['status'] == 'approved'): ?>
                                            <i class="bi bi-check-circle"></i> This job has been approved and is visible to candidates.
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-circle"></i> This job was rejected. Please review admin feedback and resubmit.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-expand if URL has #job-ID hash
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                if (target && target.classList.contains('accordion-collapse')) {
                    new bootstrap.Collapse(target, { toggle: true });
                }
            }
        });
    </script>
</body>
</html>