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
  
  // Include notification functions
  require_once 'functions/notifications_functions.php'; // Adjust path as needed
  
  switch ($action) {
    case 'approve_job':
      if (isset($_POST['job_id'])) {
        $jobId = (int)$_POST['job_id'];
        $stmt = $conn->prepare("UPDATE job SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $jobId);
        if ($stmt->execute()) {
          $response = ['success' => true, 'message' => 'Job approved'];
        } else {
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
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
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
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
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
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
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
      }
      break;
      
    case 'delete_candidate':
      if (isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        // Start transaction
        $conn->begin_transaction();
        
        try {
          // First delete applications
          $stmt1 = $conn->prepare("DELETE FROM applications WHERE user_id = ?");
          $stmt1->bind_param("i", $userId);
          $stmt1->execute();
          
          // Then delete user
          $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
          $stmt2->bind_param("i", $userId);
          $stmt2->execute();
          
          $conn->commit();
          $response = ['success' => true, 'message' => 'Candidate deleted'];
        } catch (Exception $e) {
          $conn->rollback();
          $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
      }
      break;
      
    case 'delete_recruiter':
      if (isset($_POST['recruiter_id'])) {
        $recruiterId = (int)$_POST['recruiter_id'];
        // Start transaction
        $conn->begin_transaction();
        
        try {
          // First delete jobs posted by this recruiter
          $stmt1 = $conn->prepare("DELETE FROM job WHERE recruiter_id = ?");
          $stmt1->bind_param("i", $recruiterId);
          $stmt1->execute();
          
          // Then delete recruiter
          $stmt2 = $conn->prepare("DELETE FROM recruiter WHERE id = ?");
          $stmt2->bind_param("i", $recruiterId);
          $stmt2->execute();
          
          $conn->commit();
          $response = ['success' => true, 'message' => 'Recruiter deleted'];
        } catch (Exception $e) {
          $conn->rollback();
          $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
      }
      break;
      
      case 'approve_cv':
        if (isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
            // Update user status and mark CV as approved
            $stmt = $conn->prepare("UPDATE users SET status = 'active', cv_status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                // Get user info
                $userStmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userStmt->bind_result($userEmail, $firstName);
                $userStmt->fetch();
                $userStmt->close();
                
                // Notify user
                sendNotification($conn, [
                    'user_id' => $userId,
                    'message' => 'Your CV has been approved by admin',
                    'type' => 'cv_approval',
                    'related_id' => $userId
                ]);
                
                // Send email notification
                $subject = "Your CV Has Been Approved";
                $message = "Dear $firstName,\n\nYour CV has been reviewed and approved by our admin team.\n\nYou can now apply for jobs on our platform.";
                sendEmail($userEmail, $subject, $message);
                
                $response = ['success' => true, 'message' => 'CV approved and candidate notified'];
            } else {
                $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
            }
        }
        break;
        
    case 'reject_cv':
        if (isset($_POST['user_id']) && isset($_POST['feedback'])) {
            $userId = (int)$_POST['user_id'];
            $feedback = trim($_POST['feedback']);
            
            if (empty($feedback)) {
                $response = ['success' => false, 'message' => 'Feedback is required when rejecting a CV'];
                break;
            }
            
            // Update user status and mark CV as rejected
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive', cv_status = 'rejected', cv = NULL WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                // Get user info
                $userStmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userStmt->bind_result($userEmail, $firstName);
                $userStmt->fetch();
                $userStmt->close();
                
                // Store feedback in database
                $feedbackStmt = $conn->prepare("INSERT INTO cv_feedback (user_id, admin_id, feedback, created_at) VALUES (?, ?, ?, NOW())");
                $adminId = $_SESSION['user_id']; // Assuming you store admin ID in session
                $feedbackStmt->bind_param("iis", $userId, $adminId, $feedback);
                $feedbackStmt->execute();
                $feedbackStmt->close();
                
                // Notify user
                sendNotification($conn, [
                    'user_id' => $userId,
                    'message' => 'Your CV has been rejected by admin. Feedback: ' . $feedback,
                    'type' => 'cv_rejection',
                    'related_id' => $userId
                ]);
                
                // Send email notification with feedback
                $subject = "Your CV Requires Modifications";
                $message = "Dear $firstName,\n\nYour CV has been reviewed but requires some modifications before approval.\n\n";
                $message .= "Admin Feedback:\n$feedback\n\n";
                $message .= "Please update your CV and resubmit for review.";
                sendEmail($userEmail, $subject, $message);
                
                $response = ['success' => true, 'message' => 'CV rejected and feedback sent to candidate'];
            } else {
                $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'User ID and feedback are required'];
        }
        break;
    default:
      $response = ['success' => false, 'message' => 'Unknown action'];
  }
}

header('Content-Type: application/json');
echo json_encode($response);
?>