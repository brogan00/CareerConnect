<?php
// functions/notifications_functions.php

/**
 * Send a notification to a user or admin
 * @param mysqli $conn Database connection
 * @param array $options Notification options
 * @return bool True on success, false on failure
 */
function sendNotification($conn, $options) {
    $defaults = [
        'user_id' => null,
        'admin_id' => null,
        'message' => '',
        'type' => null,
        'related_id' => null
    ];
    
    $options = array_merge($defaults, $options);
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, admin_id, message, type, related_id) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissi", 
        $options['user_id'],
        $options['admin_id'],
        $options['message'],
        $options['type'],
        $options['related_id']
    );
    
    return $stmt->execute();
}

/**
 * Get unread notification count for a user or admin
 * @param mysqli $conn Database connection
 * @param int|null $user_id User ID (for candidates)
 * @param int|null $admin_id Admin ID (for admins)
 * @return int Number of unread notifications
 */
function getUnreadNotificationCount($conn, $user_id = null, $admin_id = null) {
    if ($user_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
    } elseif ($admin_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE admin_id = ? AND is_read = 0");
        $stmt->bind_param("i", $admin_id);
    } else {
        // For general admin notifications (no specific admin assigned)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE admin_id IS NULL AND is_read = 0");
    }
    
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    return $count;
}

/**
 * Get notifications for a user or admin
 * @param mysqli $conn Database connection
 * @param int|null $user_id User ID (for candidates)
 * @param int|null $admin_id Admin ID (for admins)
 * @param int $limit Number of notifications to return
 * @return array Array of notifications
 */
function getNotifications($conn, $user_id = null, $admin_id = null, $limit = 10) {
    $notifications = [];
    
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id, message, type, related_id, created_at, is_read 
                               FROM notifications 
                               WHERE user_id = ? 
                               ORDER BY created_at DESC 
                               LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
    } elseif ($admin_id) {
        $stmt = $conn->prepare("SELECT id, message, type, related_id, created_at, is_read 
                               FROM notifications 
                               WHERE admin_id = ? 
                               ORDER BY created_at DESC 
                               LIMIT ?");
        $stmt->bind_param("ii", $admin_id, $limit);
    } else {
        // For general admin notifications (no specific admin assigned)
        $stmt = $conn->prepare("SELECT id, message, type, related_id, created_at, is_read 
                               FROM notifications 
                               WHERE admin_id IS NULL 
                               ORDER BY created_at DESC 
                               LIMIT ?");
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

/**
 * Mark notifications as read
 * @param mysqli $conn Database connection
 * @param int|null $user_id User ID (for candidates)
 * @param int|null $admin_id Admin ID (for admins)
 * @return bool True on success, false on failure
 */
function markNotificationsAsRead($conn, $user_id = null, $admin_id = null) {
    if ($user_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
    } elseif ($admin_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE admin_id = ? AND is_read = 0");
        $stmt->bind_param("i", $admin_id);
    } else {
        // For general admin notifications (no specific admin assigned)
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE admin_id IS NULL AND is_read = 0");
    }
    
    return $stmt->execute();
}

/**
 * Get the appropriate link for a notification based on its type
 * @param array $notification Notification data
 * @return string URL for the notification
 */
function getNotificationLink($notification) {
    switch ($notification['type']) {
        case 'cv_submission':
            return 'admin/cv_approvals.php';
        case 'cv_approval':
            return 'profile.php';
        case 'cv_rejection':
            return 'profile.php';
        case 'job_application':
            return 'admin/job_applications.php?id=' . $notification['related_id'];
        default:
            return '#';
    }
}
?>