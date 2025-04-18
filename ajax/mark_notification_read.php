<?php
// ajax/mark_notification_read.php
require_once '../connexion/config.php';
require_once '../connexion/check_auth.php';
require_once '../functions/notifications_functions.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit();
}

$notificationId = (int)$_POST['id'];

// Verify the notification belongs to the current user
$stmt = $conn->prepare("SELECT id FROM notifications WHERE id = ? AND (user_id = ? OR admin_id = ? OR admin_id IS NULL)");
$userId = $_SESSION['user_type'] == 'candidat' ? $_SESSION['user_id'] : null;
$adminId = $_SESSION['user_type'] == 'admin' ? $_SESSION['user_id'] : null;
$stmt->bind_param("iii", $notificationId, $userId, $adminId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Notification not found']);
    exit();
}

// Mark as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
$stmt->bind_param("i", $notificationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>