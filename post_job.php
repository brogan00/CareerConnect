<?php
require_once "config/database.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["user_id"])) {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];

    $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, salary, posted_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $company, $location, $salary, $_SESSION["user_id"]]);

    echo "Job posted successfully!";
} else {
    echo "Unauthorized request.";
}
