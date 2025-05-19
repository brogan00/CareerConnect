<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Get candidate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: candidate_search.php");
    exit();
}

$candidate_id = intval($_GET['id']);

// Get candidate basic info
$query = "SELECT * FROM users WHERE id = ? AND type = 'candidat'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    header("Location: candidate_search.php");
    exit();
}

// Get candidate education
$education = [];
$query = "SELECT * FROM education WHERE user_id = ? ORDER BY end_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $education[] = $row;
}
$stmt->close();

// Get candidate experience
$experience = [];
$query = "SELECT * FROM experience WHERE user_id = ? ORDER BY end_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $experience[] = $row;
}
$stmt->close();

// Get candidate skills
$skills = [];
$query = "SELECT * FROM skills WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $skills[] = $row['content'];
}
$stmt->close();

// Get reviews for the candidate
$reviews = [];
$query = "SELECT cr.*, 
                 CASE 
                     WHEN cr.reviewer_type = 'recruiter' THEN CONCAT(r.first_name, ' ', r.last_name)
                     ELSE cr.reviewer_name 
                 END as reviewer_display_name,
                 r.profile_picture as reviewer_profile_pic
          FROM candidate_reviews cr
          LEFT JOIN recruiter r ON cr.recruiter_id = r.id
          WHERE cr.candidate_id = ?
          ORDER BY cr.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Calculate average rating
