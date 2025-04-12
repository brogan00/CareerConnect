<?php
include "connexion/config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$notifications = [];

$result = $conn->query("
    SELECT id, message, type, created_at, is_read 
    FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC
");

if ($result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    
    // Mark all as read
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
</head>
<body>
    <?php include "templates/header.php" ?>

    <div class="container mt-5">
        <h2 class="mb-4">Your Notifications</h2>
        
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no notifications.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <a href="#" class="list-group-item list-group-item-action <?= $notification['is_read'] ? '' : 'list-group-item-primary' ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                            <small><?= date('M d, Y h:i A', strtotime($notification['created_at'])) ?></small>
                        </div>
                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $notification['type'])) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include "templates/footer.php" ?>
</body>
</html>