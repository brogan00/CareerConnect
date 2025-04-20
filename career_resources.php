<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in as recruiter
if (!isset($_SESSION['user_email'])){
    header("Location: login.php");
    exit();
}

// Get filter parameters
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$experience = isset($_GET['experience']) ? $_GET['experience'] : '';
$skills = isset($_GET['skills']) ? $_GET['skills'] : '';
$education = isset($_GET['education']) ? $_GET['education'] : '';

// Build SQL query with filters
$query = "SELECT u.id, u.first_name, u.last_name, u.address, u.profile_picture, u.phone, u.email, u.about,
                 GROUP_CONCAT(DISTINCT e.speciality) AS specialities,
                 GROUP_CONCAT(DISTINCT e.level) AS education_levels,
                 GROUP_CONCAT(DISTINCT s.content) AS skills_list
          FROM users u
          LEFT JOIN education e ON u.id = e.user_id
          LEFT JOIN skills s ON u.id = s.user_id
          WHERE u.type = 'candidat' AND u.status = 'active' AND u.cv IS NOT NULL";

$conditions = [];
$params = [];

if (!empty($keyword)) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR e.speciality LIKE ? OR s.content LIKE ? OR u.about LIKE ?)";
    $params = array_merge($params, ["%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"]);
}



if (!empty($location)) {
    $conditions[] = "u.address LIKE ?";
    $params[] = "%$location%";
}

if (!empty($experience)) {
    $conditions[] = "e.level IN (?)";
    $params[] = $experience == 'senior' ? 'Master,Doctorat,Ingeniorat' : ($experience == 'mid' ? 'Licence,Master' : 'BAC');
}

if (!empty($education)) {
    $conditions[] = "e.level = ?";
    $params[] = $education == 'bachelor' ? 'Licence' : ($education == 'master' ? 'Master' : 'Doctorat');
}

if (!empty($skills)) {
    $skillsArray = explode(',', $skills);
    $skillConditions = [];
    foreach ($skillsArray as $skill) {
        $skillConditions[] = "s.content LIKE ?";
        $params[] = "%$skill%";
    }
    $conditions[] = "(" . implode(' OR ', $skillConditions) . ")";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

$query .= " GROUP BY u.id";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $candidates = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $candidates = [];
}

