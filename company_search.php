<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Search functionality
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$companies = [];

if (!empty($searchTerm)) {
    // Search for distinct companies matching the term
    $stmt = $conn->prepare("SELECT DISTINCT c.* FROM company c WHERE c.name LIKE ? OR c.location LIKE ?");
    $searchParam = "%$searchTerm%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $companies = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Get all distinct companies if no search term
    $result = $conn->query("SELECT DISTINCT c.* FROM company c ORDER BY c.created_at DESC");
    $companies = $result->fetch_all(MYSQLI_ASSOC);
}

// Count jobs for each company
$companyJobsCount = [];
foreach ($companies as $company) {
    $stmt = $conn->prepare("SELECT COUNT(*) as job_count FROM job j 
                           JOIN recruiter r ON j.recruiter_id = r.id 
                           WHERE r.company_id = ? AND j.status = 'approved'");
    $stmt->bind_param("i", $company['id']);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $countData = $countResult->fetch_assoc();
    $companyJobsCount[$company['id']] = $countData['job_count'];
}

// Default profile picture path
$defaultProfilePic = "./assets/images/hamidou.png";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Company Search - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <style>
        .company-logo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .company-card {
            transition: all 0.3s ease;
            height: 100%;
        }
        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .job-count-badge {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <div class="container mt-5">
        <h2 class="text-center">Find Companies</h2>
        <div class="container search-container mt-5 w-75">
            <form action="" method="GET">
                <div class="row align-items-center justify-content-center">
                    <div class="col-11 col-md-8 col-lg-9 mb-2 mb-md-0">
                        <img src="assets/icons/batiment.png" class="col-1" alt="Company Icon">
                        <input class="col-10" type="text" name="search" placeholder="Search for companies..." 
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <button type="submit" class="btn search-button col-11 col-md-4 col-lg-3 mt-md-2 mt-sm-2">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Company Listings -->
    <div class="container mt-5">
        <?php if (!empty($searchTerm)): ?>
            <h3 class="text-center mb-4">Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h3>
        <?php else: ?>
            <h3 class="text-center mb-4">All Companies</h3>
        <?php endif; ?>
        
        <?php if (empty($companies)): ?>
            <div class="alert alert-info text-center">
                No companies found. Try a different search term.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($companies as $company): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card company-card">
                            <div class="card-body text-center">
                                <!-- Using your default hamidou.png as profile picture -->
                                <img src="<?php echo $defaultProfilePic; ?>" alt="<?php echo htmlspecialchars($company['name']); ?>" class="company-logo">
                                <h5 class="card-title"><?php echo htmlspecialchars($company['name']); ?></h5>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['location']); ?>
                                </p>
                                <?php if (!empty($company['description'])): ?>
                                    <p class="card-text"><?php echo substr(htmlspecialchars($company['description']), 0, 100); ?>...</p>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <?php if (!empty($company['website'])): ?>
                                        <a href="<?php echo htmlspecialchars($company['website']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                            <i class="fas fa-globe"></i> Website
                                        </a>
                                    <?php endif; ?>
                                    <a href="job_search.php?company_id=<?php echo $company['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-briefcase"></i> View Jobs 
                                        <span class="job-count-badge"><?php echo $companyJobsCount[$company['id']]; ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include "templates/footer.php" ?>
    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/icons/all.min.js"></script>
</body>
</html>