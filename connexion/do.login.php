<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        //$token = password_hash(session_id(), PASSWORD_DEFAULT);
        $_SESSION['user_email'] = $email;
        //$stmt = $conn->prepare("UPDATE users SET token = ? WHERE email = ?");
        //$stmt->bind_param("ss", $token, $email);
        //$stmt->execute();
        //echo json_encode(['token' => "$token"]);
        echo json_encode(['success' => "Login successful"]);
    } else {
        echo json_encode(['error' => "Invalid Email and/Or password"]);
    }

    $stmt->close();
    $conn->close();
}
