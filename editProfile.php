<?php
include "connexion/config.php";
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {



if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];
    if (isset($_SESSION['user_type'])) {
        $first_name = $_POST['stname'] ?? '';
        $last_name = $_POST['ltname'] ?? '';
        $new_email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        if ($_SESSION['user_type'] == "candidat" || $_SESSION['user_type'] == "admin") {
            $phone = $_POST['phone'] ?? '';
            $sexe = $_POST['sexe'] ?? '';
            $about = $_POST['about'] ?? '';
        }
    }else {
        die('Invalid user type!');
    }
}

/*
// Get updated values from the form
$first_name = $_POST['stname'] ?? '';
$last_name = $_POST['ltname'] ?? '';
$new_email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$sexe = $_POST['sexe'] ?? '';
$about = $_POST['about'] ?? '';
*/

// Simple validation (optional, you can improve it)
$first_name = trim($first_name);
$last_name = trim($last_name);
$new_email = trim($new_email);
$address = trim($address);
$phone = trim($phone);
if (empty($first_name) || empty($last_name) || empty($new_email)) {
    echo "Please fill in all required fields.";
    exit();
}

// Update user in the database

if (isset($_SESSION['user_email'])) {
    if (isset($_SESSION['user_type'])) {
      if ($_SESSION['user_type'] == "recruiter") {  
        $query = "UPDATE recruiter SET first_name=?, last_name=?, email=?, address=? WHERE email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $first_name, $last_name, $new_email, $address, $email);
        } else if ($_SESSION['user_type'] == "candidat" || $_SESSION['user_type'] == "admin") {
        $query = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=?, sexe=?, about=? WHERE email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", $first_name, $last_name, $new_email, $phone, $address, $sexe, $about, $email);
      } else {
        die('Invalid user type!');
      }
    }
  }


if ($stmt->execute()) {
    // Update session email if changed
    $_SESSION['user_email'] = $new_email;
    header("Location: profile.php?msg=Profile updated successfully&type=success");
} else {
    echo "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();
}else{
    die('Invalid request method!');
}
?>
