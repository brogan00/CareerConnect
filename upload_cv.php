<?php
define('SECURE_ACCESS', true);
include "connexion/config.php";
session_start();


require_once 'functions/notifications_functions.php';


if (!isset($_SESSION['user_email'])) {
    header("Location: connexion/login.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'candidat') {
    echo "<script>alert('You do not have permission to access this page.');</script>";
    header("Location: index.php");
    exit();
}


$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->store_result();
$stmt->fetch();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $required = ['full-name', 'email', 'phone', 'degree', 'institution', 'graduation-year'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        $cv_path = null;
        if (isset($_FILES['cv-upload']) && $_FILES['cv-upload']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($_FILES['cv-upload']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type. Only PDF, DOC, and DOCX are allowed.");
            }
            
            if ($_FILES['cv-upload']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size exceeds 5MB limit");
            }
            
            $uploadDir = 'uploads/cvs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = uniqid() . '.' . $ext;
            $cv_path = $uploadDir . $filename;
            
            if (!move_uploaded_file($_FILES['cv-upload']['tmp_name'], $cv_path)) {
                throw new Exception("Failed to upload CV");
            }
        } else {
            $result = $conn->query("SELECT cv FROM users WHERE id = $user_id");
            if ($result && $row = $result->fetch_assoc()) {
                $cv_path = $row['cv'];
            } else {
                throw new Exception("CV upload is required");
            }
        }

        $conn->begin_transaction();

        $nameParts = explode(' ', $_POST['full-name'], 2);
        $firstName = trim($nameParts[0]);
        $lastName = trim($nameParts[1] ?? '');
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address'] ?? '');

        $stmt = $conn->prepare("UPDATE users SET 
                              first_name = ?, 
                              last_name = ?, 
                              email = ?, 
                              phone = ?, 
                              address = ?, 
                              cv = ?, 
                              status = 'pending',
                              updated_at = CURRENT_TIMESTAMP 
                              WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $phone, $address, $cv_path, $user_id);
        $stmt->execute();

        $degree = trim($_POST['degree']);
        $institution = trim($_POST['institution']);
        $grad_year = trim($_POST['graduation-year']);
        $start_date = ($grad_year - 1) . '-09-01';
        $end_date = $grad_year . '-06-15';
        $level = 'Licence';

        $stmt = $conn->prepare("INSERT INTO education (level, speciality, univ_name, start_date, end_date, user_id) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("sssssi", $level, $degree, $institution, $start_date, $end_date, $user_id);
        $stmt->execute();

        $conn->query("DELETE FROM experience WHERE user_id = $user_id");
        
        if (!empty($_POST['job-title'])) {
            $jobTitle = trim($_POST['job-title']);
            $company = trim($_POST['company'] ?? '');
            $startDate = trim($_POST['start-date'] ?? '');
            $endDate = trim($_POST['end-date'] ?? '');
            $jobDescription = trim($_POST['job-description'] ?? '');

            $stmt = $conn->prepare("INSERT INTO experience (job_name, company_name, start_date, end_date, description, user_id) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("sssssi", $jobTitle, $company, $startDate, $endDate, $jobDescription, $user_id);
            $stmt->execute();
        }

        $conn->query("DELETE FROM skills WHERE user_id = $user_id");
        
        if (!empty($_POST['skills'])) {
            $skillsArray = array_map('trim', explode(',', $_POST['skills']));
            $skillsArray = array_filter($skillsArray);
            
            if (!empty($skillsArray)) {
                $stmt = $conn->prepare("INSERT INTO skills (content, user_id) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception("Database error: " . $conn->error);
                }
                
                foreach ($skillsArray as $skill) {
                    $stmt->bind_param("si", $skill, $user_id);
                    $stmt->execute();
                }
            }
        }

        $name_query = $conn->query("SELECT first_name, last_name FROM users WHERE id = $user_id");
        if ($name_query && $name_query->num_rows > 0) {
            $name_data = $name_query->fetch_assoc();
            $full_name = htmlspecialchars($name_data['first_name'] . ' ' . $name_data['last_name']);
        } else {
            $full_name = "User";
        }

        $adminQuery = $conn->query("SELECT id FROM users WHERE type = 'admin'");
        if ($adminQuery) {
            while ($admin = $adminQuery->fetch_assoc()) {
                sendNotification($conn, [
                    'admin_id' => $admin['id'],
                    'message' => "New CV uploaded by $full_name needs approval",
                    'type' => 'cv_submission',
                    'related_id' => $user_id
                ]);
            }
        }

        sendNotification($conn, [
            'user_id' => $user_id,
            'message' => 'Your CV has been submitted for admin approval',
            'type' => 'cv_submission',
            'related_id' => $user_id
        ]);

        $conn->commit();
        
        $_SESSION['success'] = "CV submitted successfully!";
        header("Location: upload_cv.php");
        exit();



    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: upload_cv.php");
        exit();
    }
}

$userData = [];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
if ($result && $result->num_rows > 0) {
    $userData = $result->fetch_assoc();
}

$education = [];
$result = $conn->query("SELECT * FROM education WHERE user_id = $user_id");
if ($result && $result->num_rows > 0) {
    $education = $result->fetch_assoc();
}

$experience = [];
$result = $conn->query("SELECT * FROM experience WHERE user_id = $user_id ORDER BY start_date DESC");
if ($result && $result->num_rows > 0) {
    $experience = $result->fetch_all(MYSQLI_ASSOC);
}

$skills = [];
$result = $conn->query("SELECT content FROM skills WHERE user_id = $user_id");
if ($result && $result->num_rows > 0) {
    $skills = array_column($result->fetch_all(MYSQLI_ASSOC), 'content');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CV - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <style>
        .experience-entry {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Upload Your CV</h2>
        
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

        <form action="upload_cv.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="full-name" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control" id="full-name" name="full-name" 
                               value="<?= htmlspecialchars($userData['first_name'] . ' ' . ($userData['last_name'] ?? '')) ?>" required>
                        <div class="invalid-feedback">Please provide your full name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label required-field">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label required-field">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please provide your phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address (Wilaya)</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?= htmlspecialchars($userData['address'] ?? '') ?>" list="wilayas" placeholder="Select your wilaya">
                        <datalist id="wilayas">
                            <option value="Adrar">
                            <option value="Chlef">
                            <option value="Laghouat">
                            <option value="Oum El Bouaghi">
                            <option value="Batna">
                            <option value="Béjaïa">
                            <option value="Biskra">
                            <option value="Béchar">
                            <option value="Blida">
                            <option value="Bouira">
                            <option value="Tamanrasset">
                            <option value="Tébessa">
                            <option value="Tlemcen">
                            <option value="Tiaret">
                            <option value="Tizi Ouzou">
                            <option value="Algiers">
                            <option value="Djelfa">
                            <option value="Jijel">
                            <option value="Sétif">
                            <option value="Saïda">
                            <option value="Skikda">
                            <option value="Sidi Bel Abbès">
                            <option value="Annaba">
                            <option value="Guelma">
                            <option value="Constantine">
                            <option value="Médéa">
                            <option value="Mostaganem">
                            <option value="M'Sila">
                            <option value="Mascara">
                            <option value="Ouargla">
                            <option value="Oran">
                            <option value="El Bayadh">
                            <option value="Illizi">
                            <option value="Bordj Bou Arréridj">
                            <option value="Boumerdès">
                            <option value="El Tarf">
                            <option value="Tindouf">
                            <option value="Tissemsilt">
                            <option value="El Oued">
                            <option value="Khenchela">
                            <option value="Souk Ahras">
                            <option value="Tipaza">
                            <option value="Mila">
                            <option value="Aïn Defla">
                            <option value="Naâma">
                            <option value="Aïn Témouchent">
                            <option value="Ghardaïa">
                            <option value="Relizane">
                            <option value="Timimoun">
                            <option value="Bordj Badji Mokhtar">
                            <option value="Ouled Djellal">
                            <option value="Béni Abbès">
                            <option value="In Salah">
                            <option value="In Guezzam">
                            <option value="Touggourt">
                            <option value="Djanet">
                            <option value="El M'Ghair">
                            <option value="El Menia">
                        </datalist>
                    </div>
                </div>
        </div>

            <!-- Education -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Education</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="degree" class="form-label required-field">Degree</label>
                        <input type="text" class="form-control" id="degree" name="degree" 
                               value="<?= htmlspecialchars($education['speciality'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please provide your degree.</div>
                    </div>
                    <div class="mb-3">
                        <label for="institution" class="form-label required-field">Institution</label>
                        <input type="text" class="form-control" id="institution" name="institution" 
                               value="<?= htmlspecialchars($education['univ_name'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please provide your institution name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="graduation-year" class="form-label required-field">Graduation Year</label>
                        <input type="number" class="form-control" id="graduation-year" name="graduation-year" 
                               min="1900" max="<?= date('Y') + 5 ?>" 
                               value="<?= !empty($education['end_date']) ? date('Y', strtotime($education['end_date'])) : '' ?>" required>
                        <div class="invalid-feedback">Please provide a valid graduation year.</div>
                    </div>
                </div>
            </div>

            <!-- Work Experience -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Work Experience</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($experience)): ?>
                        <?php foreach ($experience as $exp): ?>
                            <div class="experience-entry mb-4">
                                <div class="mb-3">
                                    <label for="job-title" class="form-label">Job Title</label>
                                    <input type="text" class="form-control" name="job-title" 
                                           value="<?= htmlspecialchars($exp['job_name']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="company" class="form-label">Company</label>
                                    <input type="text" class="form-control" name="company" 
                                           value="<?= htmlspecialchars($exp['company_name']) ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start-date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start-date" 
                                               value="<?= htmlspecialchars($exp['start_date']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="end-date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end-date" 
                                               value="<?= htmlspecialchars($exp['end_date']) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="job-description" class="form-label">Job Description</label>
                                    <textarea class="form-control" name="job-description" rows="3"><?= htmlspecialchars($exp['description']) ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="experience-entry mb-4">
                            <div class="mb-3">
                                <label for="job-title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" name="job-title" placeholder="e.g., Software Engineer">
                            </div>
                            <div class="mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" name="company" placeholder="e.g., TechCorp">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start-date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start-date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end-date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end-date">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="job-description" class="form-label">Job Description</label>
                                <textarea class="form-control" name="job-description" rows="3" placeholder="Describe your role and responsibilities"></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skills -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Skills</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="skills" class="form-label">Add Skills (separate with commas)</label>
                        <input id="skills" name="skills" placeholder="Start typing..." class="form-control" 
                               value="<?= htmlspecialchars(implode(',', $skills)) ?>">
                    </div>
                </div>
            </div>

            <!-- Upload CV -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Upload Your CV</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cv-upload" class="form-label required-field">Choose File (PDF, DOC, DOCX - Max 5MB)</label>
                        <input type="file" class="form-control" id="cv-upload" name="cv-upload" accept=".pdf,.doc,.docx" <?= empty($userData['cv']) ? 'required' : '' ?>>
                        <div class="invalid-feedback">Please upload your CV.</div>
                        <?php if (!empty($userData['cv'])): ?>
                            <div class="mt-2">
                                <small>Current CV: <a href="<?= htmlspecialchars($userData['cv']) ?>" target="_blank">View</a></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5">Submit for Approval</button>
                <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2 px-5">Cancel</a>
            </div>
        </form>
    </div>

    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    
    <script>
    // Initialize only skills tagify
    const input = document.querySelector('#skills');
    const skillsList = [
        "JavaScript", "Python", "HTML", "CSS", "PHP", "Laravel", "Node.js", 
        "React", "Vue.js", "Angular", "Java", "Spring Boot", "C#", ".NET", 
        "SQL", "MySQL", "PostgreSQL", "MongoDB", "Firebase", "Git", "GitHub", 
        "Docker", "Kubernetes", "AWS", "Azure", "Google Cloud", "DevOps",
        "Agile", "Scrum", "UI/UX", "Figma", "Photoshop", "Illustrator", "SEO", 
        "Data Analysis", "Machine Learning", "AI", "TensorFlow", "Pandas", 
        "NumPy", "C++", "Rust", "Go", "Shell Scripting", "TypeScript", "Excel", 
        "WordPress", "JIRA", "Customer Service", "Marketing", "Public Speaking", 
        "Project Management", "Business Analysis"
    ];

    new Tagify(input, {
        whitelist: skillsList,
        dropdown: {
            enabled: 1,
            maxItems: 20
        }
    });

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

    document.getElementById('cv-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit');
            e.target.value = '';
        }
    });
    </script>
    </body>
    </html>