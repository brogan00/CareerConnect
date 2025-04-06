<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed!');
}


if (isset($_SESSION['user_email'])) {
    $stmt = $conn->prepare("SELECT first_name,last_name,profile_picture FROM users WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($first_name,$last_name,$profile_picture);
    $stmt->fetch();

    if($profile_picture==null){
        $profile_picture = "./assets/images/hamidou.png";
    }
    $stmt->close(); 
}
?>

<!-- Navbar -->
<div class="navbar navbar-expand-lg px-3">
    <div class="mt-2">
        <a href="index.php" class="navbar-brand justify-content-center">
            <img
                class="mb-3"
                src="./assets/images/logo.png"
                alt="Logo"
                width="50" />
            <span class="navbar-brand fw-bold fs-2">CareerConnect</span>
        </a>
    </div>

    <button
        class="navbar-toggler ms-auto"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainmenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse collapse px-4 fw-bold" id="mainmenu">
        <ul
            class="navbar-nav align-items-center justify-content-center text-center text-black">
            <li class="nav-item">
                <a class="nav-link nav-links scale fs-5" href="job_search.php">
                    <img src="assets/icons/recherche.png" alt="Search" width="25" />
                    Search Jobs
                </a>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link nav-links scale fs-5"
                    href="career_resources.php">
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
        <?php
        if (isset($_SESSION['user_email'])) {
            echo "
                    <div
                    class='d-flex ms-auto nav-item justify-content-center d-block mt-2 mt-lg-0'>
                    <li class='nav-link'>
                        <a class='mx-2' href='profile.php'><img src='${profile_picture}' width='50px;'>
                        <div class='btn sign-in-btn'>${first_name} ${last_name}</div>
                        </a>
                        
                    </li>
                    <li class='nav-link me-1'>
                        <a class='btn sign-in-btn' href='connexion/do.logout.php'>Log out</a>
                    </li>
                </div>";
        } else {
            echo "
                <div
                    class='d-flex ms-auto nav-item justify-content-center d-block mt-2 mt-lg-0'>
                    <li class='nav-link me-1'>
                        <a class='btn sign-in-btn' href='connexion/signup.php'>Sign Up</a>
                    </li>
                    <li class='nav-link me-1'>
                        <a class='btn sign-in-btn' href='connexion/login.php'>Login</a>
                    </li>
                </div>";
        }
        ?>
    </div>
</div>