<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $candidateId = intval($_POST['id']);
    
    // First delete related records to maintain referential integrity
    $tables = ['education', 'experience', 'skills', 'application'];
    foreach ($tables as $table) {
        $conn->query("DELETE FROM $table WHERE user_id = $candidateId");
    }
    
    // Then delete the candidate
    $query = "DELETE FROM users WHERE id = ? AND type = 'candidat'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $candidateId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>