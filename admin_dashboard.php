<?php
// admin_dashboard.php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is admin
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: connexion/login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - CareerConnect</title>
  <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/icons/all.min.css" />
  <link rel="stylesheet" href="assets/CSS/style.css" />
  <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
  <style>
    .sidebar {
      min-height: 100vh;
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
    }

    .sidebar .nav-link {
      color: #495057;
      border-radius: 0;
    }

    .sidebar .nav-link.active {
      background-color: #0d6efd;
      color: white;
    }

    .sidebar .nav-link:hover:not(.active) {
      background-color: #e9ecef;
    }

    .dashboard-card {
      transition: transform 0.3s;
    }

    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .badge-pending {
      background-color: #ffc107;
      color: #212529;
    }

    .badge-approved {
      background-color: #198754;
      color: white;
    }

    .badge-rejected {
      background-color: #dc3545;
      color: white;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <h4>Admin Dashboard</h4>
            <hr>
          </div>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#pending-jobs" data-bs-toggle="tab">
                <i class="fas fa-briefcase me-2"></i>Pending Jobs
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#manage-candidates" data-bs-toggle="tab">
                <i class="fas fa-users me-2"></i>Manage Candidates
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#manage-recruiters" data-bs-toggle="tab">
                <i class="fas fa-user-tie me-2"></i>Manage Recruiters
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#approved-jobs" data-bs-toggle="tab">
                <i class="fas fa-check-circle me-2"></i>Approved Jobs
              </a>
            </li>
            <li class="nav-item mt-4">
              <a class="nav-link text-danger" href="connexion/do.logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <div class="tab-content">
          <!-- Dashboard Tab -->
          <div class="tab-pane fade show active" id="dashboard">
            <h2 class="h3 mb-4">Admin Dashboard</h2>

            <div class="row mb-4">
              <div class="col-md-4 mb-3">
                <div class="card dashboard-card bg-primary text-white">
                  <div class="card-body">
                    <h5 class="card-title">Total Candidates</h5>
                    <?php
                    $total_candidates = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'candidat'")->fetch_assoc();
                    ?>
                    <h2 class="card-text"><?php echo $total_candidates['count']; ?></h2>
                    <a href="#manage-candidates" class="text-white" data-bs-toggle="tab">View Details</a>
                  </div>
                </div>
              </div>

              <div class="col-md-4 mb-3">
                <div class="card dashboard-card bg-warning text-dark">
                  <div class="card-body">
                    <h5 class="card-title">Total Recruiters</h5>
                    <?php
                    $total_recruiters = $conn->query("SELECT COUNT(*) as count FROM recruiter")->fetch_assoc();
                    ?>
                    <h2 class="card-text"><?php echo $total_recruiters['count']; ?></h2>
                    <a href="#manage-recruiters" class="text-dark" data-bs-toggle="tab">View Details</a>
                  </div>
                </div>
              </div>

              <div class="col-md-4 mb-3">
                <div class="card dashboard-card bg-success text-white">
                  <div class="card-body">
                    <h5 class="card-title">Active Jobs</h5>
                    <?php
                    $total_jobs = $conn->query("SELECT COUNT(*) as count FROM job")->fetch_assoc();
                    ?>
                    <h2 class="card-text"><?php echo $total_jobs['count']; ?></h2>
                    <a href="#approved-jobs" class="text-white" data-bs-toggle="tab">View Details</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      // Get recent job postings
                      $recent_jobs = $conn->query("
                        SELECT 'Job' as type, 'Posted' as action, r.first_name as name, j.created_at as date 
                        FROM job j 
                        JOIN recruiter r ON j.recruiter_id = r.id 
                        ORDER BY j.created_at DESC LIMIT 3
                      ");

                      // Get recent candidate registrations
                      $recent_candidates = $conn->query("
                        SELECT 'Candidate' as type, 'Registered' as action, 
                        CONCAT(first_name, ' ', last_name) as name, created_at as date 
                        FROM users
                        WHERE type = 'candidat' 
                        ORDER BY created_at DESC LIMIT 3
                      ");

                      // Combine and display results
                      $recent_activity = [];
                      while ($row = $recent_jobs->fetch_assoc()) $recent_activity[] = $row;
                      while ($row = $recent_candidates->fetch_assoc()) $recent_activity[] = $row;

                      // Sort by date
                      usort($recent_activity, function ($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                      });

                      foreach (array_slice($recent_activity, 0, 5) as $activity):
                      ?>
                        <tr>
                          <td><?php echo $activity['type']; ?></td>
                          <td><?php echo $activity['action']; ?></td>
                          <td><?php echo $activity['name']; ?></td>
                          <td><?php echo date('M d, Y H:i', strtotime($activity['date'])); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Jobs Tab -->
          <div class="tab-pane fade" id="pending-jobs">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h3 mb-0">Pending Job Postings</h2>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Salary</th>
                        <th>Posted By</th>
                        <th>Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $pending_jobs = $conn->query("
                        SELECT j.id, j.title, j.type_contract, j.salary, j.created_at,
                        CONCAT(r.first_name, ' ', r.last_name) as recruiter_name, c.name as company_name
                        FROM job j
                        JOIN recruiter r ON j.recruiter_id = r.id
                        JOIN company c ON r.company_id = c.id
                        ORDER BY j.created_at DESC
                      ");

                      while ($job = $pending_jobs->fetch_assoc()):
                      ?>
                        <tr>
                          <td><?php echo $job['id']; ?></td>
                          <td><?php echo $job['title']; ?></td>
                          <td><?php echo $job['company_name']; ?></td>
                          <td><?php echo $job['type_contract']; ?></td>
                          <td><?php echo $job['salary'] ? '$' . number_format($job['salary'], 2) : 'Negotiable'; ?></td>
                          <td><?php echo $job['recruiter_name']; ?></td>
                          <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                          <td>
                            <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                              <i class="fas fa-eye"></i> View
                            </a>
                            <button onclick="approveJob(<?php echo $job['id']; ?>)" class="btn btn-sm btn-success">
                              <i class="fas fa-check"></i> Approve
                            </button>
                            <button onclick="rejectJob(<?php echo $job['id']; ?>)" class="btn btn-sm btn-danger">
                              <i class="fas fa-times"></i> Reject
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Manage Candidates Tab -->
          <div class="tab-pane fade" id="manage-candidates">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h3 mb-0">Manage Candidates</h2>
              <div>
                <div class="input-group" style="width: 300px;">
                  <input type="text" id="candidateSearch" class="form-control" placeholder="Search candidates...">
                  <button class="btn btn-outline-secondary" type="button" id="searchCandidateButton">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
                <div class="mt-2">
                  <button class="btn btn-sm btn-outline-warning" onclick="filterCandidates('cv_pending')">
                    <i class="fas fa-file-upload"></i> Pending CVs
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" onclick="filterCandidates('all')">
                    <i class="fas fa-users"></i> All Candidates
                  </button>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>CV</th>
                        <th>CV Status</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $candidates = $conn->query("SELECT * FROM users WHERE type = 'candidat' ORDER BY created_at DESC");

                      while ($candidate = $candidates->fetch_assoc()):
                        $cv_status = $candidate['cv_status'] ?? 'pending';
                      ?>
                        <tr>
                          <td><?php echo $candidate['id']; ?></td>
                          <td><?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?></td>
                          <td><?php echo $candidate['email']; ?></td>
                          <td><?php echo $candidate['phone'] ?? 'N/A'; ?></td>
                          <td>
                            <?php if ($candidate['cv']): ?>
                              <a href="<?php echo $candidate['cv']; ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-file-pdf"></i> View CV
                              </a>
                            <?php else: ?>
                              No CV
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge bg-<?php 
                              echo $cv_status == 'approved' ? 'success' : 
                                   ($cv_status == 'rejected' ? 'danger' : 'warning'); ?>">
                              <?php echo ucfirst($cv_status); ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge bg-<?php echo $candidate['status'] == 'active' ? 'success' : 'secondary'; ?>">
                              <?php echo ucfirst($candidate['status']); ?>
                            </span>
                          </td>
                          <td><?php echo date('M d, Y', strtotime($candidate['created_at'])); ?></td>
                          <td>
                            <?php if ($candidate['cv']): ?>
                              <button onclick="approveCV(<?php echo $candidate['id']; ?>)" 
                                      class="btn btn-sm btn-success" 
                                      <?php echo $cv_status == 'approved' ? 'disabled' : ''; ?>>
                                <i class="fas fa-check"></i> Approve CV
                              </button>
                              <button onclick="rejectCV(<?php echo $candidate['id']; ?>)" 
                                      class="btn btn-sm btn-danger" 
                                      <?php echo $cv_status == 'rejected' ? 'disabled' : ''; ?>>
                                <i class="fas fa-times"></i> Reject CV
                              </button>
                            <?php endif; ?>
                            <button onclick="toggleCandidateStatus(<?php echo $candidate['id']; ?>, '<?php echo $candidate['status']; ?>')"
                              class="btn btn-sm btn-<?php echo $candidate['status'] == 'active' ? 'warning' : 'success'; ?>">
                              <i class="fas fa-<?php echo $candidate['status'] == 'active' ? 'ban' : 'check'; ?>"></i>
                              <?php echo $candidate['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                            </button>
                            <button onclick="confirmDeleteCandidate(<?php echo $candidate['id']; ?>)" class="btn btn-sm btn-danger">
                              <i class="fas fa-trash"></i> Delete
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Manage Recruiters Tab -->
          <div class="tab-pane fade" id="manage-recruiters">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h3 mb-0">Manage Recruiters</h2>
              <div class="input-group" style="width: 300px;">
                <input type="text" id="recruiterSearch" class="form-control" placeholder="Search recruiters...">
                <button class="btn btn-outline-secondary" type="button" id="searchRecruiterButton">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Joined</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $recruiters = $conn->query("
                        SELECT r.id, CONCAT(r.first_name, ' ', r.last_name) as full_name, r.email, r.created_at, c.name as company_name
                        FROM recruiter r
                        LEFT JOIN company c ON r.company_id = c.id
                        ORDER BY r.created_at DESC
                      ");

                      while ($recruiter = $recruiters->fetch_assoc()):
                      ?>
                        <tr>
                          <td><?php echo $recruiter['id']; ?></td>
                          <td><?php echo $recruiter['full_name']; ?></td>
                          <td><?php echo $recruiter['email']; ?></td>
                          <td><?php echo $recruiter['company_name'] ?? 'Independent'; ?></td>
                          <td><?php echo date('M d, Y', strtotime($recruiter['created_at'])); ?></td>
                          <td>
                            <button onclick="confirmDeleteRecruiter(<?php echo $recruiter['id']; ?>)" class="btn btn-sm btn-danger">
                              <i class="fas fa-trash"></i> Delete
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Approved Jobs Tab -->
          <div class="tab-pane fade" id="approved-jobs">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h3 mb-0">Approved Job Postings</h2>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Salary</th>
                        <th>Posted By</th>
                        <th>Expires</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $approved_jobs = $conn->query("
                        SELECT j.id, j.title, j.type_contract, j.salary, j.expiration_date, j.created_at,
                        CONCAT(r.first_name, ' ', r.last_name) as recruiter_name, c.name as company_name
                        FROM job j
                        JOIN recruiter r ON j.recruiter_id = r.id
                        JOIN company c ON r.company_id = c.id
                        ORDER BY j.expiration_date ASC
                      ");

                      while ($job = $approved_jobs->fetch_assoc()):
                      ?>
                        <tr>
                          <td><?php echo $job['id']; ?></td>
                          <td><?php echo $job['title']; ?></td>
                          <td><?php echo $job['company_name']; ?></td>
                          <td><?php echo $job['type_contract']; ?></td>
                          <td><?php echo $job['salary'] ? '$' . number_format($job['salary'], 2) : 'Negotiable'; ?></td>
                          <td><?php echo $job['recruiter_name']; ?></td>
                          <td><?php echo date('M d, Y', strtotime($job['expiration_date'])); ?></td>
                          <td>
                            <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                              <i class="fas fa-eye"></i> View
                            </a>
                            <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="btn btn-sm btn-danger">
                              <i class="fas fa-trash"></i> Delete
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="assets/JS/jquery-3.7.1.js"></script>
  <script src="assets/JS/bootstrap.min.js"></script>
  <script src="assets/icons/all.min.js"></script>
  <script>
    // Approve Job
    function approveJob(jobId) {
      if (confirm("Are you sure you want to approve this job posting?")) {
        $.post("admin_actions.php", {
          action: "approve_job",
          job_id: jobId
        }, function(data) {
          if (data.success) {
            alert("Job approved successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Reject Job
    function rejectJob(jobId) {
      if (confirm("Are you sure you want to reject this job posting?")) {
        $.post("admin_actions.php", {
          action: "reject_job",
          job_id: jobId
        }, function(data) {
          if (data.success) {
            alert("Job rejected successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Delete Job
    function deleteJob(jobId) {
      if (confirm("Are you sure you want to delete this job posting? This cannot be undone.")) {
        $.post("admin_actions.php", {
          action: "delete_job",
          job_id: jobId
        }, function(data) {
          if (data.success) {
            alert("Job deleted successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Toggle Candidate Status
    function toggleCandidateStatus(userId, currentStatus) {
      const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
      if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this candidate?`)) {
        $.post("admin_actions.php", {
          action: "toggle_candidate_status",
          user_id: userId,
          new_status: newStatus
        }, function(data) {
          if (data.success) {
            alert("Candidate status updated successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Delete Candidate
    function confirmDeleteCandidate(userId) {
      if (confirm("Are you sure you want to delete this candidate? All their data (applications, CV, etc.) will also be deleted.")) {
        $.post("admin_actions.php", {
          action: "delete_candidate",
          user_id: userId
        }, function(data) {
          if (data.success) {
            alert("Candidate deleted successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Delete Recruiter
    function confirmDeleteRecruiter(recruiterId) {
      if (confirm("Are you sure you want to delete this recruiter? All their job postings will also be deleted.")) {
        $.post("admin_actions.php", {
          action: "delete_recruiter",
          recruiter_id: recruiterId
        }, function(data) {
          if (data.success) {
            alert("Recruiter deleted successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Approve CV
    function approveCV(userId) {
      if (confirm("Are you sure you want to approve this CV?")) {
        $.post("admin_actions.php", {
          action: "approve_cv",
          user_id: userId
        }, function(data) {
          if (data.success) {
            alert("CV approved successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Reject CV
    function rejectCV(userId) {
      if (confirm("Are you sure you want to reject this CV?")) {
        $.post("admin_actions.php", {
          action: "reject_cv",
          user_id: userId
        }, function(data) {
          if (data.success) {
            alert("CV rejected successfully!");
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        }, "json");
      }
    }

    // Filter Candidates
    function filterCandidates(filter) {
      if (filter === 'cv_pending') {
        $("#manage-candidates table tbody tr").each(function() {
          const cvStatus = $(this).find("td:nth-child(6) span").text().toLowerCase();
          $(this).toggle(cvStatus === 'pending');
        });
      } else {
        $("#manage-candidates table tbody tr").show();
      }
    }

    // Search functionality
    $(document).ready(function() {
      // Candidate search
      $("#candidateSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#manage-candidates table tbody tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });

      $("#searchCandidateButton").click(function() {
        $("#candidateSearch").trigger("keyup");
      });

      // Recruiter search
      $("#recruiterSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#manage-recruiters table tbody tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });

      $("#searchRecruiterButton").click(function() {
        $("#recruiterSearch").trigger("keyup");
      });
    });
  </script>
</body>

</html>