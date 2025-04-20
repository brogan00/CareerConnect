<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: connexion/login.php");
  exit();
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$success_msg = isset($_GET['success']) ? $_GET['success'] : null;
$error_msg = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - CareerConnect</title>
  <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icons/all.min.css">
  <link rel="stylesheet" href="assets/CSS/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8">
  <style>
    :root {
      --sidebar-width: 280px;
      --primary-color: #0d6efd;
      --success-color: #198754;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .sidebar {
      width: var(--sidebar-width);
      min-height: 100vh;
      background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
      border-right: 1px solid #dee2e6;
      position: fixed;
      z-index: 1000;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .main-content {
      margin-left: var(--sidebar-width);
      width: calc(100% - var(--sidebar-width));
      background-color: #f8fafc;
      min-height: 100vh;
    }
    
    .nav-link {
      color: #495057;
      border-radius: 5px;
      margin: 2px 5px;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    
    .nav-link:hover {
      background-color: rgba(13, 110, 253, 0.1);
    }
    
    .nav-link.active {
      background-color: var(--primary-color);
      color: white !important;
      box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
    }
    
    .nav-link i {
      width: 20px;
      text-align: center;
      margin-right: 10px;
    }
    
    .badge-count {
      font-size: 0.7rem;
      padding: 3px 6px;
      margin-left: auto;
    }
    
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 20px;
    }
    
    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    }
    
    .card-header {
      background-color: white;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      font-weight: 600;
      border-radius: 10px 10px 0 0 !important;
    }
    
    .table-responsive {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table {
      margin-bottom: 0;
    }
    
    .table th {
      background-color: #f8f9fa;
      border-top: none;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      color: #6c757d;
    }
    
    .table td {
      vertical-align: middle;
      border-top: 1px solid #f1f1f1;
    }
    
    .status-badge {
      padding: 5px 10px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    .badge-pending {
      background-color: var(--warning-color);
      color: #212529;
    }
    
    .badge-approved {
      background-color: var(--success-color);
      color: white;
    }
    
    .badge-rejected {
      background-color: var(--danger-color);
      color: white;
    }
    
    .action-btn {
      padding: 5px 10px;
      font-size: 0.8rem;
      border-radius: 5px;
      margin: 0 2px;
    }
    
    .search-box {
      position: relative;
      max-width: 300px;
    }
    
    .search-box i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    
    .search-box input {
      padding-left: 35px;
      border-radius: 50px;
      border: 1px solid #dee2e6;
    }
    
    .animate-fade {
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .dashboard-card {
      border-left: 4px solid var(--primary-color);
    }
    
    .dashboard-card h2 {
      font-weight: 700;
      color: var(--primary-color);
    }
    
    .dashboard-card a {
      text-decoration: none;
      font-weight: 500;
      color: var(--primary-color);
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="sidebar p-0">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4 mt-3">
            <h4 class="fw-bold">Admin Dashboard</h4>
            <hr class="mx-3">
          </div>
          <ul class="nav flex-column px-3" id="adminTabs">
            <li class="nav-item mb-1">
              <a class="nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" 
                 href="?tab=dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a>
            </li>
            <li class="nav-item mb-1">
              <a class="nav-link <?= $active_tab === 'pending-jobs' ? 'active' : '' ?>" 
                 href="?tab=pending-jobs">
                <i class="fas fa-hourglass-half"></i> Pending Jobs
                <?php
                $pending_count = $conn->query("SELECT COUNT(*) FROM job WHERE status = 'pending'")->fetch_row()[0];
                if ($pending_count > 0): ?>
                  <span class="badge bg-danger badge-count"><?= $pending_count ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li class="nav-item mb-1">
              <a class="nav-link <?= $active_tab === 'candidates' ? 'active' : '' ?>" 
                 href="?tab=candidates">
                <i class="fas fa-users"></i> Candidates
              </a>
            </li>
            <li class="nav-item mb-1">
              <a class="nav-link <?= $active_tab === 'recruiters' ? 'active' : '' ?>" 
                 href="?tab=recruiters">
                <i class="fas fa-user-tie"></i> Recruiters
              </a>
            </li>
            <li class="nav-item mb-1">
              <a class="nav-link <?= $active_tab === 'approved-jobs' ? 'active' : '' ?>" 
                 href="?tab=approved-jobs">
                <i class="fas fa-check-circle"></i> Approved Jobs
              </a>
            </li>
            <li class="nav-item mt-4 mb-1">
              <a class="nav-link text-danger" href="connexion/do.logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <main class="main-content p-4 animate__animated animate__fadeIn">
        <?php if ($success_msg): ?>
          <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn">
            <?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
          <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn">
            <?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <?php
        // Include the appropriate content based on active tab
        $tab_files = [
          'dashboard' => 'admin_tabs/dashboard.php',
          'pending-jobs' => 'admin_tabs/pending_jobs.php',
          'candidates' => 'admin_tabs/candidates.php',
          'recruiters' => 'admin_tabs/recruiters.php',
          'approved-jobs' => 'admin_tabs/approved_jobs.php'
        ];
        
        if (isset($tab_files[$active_tab])) {
          include $tab_files[$active_tab];
        } else {
          include 'admin_tabs/dashboard.php';
        }
        ?>
      </main>
    </div>
  </div>

  <script src="assets/JS/jquery-3.7.1.js"></script>
  <script src="assets/JS/bootstrap.bundle.min.js"></script>
  <script src="assets/icons/all.min.js"></script>
  <script>
    // Initialize tooltips
    $(document).ready(function() {
      $('[data-bs-toggle="tooltip"]').tooltip();
      
      // Search functionality
      $('.search-input').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        const table = $(this).closest('.card').find('table');
        table.find('tbody tr').filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
      
      // Animate tab changes
      $('.nav-link').on('click', function() {
        $('.main-content').addClass('animate__fadeOut');
        setTimeout(() => {
          $('.main-content').removeClass('animate__fadeOut').addClass('animate__fadeIn');
        }, 300);
      });
    });
  </script>
</body>
</html>