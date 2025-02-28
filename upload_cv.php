<?php
require_once "config/database.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["user_id"])) {
    $filename = $_FILES['cv']['name'];
    $temp_name = $_FILES['cv']['tmp_name'];
    $upload_dir = "uploads/cv/";

    if (move_uploaded_file($temp_name, $upload_dir . $filename)) {
        $stmt = $conn->prepare("INSERT INTO resumes (user_id, file_name) VALUES (?, ?)");
        $stmt->execute([$_SESSION["user_id"], $filename]);
        echo "CV uploaded successfully!";
    } else {
        echo "Error uploading CV.";
    }
}
