<?php
include "connexion/config.php";
session_start();

// Check if user came from signup
if (!isset($_SESSION['new_user_id'])) {
    header("Location: signup.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = filter_input(INPUT_POST, 'account_type', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['new_user_id'];

    try {
        // Update user type
        $stmt = $conn->prepare("UPDATE users SET type = ? WHERE id = ?");
        $stmt->execute([$account_type, $user_id]);

        // Create profile in appropriate table
        if ($account_type === 'recruiter') {
            $stmt = $conn->prepare("
                INSERT INTO recruiter (email, password, user_id, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $password = $_SESSION['new_user_password'] ?? ''; // You should store this securely
            $stmt->execute([$_SESSION['new_user_email'], $password, $user_id]);
        } else {
            // For candidates, we already have them in users table
            // You can add more candidate-specific data here if needed
        }

        // Clear session variables
        unset($_SESSION['new_user_id']);
        unset($_SESSION['new_user_email']);
        unset($_SESSION['new_user_password']);

        // Set login session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $account_type;
        $_SESSION['email'] = $_SESSION['new_user_email'];

        // Redirect to appropriate dashboard
        if ($account_type === 'recruiter') {
            header("Location: recruiter_dashboard.php");
        } else {
            header("Location: candidate_dashboard.php");
        }
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: select_account_type.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Account Type</title>
    <link href="assets/CSS/bootstrap.min.css" rel="stylesheet">
    <style>
        .account-type-card {
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .account-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .account-type-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Select Your Account Type</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <input type="radio" name="account_type" id="candidate" value="candidate" class="d-none" checked>
                                    <label for="candidate" class="card account-type-card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person fs-1 text-primary"></i>
                                            <h4 class="mt-3">Candidate</h4>
                                            <p class="text-muted">I'm looking for job opportunities</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="radio" name="account_type" id="recruiter" value="recruiter" class="d-none">
                                    <label for="recruiter" class="card account-type-card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-briefcase fs-1 text-primary"></i>
                                            <h4 class="mt-3">Recruiter</h4>
                                            <p class="text-muted">I want to post job offers</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Continue</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Make cards selectable
        document.querySelectorAll('.account-type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.account-type-card').forEach(c => {
                    c.classList.remove('selected');
                });
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
    </script>
</body>
</html>