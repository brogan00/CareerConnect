<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

if (!isset($_SESSION['user_email'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    
    // Get user ID from email
    $user_email = $_SESSION['user_email'];
    $user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_query->bind_param("s", $user_email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    
    if ($user_result->num_rows == 0) {
        exit(json_encode(['success' => false, 'error' => 'User not found']));
    }
    
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];
    
    // Mark notification as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>