<?php
// ajax/mark_all_notifications_read.php
require_once '../connexion/config.php';
require_once '../connexion/check_auth.php';
require_once '../functions/notifications_functions.php';

header('Content-Type: application/json');

if ($_SESSION['user_type'] == "candidat") {
    $success = markNotificationsAsRead($conn, $_SESSION['user_id']);
} elseif ($_SESSION['user_type'] == "admin") {
    $success = markNotificationsAsRead($conn, null, $_SESSION['user_id']);
} else {
    $success = false;
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
}
?>