<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $keywords = $_GET['keywords'];
    $location = $_GET['location'];

    // Search for jobs
    $stmt = $conn->prepare("SELECT * FROM post WHERE title LIKE ? OR content LIKE ?");
    $search_keywords = "%$keywords%";
    $stmt->bind_param("ss", $search_keywords, $search_keywords);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<div><h3>{$row['title']}</h3><p>{$row['content']}</p></div>";
    }

    $stmt->close();
}
?>

<!-- HTML Form for Searching Jobs -->
<form method="GET" action="search_jobs.php">
    <input type="text" name="keywords" placeholder="Job Title or Keywords" required>
    <input type="text" name="location" placeholder="Location">
    <button type="submit">Search</button>
</form>