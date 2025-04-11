<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $type = $_POST['type'];


    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM recruiter WHERE email = ?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['error' => "This email already exist"]);
    } else {
        //$token = password_hash(session_id(), PASSWORD_DEFAULT);

        if ($type == "candidat" || $type == "recruiter") {

            if ($type == "candidat") {
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);
            } else {
                $stmt = $conn->prepare("INSERT INTO recruiter (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);
            }
            if ($stmt->execute()) {
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = $type;
                //echo json_encode(['token' => "$token"]);
                echo json_encode(['success' => "Account created successfully"]);
            } else {
                echo json_encode(['error' => "Server Error"]);
            }
            $stmt->close();
            $conn->close();
        }
    }
}
