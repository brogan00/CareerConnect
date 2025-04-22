<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in as candidate
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}

if ($_SESSION['user_type'] !== 'candidat') {
    header("Location: index.php");
    exit();
}

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get job details with proper null checks
$job = [
    'id' => 0,
    'title' => 'Job Not Found',
    'company_name' => 'Unknown Company',
    'company_location' => 'Unknown Location',
    'type_contract' => 'N/A',
    'salary' => 0,
    'mission' => 'No description available',
    'recruiter_id' => null
];

$stmt = $conn->prepare("
    SELECT j.*, 
           COALESCE(c.name, 'Unknown Company') AS company_name, 
           COALESCE(c.location, 'Unknown Location') AS company_location,
           r.id AS recruiter_id
    FROM job j
    LEFT JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.id = ? AND j.status = 'approved'
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $job = array_merge($job, $result->fetch_assoc());
}
$stmt->close();

// Get candidate details with CV check
$user_id = $_SESSION['user_email'];
$candidate = [
    'first_name' => 'User',
    'last_name' => '',
    'email' => $_SESSION['user_email'],
    'phone' => '',
    'cv' => null,
    'cv_exists' => false
];

$stmt = $conn->prepare("SELECT first_name, last_name, phone, cv FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $dbData = $result->fetch_assoc();
    $candidate = array_merge($candidate, $dbData);
    
    // Check if CV exists in database and on server
    if (!empty($dbData['cv'])) {
        $candidate['cv_exists'] = file_exists($dbData['cv']);
        $candidate['cv'] = $dbData['cv'];
    }
}
$stmt->close();

// Process application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if candidate has a CV
        if (!$candidate['cv_exists']) {
            throw new Exception("You must upload a CV before applying for jobs");
        }

        // Check if already applied
        $stmt = $conn->prepare("SELECT id FROM application WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ii", $user_id, $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("You have already applied for this position");
        }
        $stmt->close();

        // Insert application
        $stmt = $conn->prepare("
            INSERT INTO application (user_id, job_id, status, applied_at, updated_at)
            VALUES (?, ?, 'pending', NOW(), NOW())
        ");
        $status = 'pending';
        $stmt->bind_param("ii", $user_id, $job_id);
        $stmt->execute();
        $application_id = $stmt->insert_id;
        $stmt->close();

        // Create notification if recruiter exists
        if ($job['recruiter_id']) {
            $message = "New application for your job: " . $job['title'];
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, recruiter_id, message, type, related_id, created_at)
                VALUES (?, ?, ?, 'application', ?, NOW())
            ");
            $type = 'application';
            $recruiter_id = $job['recruiter_id'];
            $stmt->bind_param("iisi", $user_id, $recruiter_id, $message, $application_id);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect to success page
        header("Location: application_success.php?id=" . $application_id);
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?= htmlspecialchars($job['title']) ?> - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <style>
        .application-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .application-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .form-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .preview-section {
            border: 1px dashed #dee2e6;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .cv-alert {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container py-5">
        <div class="application-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="application-header text-center">
                <h2>Apply for <?= htmlspecialchars($job['title']) ?></h2>
                <p class="lead"><?= htmlspecialchars($job['company_name']) ?> • <?= htmlspecialchars($job['company_location']) ?></p>
            </div>

            <form method="POST" action="">
                <div class="form-section">
                    <h4 class="mb-4">Your Application</h4>
                    
                    <div class="preview-section mb-4">
                        <h5><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($candidate['email']) ?></p>
                        <?php if (!empty($candidate['phone'])): ?>
                            <p><i class="fas fa-phone me-2"></i> <?= htmlspecialchars($candidate['phone']) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($candidate['cv_exists']): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <span>CV Found: </span>
                                    <a href="<?= htmlspecialchars($candidate['cv']) ?>" target="_blank" class="alert-link">View Your CV</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert cv-alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    No CV found. Please <a href="upload_cv.php" class="alert-link">upload your CV</a> before applying.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="cover_letter" class="form-label">Cover Letter (Optional)</label>
                        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="6" placeholder="Explain why you're a good fit for this position..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" <?= !$candidate['cv_exists'] ? 'disabled' : '' ?>>
                            <i class="fas fa-paper-plane me-2"></i> Submit Application
                        </button>
                        <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Job
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
    <script>
        // Disable form submission if no CV exists
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn.disabled) {
                submitBtn.title = "You must upload a CV before applying";
            }
        });
    </script>
</body>
</html>