<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if already applied
    $check = $conn->prepare("SELECT id FROM application WHERE user_id = ? AND job_id = ?");
    $check->bind_param("ii", $user_id, $job_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        // Insert application
        $stmt = $conn->prepare("INSERT INTO application (status, user_id, job_id) VALUES ('pending', ?, ?)");
        $stmt->bind_param("ii", $user_id, $job_id);
        
        if ($stmt->execute()) {
            // Get recruiter ID for this job
            $recruiter_query = $conn->prepare("SELECT recruiter_id FROM job WHERE id = ?");
            $recruiter_query->bind_param("i", $job_id);
            $recruiter_query->execute();
            $result = $recruiter_query->get_result();
            $job_data = $result->fetch_assoc();
            $recruiter_id = $job_data['recruiter_id'];
            
            // Get candidate name
            $candidate_query = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $candidate_query->bind_param("i", $user_id);
            $candidate_query->execute();
            $candidate_result = $candidate_query->get_result();
            $candidate_data = $candidate_result->fetch_assoc();
            $candidate_name = $candidate_data['first_name'] . ' ' . $candidate_data['last_name'];
            
            // Create notification
            $message = "New application from " . $candidate_name;
            $notif = $conn->prepare("INSERT INTO notifications (recruiter_id, message, type, related_id, created_at) VALUES (?, ?, 'cv_submission', ?, NOW())");
            $notif->bind_param("isi", $recruiter_id, $message, $job_id);
            $notif->execute();
            
            $_SESSION['success'] = "Application submitted successfully!";
        } else {
            $_SESSION['error'] = "Error submitting application.";
        }
    } else {
        $_SESSION['error'] = "You've already applied to this job.";
    }
    
    header("Location: job_details.php?id=" . $job_id);
    exit();
}
?>