<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Debug mode - uncomment these to see errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Get filter parameters
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$experience = isset($_GET['experience']) ? trim($_GET['experience']) : '';
$skills = isset($_GET['skills']) ? trim($_GET['skills']) : '';
$education = isset($_GET['education']) ? trim($_GET['education']) : '';

// Main query to get candidates
$query = "SELECT 
    u.id, u.first_name, u.last_name, u.address, u.profile_picture, 
    u.phone, u.email, u.about, u.cv,
    GROUP_CONCAT(DISTINCT e.speciality) AS specialities,
    GROUP_CONCAT(DISTINCT e.level) AS education_levels,
    GROUP_CONCAT(DISTINCT s.content) AS skills_list,
    GROUP_CONCAT(DISTINCT exp.job_name) AS job_titles
FROM users u
LEFT JOIN education e ON u.id = e.user_id
LEFT JOIN skills s ON u.id = s.user_id
LEFT JOIN experience exp ON u.id = exp.user_id
WHERE u.type = 'candidat' 
AND u.status = 'active' 
AND u.cv IS NOT NULL
";

$conditions = [];
$params = [];
$types = '';

if (!empty($keyword)) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR e.speciality LIKE ? OR s.content LIKE ? OR u.about LIKE ? OR exp.job_name LIKE ?)";
    $searchTerm = "%$keyword%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= str_repeat('s', 6);
}

if (!empty($location)) {
    $conditions[] = "u.address LIKE ?";
    $params[] = "%$location%";
    $types .= 's';
}

if (!empty($experience)) {
    $conditions[] = "e.level IN (?)";
    $params[] = $experience == 'senior' ? 'Master,Doctorat,Ingeniorat' : ($experience == 'mid' ? 'Licence,Master' : 'BAC');
    $types .= 's';
}

if (!empty($education)) {
    $conditions[] = "e.level = ?";
    $params[] = $education == 'bachelor' ? 'Licence' : ($education == 'master' ? 'Master' : 'Doctorat');
    $types .= 's';
}

