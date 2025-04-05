<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to apply for a job.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];

    // Insert into job_application table
    $stmt = $conn->prepare("INSERT INTO job_application (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $post_id);

    if ($stmt->execute()) {
        echo "Application submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- HTML Form for Applying to a Job -->
<form method="POST" action="apply_job.php">
    <input type="number" name="post_id" placeholder="Job Post ID" required>
    <button type="submit">Apply Now</button>
</form>