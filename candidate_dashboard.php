<?php
// Start session and check authentication
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'candidat') {
    header("Location: connexion/login.php");
    exit();
}

// Database connection
require_once "connexion/config.php";

// Get candidate info
$email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$candidate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$candidate) {
    die("Candidate not found");
}

$user_id = $candidate['id'];

// Get applications count
$count_stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(status = 'pending') as pending,
    SUM(status = 'approved') as approved,
    SUM(status = 'rejected') as rejected
    FROM application WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$counts = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

// Get recent applications
$apps_stmt = $conn->prepare("SELECT a.*, j.title, c.name as company 
    FROM application a
    JOIN job j ON a.job_id = j.id
    JOIN recruiter r ON j.recruiter_id = r.id
    JOIN company c ON r.company_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5");
$apps_stmt->bind_param("i", $user_id);
$apps_stmt->execute();
$applications = $apps_stmt->get_result();
$apps_stmt->close();

// Time elapsed function
function timeAgo($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60) return "just now";
    $diff = round($diff/60);
    if ($diff < 60) return "$diff min ago";
    $diff = round($diff/60);
    if ($diff < 24) return "$diff hours ago";
    $diff = round($diff/24);
    if ($diff < 7) return "$diff days ago";
    return date('M j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        .dashboard {
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            flex: 1;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            font-size: 24px;
        }
        .stat-box p {
            margin: 5px 0 0;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: normal;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1>CareerConnect</h1>
            <div>
                <img src="<?= $candidate['profile_picture'] ?? 'assets/images/default-profile.jpg' ?>" class="profile-img" alt="Profile">
                <span><?= htmlspecialchars($candidate['first_name'].' '.$candidate['last_name']) ?></span>
                <a href="connexion/do.logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard" style="max-width: 1200px; margin: 0 auto;">
        <h2>Candidate Dashboard</h2>
        
        <div class="stats">
            <div class="stat-box">
                <h3><?= $counts['total'] ?? 0 ?></h3>
                <p>Total Applications</p>
            </div>
            <div class="stat-box">
                <h3><?= $counts['pending'] ?? 0 ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-box">
                <h3><?= $counts['approved'] ?? 0 ?></h3>
                <p>Approved</p>
            </div>
            <div class="stat-box">
                <h3><?= $counts['rejected'] ?? 0 ?></h3>
                <p>Rejected</p>
            </div>
        </div>

        <div class="card">
            <h3>Recent Applications</h3>
            <?php if ($applications->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($app['title']) ?></td>
                                <td><?= htmlspecialchars($app['company']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $app['status'] ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                </td>
                                <td><?= timeAgo($app['applied_at']) ?></td>
                                <td>
                                    <a href="job_details.php?id=<?= $app['job_id'] ?>" class="btn btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't applied to any jobs yet. <a href="job_search.php">Browse jobs</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>