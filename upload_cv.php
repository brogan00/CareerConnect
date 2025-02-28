<?php
require_once "config/database.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["user_id"])) {
    if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != 0) {
        die("Error: No file uploaded or upload error.");
    }

    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    $filename = $_FILES['cv']['name'];
    $temp_name = $_FILES['cv']['tmp_name'];
    $file_size = $_FILES['cv']['size'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($file_ext, $allowed_extensions)) {
        die("Error: Invalid file type. Only PDF, DOC, and DOCX are allowed.");
    }

    // Validate file size
    if ($file_size > $max_file_size) {
        die("Error: File size exceeds 2MB.");
    }

    // Create a unique file name to prevent overwriting
    $new_filename = uniqid("cv_", true) . "." . $file_ext;
    $upload_dir = "uploads/cv/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($temp_name, $upload_dir . $new_filename)) {
        $stmt = $conn->prepare("INSERT INTO resumes (user_id, file_name) VALUES (?, ?)");
        if ($stmt->execute([$_SESSION["user_id"], $new_filename])) {
            echo "CV uploaded successfully!";
        } else {
            echo "Error saving file info to database.";
        }
    } else {
        echo "Error uploading CV.";
    }
} else {
    echo "Unauthorized access.";
}
