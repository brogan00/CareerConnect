<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Debug: View submitted data (remove after testing)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log(print_r($_POST, true));
}

// Check if user is logged in and is a recruiter
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'recruiter') {
    echo "<script>alert('You do not have permission to access this page.');</script>";
    header("Location: index.php");
    exit();
}

// Get recruiter ID from users table
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Get recruiter details from recruiter table
$recruiter_info = [];
$stmt = $conn->prepare("SELECT * FROM recruiter WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recruiter_info = $result->fetch_assoc();
$stmt->close();

// Get company info if available
$company_info = [];
if (!empty($recruiter_info['company_id'])) {
    $stmt = $conn->prepare("SELECT * FROM company WHERE id = ?");
    $stmt->bind_param("i", $recruiter_info['company_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $company_info = $result->fetch_assoc();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields (removed 'city' from requirements)
        $required = ['company_name', 'job_title', 'state', 'contract_type', 'job_description'];
        $missing_fields = [];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }

        // Sanitize inputs
        $company_name = $conn->real_escape_string(trim($_POST['company_name']));
        $company_industry = $conn->real_escape_string(trim($_POST['company_industry'] ?? ''));
        $company_description = $conn->real_escape_string(trim($_POST['company_description'] ?? ''));
        $company_website = $conn->real_escape_string(trim($_POST['company_website'] ?? ''));
        $contact_email = $conn->real_escape_string(trim($_POST['contact_email'] ?? ''));
        $contact_phone = $conn->real_escape_string(trim($_POST['contact_phone'] ?? ''));
        $job_title = $conn->real_escape_string(trim($_POST['job_title']));
        $state = $conn->real_escape_string(trim($_POST['state']));
        $contract_type = $conn->real_escape_string(trim($_POST['contract_type']));
        $working_hours = $conn->real_escape_string(trim($_POST['working_hours'] ?? ''));
        $salary_amount = is_numeric($_POST['salary_amount'] ?? 0) ? floatval($_POST['salary_amount']) : 0;
        $job_description = $conn->real_escape_string(trim($_POST['job_description']));
        $location = "$state, Algeria"; // Removed city from location string

        // Begin transaction
        $conn->begin_transaction();

        // Check if company exists or create new one
        if (!empty($recruiter_info['company_id'])) {
            $company_id = $recruiter_info['company_id'];
            
            // Update existing company
            $stmt = $conn->prepare("
                UPDATE company SET 
                name = ?, 
                location = ?, 
                description = ?, 
                updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $company_name, $location, $company_description, $company_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Create new company
            $stmt = $conn->prepare("
                INSERT INTO company (name, location, description, website, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssss", $company_name, $location, $company_description, $company_website);
            $stmt->execute();
            $company_id = $stmt->insert_id;
            $stmt->close();
            
            // Update recruiter's company association
            $stmt = $conn->prepare("UPDATE recruiter SET company_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $company_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Insert job with pending status
        $stmt = $conn->prepare("
            INSERT INTO job (
                title, mission, type_contract, salary, expiration_date,
                recruiter_id, created_at, updated_at, status
            ) VALUES (
                ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY),
                ?, NOW(), NOW(), 'pending'
            )
        ");
        $stmt->bind_param("sssdi", $job_title, $job_description, $contract_type, $salary_amount, $user_id);
        $stmt->execute();
        $job_id = $stmt->insert_id;
        $stmt->close();

        // Create notification for admin
        $notification_message = "New job posted by " . $recruiter_info['email'] . " needs approval";
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, admin_id, message, type, related_id, created_at)
            VALUES (?, (SELECT id FROM users WHERE type = 'admin' LIMIT 1), ?, 'job_approval', ?, NOW())
        ");
        $stmt->bind_param("isi", $user_id, $notification_message, $job_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Job submitted successfully! It will be visible after admin approval.";
        header("Location: post_a_job.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: post_a_job.php");
        exit();
    }
}

// List of Algerian wilayas
$algerian_wilayas = [
    "Adrar", "Chlef", "Laghouat", "Oum El Bouaghi", "Batna", "Béjaïa", "Biskra",
    "Béchar", "Blida", "Bouira", "Tamanrasset", "Tébessa", "Tlemcen", "Tiaret",
    "Tizi Ouzou", "Algiers", "Djelfa", "Jijel", "Sétif", "Saïda", "Skikda",
    "Sidi Bel Abbès", "Annaba", "Guelma", "Constantine", "Médéa", "Mostaganem",
    "M'Sila", "Mascara", "Ouargla", "Oran", "El Bayadh", "Illizi", "Bordj Bou Arréridj",
    "Boumerdès", "El Tarf", "Tindouf", "Tissemsilt", "El Oued", "Khenchela",
    "Souk Ahras", "Tipaza", "Mila", "Aïn Defla", "Naâma", "Aïn Témouchent",
    "Ghardaïa", "Relizane", "Timimoun", "Bordj Badji Mokhtar", "Ouled Djellal",
    "Béni Abbès", "In Salah", "In Guezzam", "Touggourt", "Djanet", "El M'Ghair",
    "El Menia"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
        .wilaya-suggestions {
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none;
        }
        .wilaya-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .wilaya-item:hover {
            background-color: #f8f9fa;
        }
        .wilaya-match {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Post a Job</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="company_name" class="form-label required-field">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?= htmlspecialchars($company_info['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_industry" class="form-label">Industry</label>
                        <input type="text" class="form-control" id="company_industry" name="company_industry" 
                               placeholder="e.g., Technology, Healthcare, Finance">
                    </div>
                    <div class="mb-3">
                        <label for="company_description" class="form-label">Company Description</label>
                        <textarea class="form-control" id="company_description" name="company_description" rows="3"><?= htmlspecialchars($company_info['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="company_website" class="form-label">Company Website</label>
                        <input type="url" class="form-control" id="company_website" name="company_website" 
                               value="<?= htmlspecialchars($company_info['website'] ?? '') ?>" placeholder="https://">
                    </div>
                    <div class="mb-3">
                        <label for="contact_email" class="form-label required-field">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                               value="<?= htmlspecialchars($recruiter_info['email'] ?? $_SESSION['user_email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label required-field">Contact Phone</label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                               value="<?= htmlspecialchars($recruiter_info['phone'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Job Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Job Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="job_title" class="form-label required-field">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required-field">Job Location</label>
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select" id="country" disabled>
                                    <option selected>Algeria</option>
                                </select>
                            </div>
                            <div class="col-md-5 position-relative">
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?= !empty($company_info['location']) ? trim(explode(',', $company_info['location'])[0]) : '' ?>" 
                                       placeholder="Wilaya (State)" required autocomplete="off">
                                <div class="wilaya-suggestions" id="wilaya-suggestions"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contract_type" class="form-label required-field">Contract Type</label>
                        <select class="form-select" id="contract_type" name="contract_type" required>
                            <option value="">Select contract type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="working_hours" class="form-label">Working Hours</label>
                        <select class="form-select" id="working_hours" name="working_hours">
                            <option value="">Any</option>
                            <option value="9-5">9 AM - 5 PM</option>
                            <option value="Flexible">Flexible</option>
                            <option value="Shift">Shift Work</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="salary_amount" class="form-label">Salary (DZD)</label>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="number" class="form-control" id="salary_amount" name="salary_amount" 
                                       placeholder="Amount" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="time_period" name="time_period">
                                    <option value="month">per month</option>
                                    <option value="year">per year</option>
                                    <option value="hour">per hour</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="job_description" class="form-label required-field">Job Description</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="5" required></textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Submit for Approval</button>
                <a href="dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })();

        // Wilaya autocomplete
        document.addEventListener('DOMContentLoaded', function() {
            const stateInput = document.getElementById('state');
            const suggestionsContainer = document.getElementById('wilaya-suggestions');
            const wilayas = <?php echo json_encode($algerian_wilayas); ?>;
            
            stateInput.addEventListener('input', function() {
                const input = this.value.toLowerCase();
                suggestionsContainer.innerHTML = '';
                
                if (input.length < 1) {
                    suggestionsContainer.style.display = 'none';
                    return;
                }
                
                const matches = wilayas.filter(wilaya => 
                    wilaya.toLowerCase().includes(input)
                );
                
                if (matches.length > 0) {
                    matches.forEach(wilaya => {
                        const div = document.createElement('div');
                        div.className = 'wilaya-item';
                        div.textContent = wilaya;
                        div.addEventListener('click', function() {
                            stateInput.value = wilaya;
                            suggestionsContainer.style.display = 'none';
                        });
                        suggestionsContainer.appendChild(div);
                    });
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!stateInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>