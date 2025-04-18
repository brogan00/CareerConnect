<?php
// admin_actions.php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit();
}

$response = ['success' => false, 'message' => 'Invalid action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  switch ($action) {
    case 'approve_job':
      if (isset($_POST['job_id'])) {
        $jobId = (int)$_POST['job_id'];
        $stmt = $conn->prepare("UPDATE job SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $jobId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Job approved'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
    case 'reject_job':
      if (isset($_POST['job_id'])) {
        $jobId = (int)$_POST['job_id'];
        $stmt = $conn->prepare("UPDATE job SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $jobId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Job rejected'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
    case 'delete_job':
      if (isset($_POST['job_id'])) {
        $jobId = (int)$_POST['job_id'];
        $stmt = $conn->prepare("DELETE FROM job WHERE id = ?");
        $stmt->bind_param("i", $jobId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Job deleted'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
    case 'toggle_candidate_status':
      if (isset($_POST['user_id']) && isset($_POST['new_status'])) {
        $userId = (int)$_POST['user_id'];
        $newStatus = $_POST['new_status'];
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $userId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Candidate status updated'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
    case 'delete_candidate':
      if (isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        // First delete applications
        $conn->query("DELETE FROM applications WHERE user_id = $userId");
        // Then delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Candidate deleted'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
    case 'delete_recruiter':
      if (isset($_POST['recruiter_id'])) {
        $recruiterId = (int)$_POST['recruiter_id'];
        // First delete jobs posted by this recruiter
        $conn->query("DELETE FROM job WHERE recruiter_id = $recruiterId");
        // Then delete recruiter
        $stmt = $conn->prepare("DELETE FROM recruiter WHERE id = ?");
        $stmt->bind_param("i", $recruiterId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Recruiter deleted'];
        } else {
          $response = ['success' => false, 'message' => 'Database error'];
        }
      }
      break;
      
      require_once '../functions/notifications_functions.php';
        
        switch ($action) {
          // ... (keep your existing cases)
          
          case 'approve_cv':
            if (isset($_POST['user_id'])) {
              $userId = (int)$_POST['user_id'];
              
              // Update user status
              $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
              $stmt->bind_param("i", $userId);
              
              if ($stmt->execute()) {
                // Get user info
                $userStmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userStmt->bind_result($firstName, $lastName);
                $userStmt->fetch();
                $userStmt->close();
                
                // Notify user
                sendNotification($conn, [
                    'user_id' => $userId,
                    'message' => 'Your CV has been approved by admin',
                    'type' => 'cv_approval',
                    'related_id' => $userId
                ]);
                
                $response = ['success' => true, 'message' => 'CV approved'];
              } else {
                $response = ['success' => false, 'message' => 'Database error'];
              }
            }
            break;
            
          case 'reject_cv':
            if (isset($_POST['user_id'])) {
              $userId = (int)$_POST['user_id'];
              
              // Update user status
              $stmt = $conn->prepare("UPDATE users SET status = 'inactive', cv = NULL WHERE id = ?");
              $stmt->bind_param("i", $userId);
              
              if ($stmt->execute()) {
                // Get user info
                $userStmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userStmt->bind_result($firstName, $lastName);
                $userStmt->fetch();
                $userStmt->close();
                
                // Notify user
                sendNotification($conn, [
                    'user_id' => $userId,
                    'message' => 'Your CV has been rejected by admin',
                    'type' => 'cv_rejection',
                    'related_id' => $userId
                ]);
                
                $response = ['success' => true, 'message' => 'CV rejected'];
              } else {
                $response = ['success' => false, 'message' => 'Database error'];
              }
            }
            break;
            
          default:
            $response = ['success' => false, 'message' => 'Unknown action'];
        }
      }
}

header('Content-Type: application/json');
echo json_encode($response);
?>