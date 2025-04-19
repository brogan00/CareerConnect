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
    case 'approve_cv':
      if (isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        
        // Verify user exists and is a candidate
        $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ? AND type = 'candidat'");
        $checkUser->bind_param("i", $userId);
        $checkUser->execute();
        
        if (!$checkUser->fetch()) {
          $response = ['success' => false, 'message' => 'Candidate not found'];
          break;
        }
        
        // Update CV status
        $stmt = $conn->prepare("UPDATE users SET cv_status = 'approved', status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
          // Create notification
          $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, admin_id, message, type, related_id) 
                                      VALUES (?, ?, ?, 'cv_approval', ?)");
          $message = "Your CV has been approved by the admin team";
          $adminId = $_SESSION['user_id'] ?? 0;
          $notifStmt->bind_param("iisi", $userId, $adminId, $message, $userId);
          $notifStmt->execute();
          
          $response = ['success' => true, 'message' => 'CV approved successfully'];
        } else {
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
      }
      break;
      
    case 'reject_cv':
      if (isset($_POST['user_id']) && isset($_POST['feedback'])) {
        $userId = (int)$_POST['user_id'];
        $feedback = trim($_POST['feedback']);
        
        // Verify user exists and is a candidate
        $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ? AND type = 'candidat'");
        $checkUser->bind_param("i", $userId);
        $checkUser->execute();
        
        if (!$checkUser->fetch()) {
          $response = ['success' => false, 'message' => 'Candidate not found'];
          break;
        }
        
        if (empty($feedback)) {
          $response = ['success' => false, 'message' => 'Feedback is required'];
          break;
        }
        
        // Update CV status
        $stmt = $conn->prepare("UPDATE users SET cv_status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
          // Create notification
          $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, admin_id, message, type, related_id) 
                                      VALUES (?, ?, ?, 'cv_rejection', ?)");
          $message = "Your CV has been rejected. Feedback: " . $feedback;
          $adminId = $_SESSION['user_id'] ?? 0;
          $notifStmt->bind_param("iisi", $userId, $adminId, $message, $userId);
          $notifStmt->execute();
          
          $response = ['success' => true, 'message' => 'CV rejected with feedback'];
        } else {
          $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
      } else {
        $response = ['success' => false, 'message' => 'User ID and feedback are required'];
      }
      break;
      
    // Other existing cases remain unchanged
    default:
      $response = ['success' => false, 'message' => 'Unknown action'];
  }
}

header('Content-Type: application/json');
echo json_encode($response);
?>