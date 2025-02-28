<?php
$host = "localhost";
$db_name = "careerconnect";
$username = "root";
$password = "";

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");
