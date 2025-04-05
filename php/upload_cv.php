<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to upload a CV.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cv_content = file_get_contents($_FILES['cv']['tmp_name']);
    $cv_file_path = 'uploads/' . basename($_FILES['cv']['name']);

    // Move uploaded file to uploads directory
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $cv_file_path)) {
        // Insert into user_cv table
        $stmt = $conn->prepare("INSERT INTO user_cv (user_id, cv_file_path, cv_content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $cv_file_path, $cv_content);

        if ($stmt->execute()) {
            echo "CV uploaded successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading file.";
    }
}
?>

<!-- HTML Form for Uploading CV -->
<form method="POST" action="upload_cv.php" enctype="multipart/form-data">
    <input type="file" name="cv" required>
    <button type="submit">Upload CV</button>
</form>