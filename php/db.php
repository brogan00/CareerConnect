<?php
// db.php
$host = '127.0.0.1'; // Database host
$user = 'root';      // Database username
$password = '';      // Database password
$database = 'careerconnect_db'; // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
