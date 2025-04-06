<?php
include "connexion/config.php";
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Get updated values from the form
$first_name = $_POST['stname'] ?? '';
$last_name = $_POST['ltname'] ?? '';
$new_email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$sexe = $_POST['sexe'] ?? '';
$about = $_POST['about'] ?? '';

// Simple validation (optional, you can improve it)
if (empty($first_name) || empty($last_name) || empty($new_email)) {
    echo "Please fill in all required fields.";
    exit();
}

// Update user in the database
$query = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=?, sexe=?, about=? WHERE email=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssssss", $first_name, $last_name, $new_email, $phone, $address, $sexe, $about, $email);

if ($stmt->execute()) {
    // Update session email if changed
    $_SESSION['user_email'] = $new_email;
    header("Location: profile.php?msg=Profile updated successfully&type=success");
} else {
    echo "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
