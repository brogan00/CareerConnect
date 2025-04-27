<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Get company ID from URL if coming from company search
$company_id = isset($_GET['company_id']) ? intval($_GET['company_id']) : 0;

// Handle job application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'candidat') {
        $_SESSION['error_message'] = "You must be logged in as a candidate to apply for jobs";
        header("Location: connexion/login.php");
        exit();
    }

    $job_id = intval($_POST['job_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if already applied
    $check_stmt = $conn->prepare("SELECT id FROM application WHERE user_id = ? AND job_id = ?");
    $check_stmt->bind_param("ii", $user_id, $job_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Insert new application
        $insert_stmt = $conn->prepare("INSERT INTO application (status, user_id, job_id, applied_at) VALUES ('pending', ?, ?, NOW())");
        if ($insert_stmt->bind_param("ii", $user_id, $job_id) && $insert_stmt->execute()) {
            $application_id = $conn->insert_id;
            
            // Get job details for notification
            $job_stmt = $conn->prepare("SELECT j.title, c.name AS company_name FROM job j JOIN recruiter r ON j.recruiter_id = r.id JOIN company c ON r.company_id = c.id WHERE j.id = ?");
            $job_stmt->bind_param("i", $job_id);
            $job_stmt->execute();
            $job_data = $job_stmt->get_result()->fetch_assoc();
            
            // Create notification for candidate
            $message = "You applied for '{$job_data['title']}' at {$job_data['company_name']} - status pending";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, related_id, created_at) VALUES (?, ?, 'application', ?, NOW())");
            $notif_stmt->bind_param("isi", $user_id, $message, $application_id);
            $notif_stmt->execute();
            
            $_SESSION['success_message'] = "Application submitted successfully!";
        } else {    
            $_SESSION['error_message'] = "Error submitting application";
        }
    } else {
        $_SESSION['error_message'] = "You've already applied to this job";
    }
    
    header("Location: job_search.php");
    exit();
}


// Time elapsed function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Initialize filter variables
$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$job_type = isset($_GET['job_type']) ? trim($_GET['job_type']) : '';
$salary_min = isset($_GET['salary_min']) ? intval($_GET['salary_min']) : 0;

// Get company name if coming from company search
$company_name = '';
if ($company_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM company WHERE id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $company = $result->fetch_assoc();
        $company_name = $company['name'];
    }
}

// Get list of jobs user has already applied to
$applied_jobs = [];
if (isset($_SESSION['user_email']) && $_SESSION['user_type'] === 'candidat') {
    $applied_stmt = $conn->prepare("SELECT job_id FROM application WHERE user_id = ?");
    $applied_stmt->bind_param("i", $_SESSION['user_email']);
    $applied_stmt->execute();
    $applied_result = $applied_stmt->get_result();
    while ($row = $applied_result->fetch_assoc()) {
        $applied_jobs[] = $row['job_id'];
    }
    $applied_stmt->close();
}

// Build the SQL query with filters
$sql = "SELECT j.*, c.name AS company, c.location AS company_location 
        FROM job j
        LEFT JOIN recruiter r ON j.recruiter_id = r.id
        LEFT JOIN company c ON r.company_id = c.id
        WHERE j.status = 'approved'";

$params = [];
$types = '';