$average_rating = 0;
if (!empty($reviews)) {
    $total_ratings = array_sum(array_column($reviews, 'rating'));
    $average_rating = $total_ratings / count($reviews);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
   
    $reviewer_type = 'public';
    $recruiter_id = 0;
    
    // If the reviewer is a logged-in recruiter
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'recruiter') {
        $reviewer_type = 'recruiter';
        $recruiter_id = $_SESSION['user_email'];
        $reviewer_name = ''; // Will use recruiter's name from DB
        $reviewer_email = ''; // Will use recruiter's email from DB
    }
    
    if ($rating > 0 && $rating <= 5) {
        $query = "INSERT INTO candidate_reviews 
                  (candidate_id, reviewer_type, reviewer_name, reviewer_email, recruiter_id, rating, comment)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssiis", 
            $candidate_id, 
            $reviewer_type,
            $reviewer_name,
            $reviewer_email,
            $recruiter_id,
            $rating,
            $comment
        );
        
       
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?> - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #f9fafb;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #374151;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
            color: white;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        
        .profile-picture {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }
        
        .profile-picture:hover {
            transform: scale(1.05);
        }
        
        .section-card {
            background-color: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .skill-badge {
            background-color: #e0e7ff;
            color: var(--primary-color);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .skill-badge:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 30px;
            border-left: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-left: 2px solid transparent;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -11px;
            top: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: white;
            border: 4px solid var(--primary-color);
            z-index: 2;
        }
        
        .timeline-date {
            background-color: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px 28px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 12px 28px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        
        /* Rating styles */
        .rating-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .average-rating {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-right: 15px;
        }
        
        .stars {
            display: flex;
            margin-right: 15px;
        }
        
        .star {
            color: var(--warning-color);
            font-size: 1.5rem;
            margin-right: 3px;
        }
        
        .total-reviews {
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Review card styles */
        .review-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #e5e7eb;
        }
        
        .reviewer-info {
            flex: 1;
        }
        
        .reviewer-name {
            font-weight: 600;
            margin-bottom: 2px;
            color: var(--dark-color);
        }
        
        .review-date {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        .review-rating {
            display: flex;
            margin-bottom: 10px;
        }
        
        .review-comment {
            color: #4b5563;
            line-height: 1.6;
        }
        
        /* Review form styles */
        .review-form {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        
        .rating-input input {
            display: none;
        }
        
        .rating-input label {
            cursor: pointer;
            font-size: 2rem;
            color: #d1d5db;
            margin-right: 5px;
            transition: color 0.2s;
        }
        
        .rating-input input:checked ~ label,
        .rating-input input:hover ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: var(--warning-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-header {
                padding: 30px 20px;
            }
            
            .profile-picture {
                width: 120px;
                height: 120px;
            }
            
            .section-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include "templates/header.php" ?>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Profile Header -->
        <div class="profile-header text-center">
            <img src="<?= !empty($candidate['profile_picture']) ? htmlspecialchars($candidate['profile_picture']) : 'assets/icons/default-profile.png' ?>" 
                 alt="Profile Picture" class="profile-picture mb-4">
            <h1 class="mb-2"><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h1>
            <p class="lead mb-3">
                <?= !empty($education[0]['speciality']) ? htmlspecialchars($education[0]['speciality']) : 'Professional Candidate' ?>
            </p>
            
            <!-- Rating badge -->
            <?php if (!empty($reviews)): ?>
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="stars mr-2">
                        <?php
                        $full_stars = floor($average_rating);
                        $half_star = ($average_rating - $full_stars) >= 0.5;
                        
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $full_stars):
                        ?>
                            <i class="bi bi-star-fill text-warning"></i>
                        <?php elseif ($i == $full_stars + 1 && $half_star): ?>
                            <i class="bi bi-star-half text-warning"></i>
                        <?php else: ?>
                            <i class="bi bi-star text-warning"></i>
                        <?php endif; endfor; ?>
                    </div>
                    <span class="badge bg-white text-dark"><?= number_format($average_rating, 1) ?> (<?= count($reviews) ?> reviews)</span>
                </div>
            <?php endif; ?>
            
            <p class="mb-4">
                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($candidate['address'] ?? 'Location not specified') ?> | 
                <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($candidate['email']) ?> | 
                <i class="bi bi-phone-fill"></i> <?= htmlspecialchars($candidate['phone'] ?? 'Phone not provided') ?>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= htmlspecialchars($candidate['cv']) ?>" class="btn btn-primary" download>
                    <i class="bi bi-download"></i> Download CV
                </a>
                <a href="c  areer_resources.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Back to Search
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- About Section -->
                <div class="section-card">
                    <h2 class="section-title">About Me</h2>
                    <p class="lead"><?= !empty($candidate['about']) ? nl2br(htmlspecialchars($candidate['about'])) : 'This candidate hasn\'t added an about section yet.' ?></p>
                </div>

                <!-- Experience Section -->
                <?php if (!empty($experience)): ?>
                    <div class="section-card">
                        <h2 class="section-title">Work Experience</h2>
                        <div class="timeline">
                            <?php foreach ($experience as $exp): ?>
                                <div class="timeline-item">
                                    <span class="timeline-date">
                                        <?= date('M Y', strtotime($exp['start_date'])) ?> - 
                                        <?= !empty($exp['end_date']) ? date('M Y', strtotime($exp['end_date'])) : 'Present' ?>
                                    </span>
                                    <h4 class="mb-1"><?= htmlspecialchars($exp['job_name']) ?></h4>
                                    <h5 class="text-primary mb-3"><?= htmlspecialchars($exp['company_name']) ?></h5>
                                    <?php if (!empty($exp['description'])): ?>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Education Section -->
                <?php if (!empty($education)): ?>
                    <div class="section-card">
                        <h2 class="section-title">Education</h2>
                        <div class="timeline">
                            <?php foreach ($education as $edu): ?>
                                <div class="timeline-item">
                                    <span class="timeline-date">
                                        <?= date('M Y', strtotime($edu['start_date'])) ?> - 
                                        <?= !empty($edu['end_date']) ? date('M Y', strtotime($edu['end_date'])) : 'Present' ?>
                                    </span>
                                    <h4 class="mb-1"><?= htmlspecialchars($edu['level']) ?> in <?= htmlspecialchars($edu['speciality']) ?></h4>
                                    <h5 class="text-primary"><?= htmlspecialchars($edu['univ_name']) ?></h5>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Skills Section -->
                <?php if (!empty($skills)): ?>
                    <div class="section-card">
                        <h2 class="section-title">Skills & Expertise</h2>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($skills as $skill): 
                                $skill_data = json_decode($skill, true);
                                $skill_value = is_array($skill_data) ? $skill_data['value'] : $skill;
                            ?>
                                <span class="skill-badge"><?= htmlspecialchars($skill_value) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contact Card -->
                <div class="section-card">
                    <h2 class="section-title">Contact Information</h2>
                    <ul class="list-unstyled">
                        <?php if (!empty($candidate['email'])): ?>
                            <li class="mb-3">
                                <i class="bi bi-envelope-fill text-primary me-2"></i>
                                <a href="mailto:<?= htmlspecialchars($candidate['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($candidate['email']) ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($candidate['phone'])): ?>
                            <li class="mb-3">
                                <i class="bi bi-telephone-fill text-primary me-2"></i>
                                <a href="tel:<?= htmlspecialchars($candidate['phone']) ?>" class="text-decoration-none"><?= htmlspecialchars($candidate['phone']) ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($candidate['address'])): ?>
                            <li class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                <span><?= htmlspecialchars($candidate['address']) ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="mailto:<?= htmlspecialchars($candidate['email']) ?>" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-envelope-fill"></i> Contact Candidate
                    </a>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="section-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">Candidate Reviews</h2>
                <?php if (!empty($reviews)): ?>
                    <div class="d-flex align-items-center">
                        <div class="average-rating"><?= number_format($average_rating, 1) ?></div>
                        <div class="stars mr-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($average_rating)): ?>
                                    <i class="bi bi-star-fill star"></i>
                                <?php elseif ($i == ceil($average_rating) && ($average_rating - floor($average_rating)) >= 0.5): ?>
                                    <i class="bi bi-star-half star"></i>
                                <?php else: ?>
                                    <i class="bi bi-star star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="total-reviews"><?= count($reviews) ?> reviews</div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($reviews)): ?>
                <div class="row">
                    <?php foreach ($reviews as $review): ?>
                        <div class="col-md-6 mb-4">
                            <div class="review-card">
                                <div class="review-header">
                                    <?php if ($review['reviewer_type'] === 'recruiter' && !empty($review['reviewer_profile_pic'])): ?>
                                        <img src="<?= htmlspecialchars($review['reviewer_profile_pic']) ?>" alt="Reviewer" class="reviewer-avatar">
                                    <?php else: ?>
                                        <div class="reviewer-avatar bg-light text-primary d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-fill" style="font-size: 1.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="reviewer-info">
                                        <h5 class="reviewer-name"><?= htmlspecialchars($review['reviewer_display_name']) ?></h5>
                                        <div class="review-date">
                                            <?= date('F j, Y', strtotime($review['created_at'])) ?>
                                            <?php if ($review['reviewer_type'] === 'recruiter'): ?>
                                                <span class="badge bg-primary ms-2">Recruiter</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-comment">
                                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-chat-square-text display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No reviews yet</h5>
                    <p class="text-muted">Be the first to review this candidate</p>
                </div>
            <?php endif; ?>

            <!-- Review Form -->
            <div class="review-form mt-5">
                <h3 class="mb-4">Write a Review</h3>
                
                <?php if (isset($_SESSION['review_success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['review_success'] ?>
                    </div>
                    <?php unset($_SESSION['review_success']); ?>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="rating" class="form-label">Your Rating</label>
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5" required />
                            <label for="star5" title="5 stars"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star4" name="rating" value="4" />
                            <label for="star4" title="4 stars"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star3" name="rating" value="3" />
                            <label for="star3" title="3 stars"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star2" name="rating" value="2" />
                            <label for="star2" title="2 stars"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" id="star1" name="rating" value="1" />
                            <label for="star1" title="1 star"><i class="bi bi-star-fill"></i></label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="comment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" required placeholder="Share your experience with this candidate..."></textarea>
                    </div>
                    
                    <?php if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'recruiter'): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="reviewer_name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="reviewer_name" name="reviewer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reviewer_email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="reviewer_email" name="reviewer_email" required>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="submit_review" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i> Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include "templates/footer.php" ?>

    <script src="assets/JS/bootstrap.bundle.min.js"></script>
    <script src="assets/JS/jquery-3.7.1.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Rating input interaction
        const stars = document.querySelectorAll('.rating-input label');
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('for').replace('star', '');
                highlightStars(rating);
            });
            
            star.addEventListener('mouseout', function() {
                const checked = document.querySelector('.rating-input input:checked');
                if (checked) {
                    highlightStars(checked.value);
                } else {
                    resetStars();
                }
            });
        });
        
        function highlightStars(rating) {
            stars.forEach(star => {
                const starRating = star.getAttribute('for').replace('star', '');
                if (starRating <= rating) {
                    star.querySelector('i').classList.add('text-warning');
                } else {
                    star.querySelector('i').classList.remove('text-warning');
                }
            });
        }
        
        function resetStars() {
            stars.forEach(star => {
                star.querySelector('i').classList.remove('text-warning');
            });
        }
    </script>
</body>
</html>