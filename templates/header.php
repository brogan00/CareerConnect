
<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed!');
}

if (isset($_SESSION['user_email'])) {
    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] == "recruiter") {
            $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture FROM recruiter WHERE email = ?");
        }else if ($_SESSION['user_type'] == "candidat") {
            $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE email = ?");
        }else if($_SESSION['user_type'] == "admin") {
            $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE email = ?");
        } else {
            die('Invalid user type!');
        }
    }
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($first_name, $last_name, $profile_picture);
    $stmt->fetch();
    $stmt->close();

    if (!$profile_picture) {
        $profile_picture = "./assets/images/hamidou.png";
    }
}
?>

<!-- Navbar -->
<div class="navbar navbar-expand-lg px-3">
    <div class="mt-2">
        <a href="index.php" class="navbar-brand d-flex align-items-center">
            <img class="mb-2 me-2" src="./assets/images/logo.png" alt="Logo" width="50" />
            <span class="navbar-brand fw-bold fs-2">CareerConnect</span>
        </a>
    </div>

    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainmenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse collapse px-4 fw-bold" id="mainmenu">
        <ul class="navbar-nav align-items-center justify-content-center text-center text-black">
            <li class="nav-item">
                <a class="nav-link nav-links scale fs-5" href="job_search.php">
                    <img src="assets/icons/recherche.png" alt="Search" width="25" />
                    Search Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-links scale fs-5" href="career_resources.php">
                    <img src="assets/icons/poste-vacant.png" alt="Post" width="25" />
                    Search for a Candidate
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-links scale fs-5" href="company_search.php">
                    <img src="assets/icons/batiment.png" alt="Company" width="25" />
                    Find Companies
                </a>
            </li>
        </ul>

        <?php if (isset($_SESSION['user_email'])): ?>
            <div class="d-flex ms-auto align-items-center mt-2 mt-lg-0">
                <a href="profile.php" class="d-flex align-items-center text-decoration-none me-3">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" width="45" height="45" class="rounded-circle border border-secondary shadow-sm me-2" alt="Profile" />
                    <span class="btn sign-in-btn"><?php echo htmlspecialchars($first_name . " " . $last_name); ?></span>
                </a>
                <a class="btn sign-in-btn" href="connexion/do.logout.php">Log out</a>
            </div>
        <?php else: ?>
            <div class="d-flex ms-auto align-items-center mt-2 mt-lg-0">
                <a class="btn sign-in-btn me-2" href="connexion/signup.php">Sign Up</a>
                <a class="btn sign-in-btn" href="connexion/login.php">Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>