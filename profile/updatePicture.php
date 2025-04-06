<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Check if file uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['profile_picture']['type'];

    if (!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Only JPG, PNG, or GIF allowed.'); window.location.href='profile.php';</script>";
        exit();
    }

    $filename = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetDir = "uploads/profile_pictures/";
    $targetFile = $targetDir . $filename;

    // Create folder if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Move the uploaded file
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
        // Save to DB
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
        $stmt->bind_param("ss", $targetFile, $email);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Profile picture updated!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error uploading file.'); window.location.href='profile.php';</script>";
    }
} else {
    echo "<script>alert('No file uploaded or upload error.'); window.location.href='profile.php';</script>";
}