if (!empty($skills)) {
    $skillsArray = explode(',', $skills);
    $skillConditions = [];
    foreach ($skillsArray as $skill) {
        $skill = trim($skill);
        $skillConditions[] = "s.content LIKE ?";
        $params[] = "%$skill%";
        $types .= 's';
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
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $candidates = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Database error: " . $conn->error);
}

// Get all skills for autocomplete
$skills_query = "SELECT DISTINCT content FROM skills ORDER BY content ASC";
$skills_result = $conn->query($skills_query);
$all_skills = $skills_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for Candidates - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/icons/all.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <style>
        .job-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        
        .candidate-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .skill-badge {
            background-color: #e0e0e0;
            color: #333;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .skill-badge:hover {
            background-color: #d0d0d0;
        }
        
        /* Tagify styles */
        .tagify {
            --tags-border-color: #ced4da;
            --tag-bg: #e0e0e0;
            --tag-text-color: #333;
            --tag-remove-btn-color: #dc3545;
        }
        
        .tagify__dropdown__item {
            padding: 8px 12px;
        }
        
        .tagify__dropdown__item--active {
            background-color: #0d6efd;
            color: white;
        }
        
        /* Wilaya input animation */
        #location {
            transition: all 0.3s ease;
        }
        
        #location:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Find Top Candidates</h2>
        
        <!-- Debug Info -->
        <div class="alert alert-info mb-4">
            <strong>Debug Info:</strong> 
            Found <?php echo count($candidates); ?> candidates matching your criteria.
            <?php if (count($candidates) > 0): ?>
                First candidate: <?php echo $candidates[0]['first_name'] . ' ' . $candidates[0]['last_name']; ?>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Filters Column -->
            <div class="col-md-3">
                <form id="filterForm" method="GET" action="">
                    <div class="card p-3 mb-4">
                        <h5 class="mb-3">Filters</h5>
                        
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keywords</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" 
                                   placeholder="Name, title, etc." value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location (Wilaya)</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="Start typing wilaya..." value="<?= htmlspecialchars($location) ?>" list="wilayas">
                            <datalist id="wilayas">
                                <option value="Adrar">
                                <option value="Algiers">
                                <option value="Batna">
                                <option value="Tiaret">
                                <!-- Add more wilayas as needed -->
                            </datalist>
                        </div>
                        
                        <div class="mb-3">
                            <label for="experience" class="form-label">Experience Level</label>
                            <select class="form-select" id="experience" name="experience">
                                <option value="">All Levels</option>
                                <option value="entry" <?= $experience == 'entry' ? 'selected' : '' ?>>Entry Level</option>
                                <option value="mid" <?= $experience == 'mid' ? 'selected' : '' ?>>Mid Level</option>
                                <option value="senior" <?= $experience == 'senior' ? 'selected' : '' ?>>Senior Level</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" name="skills" 
                                   placeholder="Type and press enter" value="<?= htmlspecialchars($skills) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <select class="form-select" id="education" name="education">
                                <option value="">All Education</option>
                                <option value="bachelor" <?= $education == 'bachelor' ? 'selected' : '' ?>>Bachelor's Degree</option>
                                <option value="master" <?= $education == 'master' ? 'selected' : '' ?>>Master's Degree</option>
                                <option value="phd" <?= $education == 'phd' ? 'selected' : '' ?>>PhD</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <?php if (!empty($_GET)): ?>
                            <a href="?" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Candidates List -->
            <div class="col-md-9">
                <?php if (empty($candidates)): ?>
                    <div class="alert alert-warning">No candidates found matching your criteria. Try adjusting your filters.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($candidates as $candidate): 
                            // Clean up skills data
                            $skills_list = !empty($candidate['skills_list']) ? $candidate['skills_list'] : '';
                            $skills_array = explode(',', $skills_list);
                            $skills_array = array_map('trim', $skills_array);
                            $skills_array = array_filter($skills_array);
                        ?>
                            <div class="col-md-6 mb-4">
                                <div class="card job-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <img src="<?= !empty($candidate['profile_picture']) ? htmlspecialchars($candidate['profile_picture']) : 'assets/icons/default-profile.png' ?>" 
                                                 class="candidate-img me-3" alt="Candidate">
                                            <div>
                                                <h5 class="card-title mb-1"><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h5>
                                                <?php if (!empty($candidate['job_titles'])): ?>
                                                    <p class="text-muted mb-2"><?= htmlspecialchars(explode(',', $candidate['job_titles'])[0]) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="text-muted me-3"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($candidate['address']) ?></span>
                                            <?php if (!empty($candidate['education_levels'])): ?>
                                                <span class="text-muted"><i class="fas fa-graduation-cap me-1"></i> <?= htmlspecialchars(explode(',', $candidate['education_levels'])[0]) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($skills_array)): ?>
                                            <div class="mb-3">
                                                <?php foreach (array_slice($skills_array, 0, 5) as $skill): 
                                                    // Clean JSON formatting if present
                                                    $skill = str_replace(['{"value":"', '"}', '[', ']'], '', $skill);
                                                ?>
                                                    <span class="skill-badge"><?= htmlspecialchars($skill) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="candidate_profile.php?id=<?= $candidate['id'] ?>" class="btn btn-outline-primary btn-sm">View Profile</a>
                                            <?php if (!empty($candidate['cv'])): ?>
                                                <a href="<?= htmlspecialchars($candidate['cv']) ?>" class="btn btn-primary btn-sm" download>Download CV</a>
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
    <script src="assets/icons/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    
    <script>
        // Initialize Tagify for skills input
        const skillsInput = document.getElementById('skills');
        const tagify = new Tagify(skillsInput, {
            whitelist: <?= json_encode(array_column($all_skills, 'content')) ?>,
            dropdown: {
                enabled: 1,
                maxItems: 10,
                position: "input",
                closeOnSelect: false
            },
            originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
        });

        // Convert tags to comma-separated string before form submission
        document.getElementById('filterForm').addEventListener('submit', function() {
            const tags = tagify.value.map(tag => tag.value);
            skillsInput.value = tags.join(',');
        });
    </script>
</body>
</html>