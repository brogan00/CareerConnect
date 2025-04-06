<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if (!$oldPassword || !$newPassword) {
    echo "Missing fields.";
    exit();
}

// Get current hashed password from DB
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

// Verify old password
if (!password_verify($oldPassword, $hashedPassword)) {
    echo "<script>alert('Old password is incorrect.'); window.location.href='profile.php';</script>";
    exit();
}

// Hash new password
$newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update DB
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $newHashedPassword, $email);
$stmt->execute();
$stmt->close();

echo "<script>alert('Password changed successfully.'); window.location.href='profile.php';</script>";
exit();
