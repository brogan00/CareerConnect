<?php
// Get counts for dashboard
$pending_jobs_count = $conn->query("SELECT COUNT(*) FROM job WHERE status = 'pending'")->fetch_row()[0];
$approved_jobs_count = $conn->query("SELECT COUNT(*) FROM job WHERE status = 'approved'")->fetch_row()[0];
$candidates_count = $conn->query("SELECT COUNT(*) FROM users WHERE type = 'candidat'")->fetch_row()[0];
$recruiters_count = $conn->query("SELECT COUNT(*) FROM recruiter")->fetch_row()[0];
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card animate__animated animate__fadeIn animate__delay-1s">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Pending Jobs</h6>
                <h2 class="mb-3"><?= $pending_jobs_count ?></h2>
                <a href="?tab=pending-jobs" class="card-link">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card animate__animated animate__fadeIn animate__delay-2s">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Approved Jobs</h6>
                <h2 class="mb-3"><?= $approved_jobs_count ?></h2>
                <a href="?tab=approved-jobs" class="card-link">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card animate__animated animate__fadeIn animate__delay-3s">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Candidates</h6>
                <h2 class="mb-3"><?= $candidates_count ?></h2>
                <a href="?tab=candidates" class="card-link">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card animate__animated animate__fadeIn animate__delay-4s">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Recruiters</h6>
                <h2 class="mb-3"><?= $recruiters_count ?></h2>
                <a href="?tab=recruiters" class="card-link">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5 class="mb-0">Recent Pending Jobs</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_pending = $conn->query("
                    SELECT j.title, j.created_at, r.first_name, r.last_name 
                    FROM job j
                    JOIN recruiter r ON j.recruiter_id = r.id
                    WHERE j.status = 'pending'
                    ORDER BY j.created_at DESC
                    LIMIT 5
                ")->fetch_all(MYSQLI_ASSOC);
                
                if (empty($recent_pending)): ?>
                    <div class="alert alert-info">No pending jobs found.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recent_pending as $job): ?>
                            <a href="?tab=pending-jobs" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($job['title']) ?></h6>
                                    <small><?= date('M d', strtotime($job['created_at'])) ?></small>
                                </div>
                                <small>Posted by: <?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h5 class="mb-0">Recently Approved Jobs</h5>
            </div>
            <div class="card-body">
                <?php
                $recent_approved = $conn->query("
                    SELECT j.title, j.updated_at, r.first_name, r.last_name 
                    FROM job j
                    JOIN recruiter r ON j.recruiter_id = r.id
                    WHERE j.status = 'approved'
                    ORDER BY j.updated_at DESC
                    LIMIT 5
                ")->fetch_all(MYSQLI_ASSOC);
                
                if (empty($recent_approved)): ?>
                    <div class="alert alert-info">No approved jobs found.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recent_approved as $job): ?>
                            <a href="?tab=approved-jobs" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($job['title']) ?></h6>
                                    <small><?= date('M d', strtotime($job['updated_at'])) ?></small>
                                </div>
                                <small>Posted by: <?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>