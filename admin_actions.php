<?php
// admin_actions.php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is admin
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'approve_job':
                if (isset($_POST['job_id'])) {
                    $job_id = intval($_POST['job_id']);
                    $conn->begin_transaction();
                    
                    try {
                        // Update job status
                        $stmt = $conn->prepare("UPDATE job SET status = 'approved' WHERE id = ?");
                        $stmt->bind_param("i", $job_id);
                        $stmt->execute();
                        
                        // Create notification for recruiter
                        $notification_message = "Your job posting has been approved";
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_id, admin_id, message, type, related_id, created_at)
                            SELECT j.recruter_id, ?, ?, 'job_approval', ?, NOW()
                            FROM job j WHERE j.id = ?
                        ");
                        $admin_id = $_SESSION['user_id'];
                        $stmt->bind_param("isii", $admin_id, $notification_message, $job_id, $job_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response = ['success' => true, 'message' => 'Job approved'];
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response = ['success' => false, 'message' => 'Error approving job: ' . $e->getMessage()];
                    }
                }
                break;
                
            case 'reject_job':
                if (isset($_POST['job_id'])) {
                    $job_id = intval($_POST['job_id']);
                    $conn->begin_transaction();
                    
                    try {
                        // Update job status
                        $stmt = $conn->prepare("UPDATE job SET status = 'rejected' WHERE id = ?");
                        $stmt->bind_param("i", $job_id);
                        $stmt->execute();
                        
                        // Create notification for recruiter
                        $notification_message = "Your job posting has been rejected";
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_id, admin_id, message, type, related_id, created_at)
                            SELECT j.recruter_id, ?, ?, 'job_rejection', ?, NOW()
                            FROM job j WHERE j.id = ?
                        ");
                        $admin_id = $_SESSION['user_id'];
                        $stmt->bind_param("isii", $admin_id, $notification_message, $job_id, $job_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response = ['success' => true, 'message' => 'Job rejected'];
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response = ['success' => false, 'message' => 'Error rejecting job: ' . $e->getMessage()];
                    }
                }
                break;
                
            case 'delete_job':
                if (isset($_POST['job_id'])) {
                    $job_id = intval($_POST['job_id']);
                    $conn->begin_transaction();
                    
                    try {
                        // Delete applications first
                        $stmt = $conn->prepare("DELETE FROM application WHERE job_id = ?");
                        $stmt->bind_param("i", $job_id);
                        $stmt->execute();
                        
                        // Then delete the job
                        $stmt = $conn->prepare("DELETE FROM job WHERE id = ?");
                        $stmt->bind_param("i", $job_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response = ['success' => true, 'message' => 'Job deleted'];
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response = ['success' => false, 'message' => 'Error deleting job'];
                    }
                }
                break;
                
            case 'toggle_candidate_status':
                if (isset($_POST['user_id']) && isset($_POST['new_status'])) {
                    $user_id = intval($_POST['user_id']);
                    $new_status = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
                    
                    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_status, $user_id);
                    if ($stmt->execute()) {
                        $response = ['success' => true, 'message' => 'Candidate status updated'];
                    } else {
                        $response = ['success' => false, 'message' => 'Database error'];
                    }
                }
                break;
                
            case 'delete_candidate':
                if (isset($_POST['user_id'])) {
                    $user_id = intval($_POST['user_id']);
                    $conn->begin_transaction();
                    
                    try {
                        // Delete applications
                        $stmt = $conn->prepare("DELETE FROM application WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        // Delete education
                        $stmt = $conn->prepare("DELETE FROM education WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        // Delete experience
                        $stmt = $conn->prepare("DELETE FROM experience WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        // Delete skills
                        $stmt = $conn->prepare("DELETE FROM skills WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        // Finally delete the user
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response = ['success' => true, 'message' => 'Candidate deleted'];
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response = ['success' => false, 'message' => 'Error deleting candidate'];
                    }
                }
                break;
                
            case 'delete_recruiter':
                if (isset($_POST['recruiter_id'])) {
                    $recruiter_id = intval($_POST['recruiter_id']);
                    $conn->begin_transaction();
                    
                    try {
                        // First get all jobs by this recruiter
                        $job_ids = [];
                        $stmt = $conn->prepare("SELECT id FROM job WHERE recruter_id = ?");
                        $stmt->bind_param("i", $recruiter_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $job_ids[] = $row['id'];
                        }
                        
                        // Delete applications for these jobs
                        if (!empty($job_ids)) {
                            $placeholders = implode(',', array_fill(0, count($job_ids), '?'));
                            $types = str_repeat('i', count($job_ids));
                            
                            $stmt = $conn->prepare("DELETE FROM application WHERE job_id IN ($placeholders)");
                            $stmt->bind_param($types, ...$job_ids);
                            $stmt->execute();
                        }
                        
                        // Delete jobs
                        $stmt = $conn->prepare("DELETE FROM job WHERE recruter_id = ?");
                        $stmt->bind_param("i", $recruiter_id);
                        $stmt->execute();
                        
                        // Delete recruiter
                        $stmt = $conn->prepare("DELETE FROM recruiter WHERE id = ?");
                        $stmt->bind_param("i", $recruiter_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response = ['success' => true, 'message' => 'Recruiter deleted'];
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response = ['success' => false, 'message' => 'Error deleting recruiter'];
                    }
                }
                break;
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response);
?>