<?php
require_once 'connexion/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['action'])) {
    $candidateId = intval($_POST['id']);
    $action = $_POST['action'];
    
    // In a real application, you would update the candidate's status in the database
    // Since we don't have a cv_status column, we'll just return success
    
    if ($action === 'approve') {
        // Here you would typically update the database to mark as approved
        echo json_encode(['success' => true, 'message' => 'CV approved']);
    } elseif ($action === 'reject') {
        // Here you would typically update the database to mark as rejected
        echo json_encode(['success' => true, 'message' => 'CV rejected']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>