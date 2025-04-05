<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password


    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['error' => "This email already exist"]);
    } else {
        $token = password_hash(session_id(), PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password,token) VALUES (?, ?, ?, ?,?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $token);

        if ($stmt->execute()) {
            $_SESSION['token'] = $token;
            echo json_encode(['token' => "$token"]);
        } else {
            echo json_encode(['error' => "Server Error"]);
        }
    }

    $stmt->close();
    $conn->close();
}
