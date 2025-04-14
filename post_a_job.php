<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in and is a recruiter
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}
if (!isset($_SESSION['user_type'])) {
    if($_SESSION['user_type'] !== 'recruiter') {
        echo "<script>alert('You do not have permission to access this page.');</script>";
        header("Location: index.php");
        exit();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
    $company_industry = filter_input(INPUT_POST, 'company_industry', FILTER_SANITIZE_STRING);
    $company_description = filter_input(INPUT_POST, 'company_description', FILTER_SANITIZE_STRING);
    $company_website = filter_input(INPUT_POST, 'company_website', FILTER_SANITIZE_URL);
    $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_SANITIZE_EMAIL);
    $contact_phone = filter_input(INPUT_POST, 'contact_phone', FILTER_SANITIZE_STRING);
    $job_title = filter_input(INPUT_POST, 'job_title', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $contract_type = filter_input(INPUT_POST, 'contract_type', FILTER_SANITIZE_STRING);
    $working_hours = filter_input(INPUT_POST, 'working_hours', FILTER_SANITIZE_STRING);
    $salary_amount = filter_input(INPUT_POST, 'salary_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $job_description = filter_input(INPUT_POST, 'job_description', FILTER_SANITIZE_STRING);
    $recruiter_id = $_SESSION['user_id'];

    // Validate required fields
    $required = ['company_name', 'job_title', 'city', 'state', 'contract_type', 'job_description'];
    foreach ($required as $field) {
        if (empty($$field)) {
            $error = "Please fill in all required fields";
            break;
        }
    }

    if (!isset($error)) {
        try {
            // Start transaction
            $conn->beginTransaction();

            // Check if company exists or create new one
            $stmt = $conn->prepare("SELECT id FROM company WHERE name = ? LIMIT 1");
            $stmt->execute([$company_name]);
            $company_id = $stmt->fetchColumn();

            if (!$company_id) {
                $stmt = $conn->prepare("
                    INSERT INTO company (name, location, description, website, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $location = "$city, $state, Algeria";
                $stmt->execute([$company_name, $location, $company_description, $company_website]);
                $company_id = $conn->lastInsertId();
            }

            // Insert job - FIXED PARAMETER COUNT
            $stmt = $conn->prepare("
                INSERT INTO job (
                    title, mission, type_contract, salary, expiration_date, 
                    recruter_id, created_at, updated_at, location
                ) VALUES (
                    ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 
                    ?, NOW(), NOW(), ?
                )
            ");
            
            $location = "$city, $state, Algeria";
            $stmt->execute([
                $job_title, 
                $job_description, 
                $contract_type, 
                $salary_amount, 
                $recruiter_id, 
                $location
            ]);
            
            $job_id = $conn->lastInsertId();

            // Update recruiter's company association if needed
            $stmt = $conn->prepare("UPDATE recruter SET company_id = ? WHERE id = ?");
            $stmt->execute([$company_id, $recruiter_id]);

            $conn->commit();
            
            // Redirect to job details page
            header("Location: job_details.php?id=$job_id");
            exit();

        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get recruiter info to pre-fill company details - FIXED SESSION VARIABLE
$recruiter_info = [];
try {
    $stmt = $conn->prepare("
        SELECT r.*, c.name as company_name, c.description as company_description, 
               c.website as company_website, c.location as company_location
        FROM recruter r
        LEFT JOIN company c ON r.company_id = c.id
        WHERE r.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recruiter_info = $stmt->fetch(PDO::FETCH_ASSOC);   
} catch (PDOException $e) {
    // Continue with empty recruiter info
}

// List of all Algerian wilayas
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
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Post a Job - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <style>
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .form-control, .form-select {
            margin-bottom: 15px;
        }
        .datalist-container {
            position: relative;
        }
        datalist {
            position: absolute;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            background: white;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 0 0 5px 5px;
            display: none;
        }
        datalist option {
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        datalist option:hover {
            background-color: #f8f9fa;
        }
        input:focus + datalist {
            display: block;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Post a Job Form -->
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Post a Job</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Company Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name*</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?php echo htmlspecialchars($recruiter_info['company_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_industry" class="form-label">Industry</label>
                        <input type="text" class="form-control" id="company_industry" name="company_industry" 
                               placeholder="e.g., Technology, Healthcare, Finance">
                    </div>
                    <div class="mb-3">
                        <label for="company_description" class="form-label">Company Description</label>
                        <textarea class="form-control" id="company_description" name="company_description" 
                                  rows="3"><?php echo htmlspecialchars($recruiter_info['company_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="company_website" class="form-label">Company Website</label>
                        <input type="url" class="form-control" id="company_website" name="company_website" 
                               value="<?php echo htmlspecialchars($recruiter_info['company_website'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Contact Email*</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                               value="<?php echo htmlspecialchars($recruiter_info['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Contact Phone*</label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                               value="<?php echo htmlspecialchars($recruiter_info['phone'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Job Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Job Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title*</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="job_location" class="form-label">Job Location*</label>
                        <div class="row">
                            <div class="col">
                                <select class="form-select" id="country" disabled>
                                    <option selected>Algeria</option>
                                </select>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" id="city" name="city" placeholder="Commune (City)" required>
                            </div>
                            <div class="col datalist-container">
                                <input type="text" class="form-control" id="state" name="state" 
                                       placeholder="Wilaya (State)" list="wilayas" required
                                       autocomplete="off">
                                <datalist id="wilayas">
                                    <?php foreach ($algerian_wilayas as $wilaya): ?>
                                        <option value="<?php echo htmlspecialchars($wilaya); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="contract_type" class="form-label">Contract Type*</label>
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
                            <div class="col">
                                <input type="number" class="form-control" id="salary_amount" name="salary_amount" 
                                       placeholder="Amount" min="0" step="1000">
                            </div>
                            <div class="col">
                                <select class="form-select" id="time_period" name="time_period">
                                    <option value="year">per year</option>
                                    <option value="month">per month</option>
                                    <option value="hour">per hour</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="job_description" class="form-label">Job Description*</label>
                        <textarea class="form-control" id="job_description" name="job_description" rows="5" required></textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
            </div>
        </form>
    </div>

    <?php include "templates/footer.php" ?>

    <script>
        // Enhanced datalist functionality with animation
        document.addEventListener('DOMContentLoaded', function() {
            const stateInput = document.getElementById('state');
            const datalist = document.querySelector('datalist');
            
            // Show datalist when input is focused
            stateInput.addEventListener('focus', function() {
                datalist.style.display = 'block';
            });
            
            // Hide datalist when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== stateInput) {
                    datalist.style.display = 'none';
                }
            });
            
            // Pre-fill location if available in recruiter info
            <?php if (!empty($recruiter_info['company_location'])): ?>
                const locationParts = "<?php echo $recruiter_info['company_location']; ?>".split(', ');
                if (locationParts.length >= 2) {
                    document.getElementById('city').value = locationParts[0];
                    document.getElementById('state').value = locationParts[1];
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>