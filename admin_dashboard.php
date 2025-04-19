<?php
// admin_dashboard.php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is admin

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
    /* Your existing styles remain the same */
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar (unchanged) -->
      
      <!-- Main Content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <div class="tab-content">
          <!-- Dashboard Tab (unchanged) -->
          
          <!-- Pending Jobs Tab (unchanged) -->
          
          <!-- Manage Candidates Tab (updated) -->
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
                              <button data-user-id="<?php echo $candidate['id']; ?>" 
                                      class="btn btn-sm btn-success approve-cv-btn" 
                                      <?php echo $cv_status == 'approved' ? 'disabled' : ''; ?>>
                                <i class="fas fa-check"></i> Approve CV
                              </button>
                              <button data-user-id="<?php echo $candidate['id']; ?>" 
                                      class="btn btn-sm btn-danger reject-cv-btn" 
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

          <!-- Other tabs remain unchanged -->
        </div>
      </main>
    </div>
  </div>

  <!-- CV Approval Modal -->
  <div class="modal fade" id="approveCvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Approve CV</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to approve this candidate's CV?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="confirmApproveCv">Approve CV</button>
        </div>
      </div>
    </div>
  </div>

  <!-- CV Rejection Modal -->
  <div class="modal fade" id="rejectCvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reject CV</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Please provide feedback to help the candidate improve their CV:</p>
          <textarea class="form-control" id="rejectionFeedback" rows="5" placeholder="Enter feedback for the candidate..." required></textarea>
          <div class="form-text">This feedback will be sent to the candidate.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmRejectCv">Reject with Feedback</button>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/JS/jquery-3.7.1.js"></script>
  <script src="assets/JS/bootstrap.min.js"></script>
  <script src="assets/icons/all.min.js"></script>
  <script>
    // CV Approval/Rejection System
    let currentCvUserId = 0;

    $(document).on('click', '.approve-cv-btn', function() {
      currentCvUserId = $(this).data('user-id');
      $('#approveCvModal').modal('show');
    });

    $(document).on('click', '.reject-cv-btn', function() {
      currentCvUserId = $(this).data('user-id');
      $('#rejectionFeedback').val('');
      $('#rejectCvModal').modal('show');
    });

    $('#confirmApproveCv').click(function() {
      $.ajax({
        url: "admin_actions.php",
        type: "POST",
        data: {
          action: "approve_cv",
          user_id: currentCvUserId
        },
        dataType: "json",
        success: function(data) {
          if (data.success) {
            showAlert('success', 'CV Approved', data.message);
            // Update UI
            $(`button[data-user-id="${currentCvUserId}"].approve-cv-btn`).prop('disabled', true);
            $(`button[data-user-id="${currentCvUserId}"].reject-cv-btn`).prop('disabled', true);
            $(`tr:has(button[data-user-id="${currentCvUserId}"]) td:nth-child(6) span`)
              .removeClass('bg-warning bg-danger')
              .addClass('bg-success')
              .text('approved');
            $('#approveCvModal').modal('hide');
          } else {
            showAlert('danger', 'Error', data.message);
          }
        },
        error: function() {
          showAlert('danger', 'Error', 'Failed to communicate with server');
        }
      });
    });

    $('#confirmRejectCv').click(function() {
      const feedback = $('#rejectionFeedback').val().trim();
      
      if (!feedback) {
        showAlert('warning', 'Feedback Required', 'Please provide feedback for the candidate.');
        return;
      }
      
      $.ajax({
        url: "admin_actions.php",
        type: "POST",
        data: {
          action: "reject_cv",
          user_id: currentCvUserId,
          feedback: feedback
        },
        dataType: "json",
        success: function(data) {
          if (data.success) {
            showAlert('success', 'CV Rejected', data.message);
            // Update UI
            $(`button[data-user-id="${currentCvUserId}"].approve-cv-btn`).prop('disabled', true);
            $(`button[data-user-id="${currentCvUserId}"].reject-cv-btn`).prop('disabled', true);
            $(`tr:has(button[data-user-id="${currentCvUserId}"]) td:nth-child(6) span`)
              .removeClass('bg-warning bg-success')
              .addClass('bg-danger')
              .text('rejected');
            $('#rejectCvModal').modal('hide');
          } else {
            showAlert('danger', 'Error', data.message);
          }
        },
        error: function() {
          showAlert('danger', 'Error', 'Failed to communicate with server');
        }
      });
    });

    // Helper function to show alerts
    function showAlert(type, title, message) {
      const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          <strong>${title}</strong> ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
      
      // Remove any existing alerts first
      $('.alert').alert('close');
      
      // Prepend to main content area
      $('main').prepend(alertHtml);
      
      // Auto-dismiss after 5 seconds
      setTimeout(() => {
        $('.alert').alert('close');
      }, 5000);
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

    // Other existing functions remain unchanged
  </script>
</body>
</html>