<?php
include "connexion/config.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$notifications = [];

$result = $conn->query("
    SELECT id, message, type, created_at, is_read 
    FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 5
");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Mark as read
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
}

echo json_encode($notifications);