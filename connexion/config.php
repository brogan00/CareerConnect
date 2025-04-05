<?php
$host = "localhost"; 
$user = "root";  // Change if you have a different MySQL user
$pass = "";      // Add password if applicable
$dbname = "my_website"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