// Get all skills for autocomplete
$skills_query = "SELECT DISTINCT content FROM skills ORDER BY content ASC";
$skills_result = $conn->query($skills_query);
$all_skills = $skills_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search for a Candidate - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <script src="assets/JS/jquery-3.7.1.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #3a0ca3;
            --secondary-color: #4cc9f0;
            --accent-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .filter-section:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .filter-section h5 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .filter-section h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }

        .filter-section .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
        }

        .filter-section .form-control,
        .filter-section .form-select,
        .filter-section .select2-selection {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 201, 240, 0.25);
        }

        .candidate-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            overflow: hidden;
            background: white;
            position: relative;
        }
        
        .candidate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .candidate-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .candidate-card img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .candidate-card:hover img {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .candidate-card .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .candidate-card .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .candidate-card .btn-primary:hover {
            background: #480ca8;
            transform: translateY(-2px);
        }
        
        .candidate-card .btn-primary:hover::after {
            left: 100%;
        }

        .skill-badge {
            background-color: #e0e0e0;
            color: #333;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .skill-badge:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .section-title {
            position: relative;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 70px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }
        
        /* Animation classes */
        .animate-fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .animate-slide-up {
            animation: slideUp 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Profile details styling */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .profile-detail-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .profile-detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .detail-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .detail-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: var(--accent-color);
        }
        
        .detail-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .candidate-card {
                margin-bottom: 20px;
            }
            
            .filter-section {
                margin-bottom: 30px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Filter Section -->
            <div class="col-lg-4 mb-4 animate__animated animate__fadeInLeft">
                <div class="filter-section">
                    <h5>Filter Candidates</h5>
                    <form id="filterForm" method="GET" action="">
                        <!-- Keyword Search -->
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keywords</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" 
                                   value="<?= htmlspecialchars($keyword) ?>" placeholder="e.g., Python, React, Marketing">
                        </div>

                        <!-- Location - Algerian Wilayas -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location (Wilaya)</label>
                            <input class="form-control" list="wilayas" id="location" name="location" 
                                   value="<?= htmlspecialchars($location) ?>" placeholder="Select or type a wilaya">
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

                        <!-- Experience Level -->
                        <div class="mb-3">
                            <label for="experience" class="form-label">Experience Level</label>
                            <select class="form-select" id="experience" name="experience">
                                <option value="">Any</option>
                                <option value="entry" <?= $experience == 'entry' ? 'selected' : '' ?>>Entry Level</option>
                                <option value="mid" <?= $experience == 'mid' ? 'selected' : '' ?>>Mid Level</option>
                                <option value="senior" <?= $experience == 'senior' ? 'selected' : '' ?>>Senior Level</option>
                            </select>
                        </div>

                        <!-- Skills - Autocomplete -->
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" name="skills" 
                                   value="<?= htmlspecialchars($skills) ?>" placeholder="Start typing a skill...">
                            <div id="skillsSuggestions" class="mt-2"></div>
                        </div>

                        <!-- Education -->
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <select class="form-select" id="education" name="education">
                                <option value="">Any</option>
                                <option value="bachelor" <?= $education == 'bachelor' ? 'selected' : '' ?>>Bachelor's Degree</option>
                                <option value="master" <?= $education == 'master' ? 'selected' : '' ?>>Master's Degree</option>
                                <option value="phd" <?= $education == 'phd' ? 'selected' : '' ?>>PhD</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <span class="d-flex align-items-center justify-content-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-funnel-fill me-2" viewBox="0 0 16 16">
                                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
                                </svg>
                                Apply Filters
                            </span>
                        </button>
                        <?php if (!empty($_GET)): ?>
                            <a href="?" class="btn btn-outline-secondary w-100 mt-2 py-2">
                                <span class="d-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x-circle-fill me-2" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                                    </svg>
                                    Clear Filters
                                </span>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Candidate List -->
            <div class="col-lg-8 animate__animated animate__fadeInRight">
                <h2 class="mb-4 section-title">Candidates</h2>
                <?php if (empty($candidates)): ?>
                    <div class="alert alert-info animate__animated animate__fadeIn">No candidates found matching your criteria.</div>
                <?php else: ?>
                    <div class="row" id="candidateList">
                        <?php foreach ($candidates as $index => $candidate): ?>
                            <div class="col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                                <div class="card candidate-card">
                                    <div class="card-body text-center">
                                        <img src="<?= !empty($candidate['profile_picture']) ? htmlspecialchars($candidate['profile_picture']) : 'assets/icons/default-profile.png' ?>" 
                                             alt="Candidate Image" class="mb-3">
                                        <h5 class="card-title"><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h5>
                                        <p class="card-text">
                                            <?= !empty($candidate['specialities']) ? htmlspecialchars(explode(',', $candidate['specialities'])[0]) : 'No specialty specified' ?>
                                        </p>
                                        <p class="card-text"><small class="text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill me-1" viewBox="0 0 16 16">
                                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                            </svg>
                                            <?= htmlspecialchars($candidate['address']) ?>
                                        </small></p>
                                        
                                        <?php if (!empty($candidate['skills_list'])): ?>
                                            <div class="mb-3">
                                                <?php 
                                                $skills = explode(',', $candidate['skills_list']);
                                                foreach (array_slice($skills, 0, 3) as $skill): ?>
                                                    <span class="skill-badge"><?= htmlspecialchars(trim($skill)) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($skills) > 3): ?>
                                                    <span class="skill-badge">+<?= count($skills) - 3 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="candidate_profile.php?id=<?= $candidate['id'] ?>" class="btn btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill me-1" viewBox="0 0 16 16">
                                                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z"/>
                                            </svg>
                                            View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Skills autocomplete
            const allSkills = <?= json_encode(array_column($all_skills, 'content')) ?>;
            
            $('#skills').on('input', function() {
                const input = $(this).val().toLowerCase();
                if (input.length < 2) {
                    $('#skillsSuggestions').empty();
                    return;
                }
                
                const matches = allSkills.filter(skill => 
                    skill.toLowerCase().includes(input)
                ).slice(0, 5);
                
                let suggestionsHtml = '';
                if (matches.length > 0) {
                    suggestionsHtml = '<div class="list-group">';
                    matches.forEach(skill => {
                        suggestionsHtml += `
                            <a href="#" class="list-group-item list-group-item-action skill-suggestion" data-skill="${skill}">
                                ${skill}
                            </a>`;
                    });
                    suggestionsHtml += '</div>';
                }
                
                $('#skillsSuggestions').html(suggestionsHtml);
            });
            
            // Handle skill suggestion click
            $(document).on('click', '.skill-suggestion', function(e) {
                e.preventDefault();
                const skill = $(this).data('skill');
                const currentSkills = $('#skills').val();
                
                if (currentSkills) {
                    // Check if skill already exists
                    const skillsArray = currentSkills.split(',').map(s => s.trim());
                    if (!skillsArray.includes(skill)) {
                        $('#skills').val(currentSkills + ', ' + skill);
                    }
                } else {
                    $('#skills').val(skill);
                }
                
                $('#skillsSuggestions').empty();
                $('#skills').focus();
            });
            
            // Animate elements when they come into view
            const animateOnScroll = function() {
                $('.animate-on-scroll').each(function() {
                    const elementTop = $(this).offset().top;
                    const elementBottom = elementTop + $(this).outerHeight();
                    const viewportTop = $(window).scrollTop();
                    const viewportBottom = viewportTop + $(window).height();
                    
                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        const animation = $(this).data('animation');
                        $(this).addClass(`animate__animated animate__${animation}`);
                    }
                });
            };
            
            // Run once on page load
            animateOnScroll();
            
            // Run on scroll
            $(window).on('scroll', animateOnScroll);
        });
    </script>
</body>
</html>