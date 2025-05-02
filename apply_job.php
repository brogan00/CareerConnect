<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);

    $user_id_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_id_query->bind_param("s", $_SESSION['user_email']);
    $user_id_query->execute();
    $result = $user_id_query->get_result();
    $user_data = $result->fetch_assoc();
    $user_id = $user_data['id'];

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
            $notif = $conn->prepare("INSERT INTO notifications (recruiter_id,user_id, message, type, related_id, created_at) VALUES (?, ?,?, 'cv_submission', ?, NOW())");
            $notif->bind_param("iisi", $recruiter_id,$user_id, $message, $job_id);
            $notif->execute();
            
            $_SESSION['success_message'] = "Application submitted successfully!";
        } else {
            $_SESSION['error_message'] = "Error submitting application.";
        }
    } else {
        $_SESSION['error_message'] = "You've already applied to this job.";
    }
    
    header("Location: job_search.php");
    exit();
}
?>