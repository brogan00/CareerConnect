<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Candidate Profile - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <style>
        /* Custom Styles for Profile Page */
        .profile-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .profile-card img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
        }

        .profile-card h2 {
            color: #3a0ca3;
            font-weight: bold;
        }

        .profile-card p {
            color: #555;
        }

        .profile-card .btn-primary {
            background: #3a0ca3;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .profile-card .btn-primary:hover {
            background: #480ca8;
        }

        .profile-card .btn-success {
            background: #28a745;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .profile-card .btn-success:hover {
            background: #218838;
        }

        .profile-card .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card text-center">
                    <!-- Candidate Image -->
                    <img src="/assets/icons/recherche.png" alt="Candidate Image" />

                    <!-- Candidate Name -->
                    <h2>John Doe</h2>

                    <!-- Candidate Title -->
                    <p class="text-muted">Software Engineer</p>

                    <!-- Candidate Details -->
                    <div class="text-start">
                        <p><strong>Location:</strong> San Francisco, CA</p>
                        <p><strong>Experience:</strong> 5 years</p>
                        <p><strong>Skills:</strong> Python, JavaScript, React, Node.js</p>
                        <p><strong>Education:</strong> Bachelor's in Computer Science</p>
                        <p><strong>About:</strong> John is a passionate software engineer with expertise in building scalable web applications. He loves solving complex problems and contributing to open-source projects.</p>
                    </div>

                    <!-- Buttons -->
                    <div class="btn-group">
                        <a href="candidate1.html" class="btn btn-primary">View Profile</a>
                        <button class="btn btn-success" onclick="contactCandidate()">Contact Candidate</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include "templates/footer.php" ?>

    <script>
        // Function to handle "Contact Candidate" button click
        function contactCandidate() {
            // Example: Open an email client with a pre-filled email
            const email = "john.doe@example.com"; // Replace with the candidate's email
            const subject = "Opportunity at CareerConnect";
            const body = "Hello John, I came across your profile on CareerConnect and would like to discuss an opportunity with you.";
            window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

            // Alternatively, you can display a contact form or modal
            // alert("Contact form will be displayed here.");
        }
    </script>
</body>

</html>