// Add keyword filter (searches in title and mission)
if (!empty($keywords)) {
    $sql .= " AND (j.title LIKE ? OR j.mission LIKE ?)";
    $searchTerm = "%$keywords%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

// Add company filter if coming from company search
if ($company_id > 0) {
    $sql .= " AND c.id = ?";
    $params[] = $company_id;
    $types .= 'i';
}

// Add location filter
if (!empty($location)) {
    $sql .= " AND c.location LIKE ?";
    $locationTerm = "%$location%";
    $params[] = $locationTerm;
    $types .= 's';
}

// Add job type filter
if (!empty($job_type) && $job_type !== 'all') {
    $sql .= " AND j.type_contract = ?";
    $params[] = $job_type;
    $types .= 's';
}

// Add salary filter
if ($salary_min > 0) {
    $sql .= " AND j.salary >= ?";
    $params[] = $salary_min;
    $types .= 'i';
}

// Order by most recent first
$sql .= " ORDER BY j.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Job Search - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/icons/all.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <style>
        .job-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        
        .salary-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .text-truncate-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .job-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .filter-card {
            position: sticky;
            top: 20px;
        }

        #salaryValue {
            font-weight: 600;
        }

        .form-range::-webkit-slider-thumb {
            background: #0d6efd;
        }

        .form-range::-moz-range-thumb {
            background: #0d6efd;
        }

        .form-range::-ms-thumb {
            background: #0d6efd;
        }
        
        .company-filter-badge {
            background-color: #e9f7fe;
            color: #0d6efd;
            padding: 8px 12px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .company-filter-badge .close {
            margin-left: 8px;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .already-applied {
            background-color: #e8e8e8;
            color: #666;
            cursor: not-allowed;
        }
        
        .application-modal .modal-header {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>

<body>

    <?php include "templates/header.php" ?>

    <!-- Job Search Content -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Find Your Dream Job</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Filters -->
            <div class="col-md-3">
                <form id="filterForm" method="GET" action="">
                    <!-- Pass company_id if it exists -->
                    <?php if ($company_id > 0): ?>
                        <input type="hidden" name="company_id" value="<?= $company_id ?>">
                    <?php endif; ?>
                    
                    <div class="card p-3 filter-card">
                        <h5 class="mb-3">Filters</h5>
                        
                        <?php if (!empty($company_name)): ?>
                            <div class="company-filter-badge">
                                <?= htmlspecialchars($company_name) ?>
                                <a href="job_search.php" class="close">&times;</a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="keywords" class="form-label">Job Name</label>
                            <input type="text" class="form-control" id="keywords" name="keywords" 
                                   placeholder="Job title, keywords" value="<?= htmlspecialchars($keywords) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="City or State" value="<?= htmlspecialchars($location) ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="job-type" class="form-label">Job Type</label>
                            <select class="form-select" id="job-type" name="job_type">
                                <option value="all">All Types</option>
                                <option value="Full-time" <?= $job_type === 'Full-time' ? 'selected' : '' ?>>Full-Time</option>
                                <option value="Part-time" <?= $job_type === 'Part-time' ? 'selected' : '' ?>>Part-Time</option>
                                <option value="Contract" <?= $job_type === 'Contract' ? 'selected' : '' ?>>Contract</option>
                                <option value="Remote" <?= $job_type === 'Remote' ? 'selected' : '' ?>>Remote</option>
                            </select>
                        </div>
                        <!-- Salary Range -->
                        <div class="mb-3">
                            <label for="salary" class="form-label">Minimum Salary (DZD)</label>
                            <input type="range" class="form-range" id="salary" name="salary_min" 
                                   min="0" max="500000" step="10000" 
                                   value="<?= $salary_min ?>" 
                                   oninput="document.getElementById('salaryValue').textContent = formatNumber(this.value) + ' DZ'">
                            <div class="d-flex justify-content-between">
                                <span>0 DZ</span>
                                <span id="salaryValue"><?= number_format($salary_min) ?> DZ</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <?php if (!empty($keywords) || !empty($location) || $job_type !== 'all' || $salary_min > 0 || $company_id > 0): ?>
                            <a href="job_search.php" class="btn btn-outline-secondary mt-2 w-100">Reset Filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Job Listings -->
            <div id="jobs_list" class="col-md-9">
                <?php if ($jobs->num_rows > 0): ?>
                    <?php if ($company_id > 0): ?>
                        <div class="alert alert-info mb-4">
                            Showing jobs from <strong><?= htmlspecialchars($company_name) ?></strong>
                            <a href="job_search.php" class="float-end">Show all companies</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <?php while ($row = $jobs->fetch_assoc()): 
                            $has_applied = in_array($row['id'], $applied_jobs);
                        ?>
                            <div class="col-md-6 mb-4">
                                <div class="card job-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-primary mb-2"><?= htmlspecialchars($row['type_contract']) ?></span>
                                                <h5 class="card-title mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-building me-2"></i><?= htmlspecialchars($row['company']) ?>
                                                </p>
                                            </div>
                                            <div class="salary-badge">
                                                <span><?= number_format($row['salary']) ?> DZ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="job-meta mb-3">
                                            <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($row['company_location']) ?></span>
                                            <span><i class="far fa-clock me-1"></i> Posted <?= time_elapsed_string($row['created_at']) ?></span>
                                        </div>
                                        
                                        <p class="card-text text-truncate-3 mb-4"><?= htmlspecialchars($row['mission']) ?></p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="job_details.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                            <?php if (isset($_SESSION['user_email']) && $_SESSION['user_type'] === 'candidat'): ?>
                                                <?php if ($has_applied): ?>
                                                    <button class="btn btn-secondary btn-sm already-applied" disabled>
                                                        <i class="fas fa-check"></i> Applied
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#applyModal<?= $row['id'] ?>">
                                                        Apply Now
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif (!isset($_SESSION['user_email'])): ?>
                                                <a href="connexion/login.php" class="btn btn-primary btn-sm">Login to Apply</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Application Modal -->
<div class="modal fade" id="applyModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="applyModalLabel<?= $row['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applyModalLabel<?= $row['id'] ?>">Confirm Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="job_search.php">
                <div class="modal-body">
                    <p>Are you sure you want to apply for the <strong><?= htmlspecialchars($row['title']) ?></strong> position at <strong><?= htmlspecialchars($row['company']) ?></strong>?</p>
                    <p>Your profile information and CV will be sent to the recruiter.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <input type="hidden" name="job_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="apply_job" class="btn btn-primary">Confirm Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="assets/images/no-jobs.svg" alt="No jobs found" style="max-width: 300px;" class="mb-4">
                        <h4 class="mb-3">No jobs found matching your criteria</h4>
                        <p class="text-muted">Try adjusting your search filters or check back later</p>
                        <a href="job_search.php" class="btn btn-primary mt-3">Reset Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
    <script>
        // Format numbers with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Initialize salary display
        document.addEventListener('DOMContentLoaded', function() {
            const salaryInput = document.getElementById('salary');
            const salaryValue = document.getElementById('salaryValue');
            salaryValue.textContent = formatNumber(salaryInput.value) + ' DZ';
            
            // Auto-submit form when filters change (optional)
            document.querySelectorAll('#filterForm select').forEach(element => {
                element.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });
        });
    </script>
</body>
</html>