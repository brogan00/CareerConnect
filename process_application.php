<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in as recruiter
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'recruiter') {
    header("Location: connexion/login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: recruiter_dashboard.php");
    exit();
}

$application_id = intval($_GET['id']);
$action = $_GET['action'];

// Verify the application belongs to this recruiter
$stmt = $conn->prepare("
    SELECT a.id, a.user_id, j.title AS job_title, u.first_name, u.last_name, u.email
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ? AND j.recruiter_id = ?
");
$stmt->bind_param("ii", $application_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: recruiter_dashboard.php?error=invalid_application");
    exit();
}

$application = $result->fetch_assoc();
$stmt->close();

// Update application status
$status = $action === 'accept' ? 'accepted' : 'rejected';
$update_stmt = $conn->prepare("UPDATE application SET status = ? WHERE id = ?");
$update_stmt->bind_param("si", $status, $application_id);

if ($update_stmt->execute()) {
    // Create notification for candidate
    $message = $status === 'accepted' 
        ? "Your application for '{$application['job_title']}' has been accepted!" 
        : "Your application for '{$application['job_title']}' was not accepted";
    
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, related_id) 
                                VALUES (?, ?, 'application', ?)");
    $notif_stmt->bind_param("isi", $application['user_id'], $message, $application_id);
    $notif_stmt->execute();
    $notif_stmt->close();
    
    $_SESSION['success_message'] = "Application has been " . $status . " successfully";
} else {
    $_SESSION['error_message'] = "Failed to update application status";
}

$update_stmt->close();
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'recruiter_dashboard.php'));
exit();
?>