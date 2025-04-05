<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit a review.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_id = $_POST['company_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Insert into company_review table
    $stmt = $conn->prepare("INSERT INTO company_review (company_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $company_id, $_SESSION['user_id'], $rating, $comment);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- HTML Form for Submitting a Review -->
<form method="POST" action="submit_review.php">
    <input type="number" name="company_id" placeholder="Company ID" required>
    <input type="number" name="rating" placeholder="Rating (1-5)" min="1" max="5" required>
    <textarea name="comment" placeholder="Your Review" required></textarea>
    <button type="submit">Submit Review</button>
</form>