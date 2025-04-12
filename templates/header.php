<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed!');
}

if (isset($_SESSION['user_email'])) {
    $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE email = ? UNION SELECT first_name, last_name, profile_picture FROM recruiter WHERE email = ? ");
    $stmt->bind_param("ss", $_SESSION['user_email'], $_SESSION['user_email']);
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
                    <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell-fill"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" id="notificationList">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><a class="dropdown-item" href="#">No new notifications</a></li>
                </ul>
            </li>

            <script>
            // Load notifications
            function loadNotifications() {
                fetch('get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        const list = document.getElementById('notificationList');
                        const count = document.getElementById('notificationCount');
                        
                        // Clear existing items except header
                        while (list.children.length > 1) {
                            list.removeChild(list.lastChild);
                        }
                        
                        if (data.error) {
                            const item = document.createElement('li');
                            item.innerHTML = `<a class="dropdown-item text-danger" href="#">${data.error}</a>`;
                            list.appendChild(item);
                        } else if (data.length === 0) {
                            const item = document.createElement('li');
                            item.innerHTML = '<a class="dropdown-item" href="#">No new notifications</a>';
                            list.appendChild(item);
                            count.style.display = 'none';
                        } else {
                            count.textContent = data.filter(n => !n.is_read).length;
                            count.style.display = data.filter(n => !n.is_read).length > 0 ? 'block' : 'none';
                            
                            data.forEach(notification => {
                                const item = document.createElement('li');
                                const className = notification.is_read ? '' : 'fw-bold';
                                item.innerHTML = `
                                    <a class="dropdown-item ${className}" href="#">
                                        <div>${notification.message}</div>
                                        <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                                    </a>`;
                                list.appendChild(item);
                            });
                            
                            const divider = document.createElement('li');
                            divider.innerHTML = '<hr class="dropdown-divider">';
                            list.appendChild(divider);
                            
                            const viewAll = document.createElement('li');
                            viewAll.innerHTML = '<a class="dropdown-item text-center" href="notifications.php">View All</a>';
                            list.appendChild(viewAll);
                        }
                    });
            }

            // Load notifications on page load
            document.addEventListener('DOMContentLoaded', loadNotifications);

            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
            </script>
                    <?php endif; ?>
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
            
    </div>
</div>