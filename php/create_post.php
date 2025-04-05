<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a job post.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $company_id = $_POST['company_id'];
    $category_id = $_POST['category_id'];
    $domain_id = $_POST['domain_id'];
    $job_type_id = $_POST['job_type_id'];

    // Insert into post table
    $stmt = $conn->prepare("INSERT INTO post (title, content, company_id, user_id, category_id, domain_id, job_type_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiiii", $title, $content, $company_id, $_SESSION['user_id'], $category_id, $domain_id, $job_type_id);

    if ($stmt->execute()) {
        echo "Job post created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- HTML Form for Creating a Job Post -->
<form method="POST" action="create_post.php">
    <input type="text" name="title" placeholder="Job Title" required>
    <textarea name="content" placeholder="Job Description" required></textarea>
    <input type="number" name="company_id" placeholder="Company ID" required>
    <input type="number" name="category_id" placeholder="Category ID" required>
    <input type="number" name="domain_id" placeholder="Domain ID" required>
    <input type="number" name="job_type_id" placeholder="Job Type ID" required>
    <button type="submit">Create Job Post</button>
</form>