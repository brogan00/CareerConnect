<?php
require_once "config/database.php";

$search = isset($_GET['q']) ? $_GET['q'] : '';

$stmt = $conn->prepare("SELECT * FROM jobs WHERE title LIKE ? OR company LIKE ?");
$stmt->execute(["%$search%", "%$search%"]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/json");
echo json_encode($jobs);
