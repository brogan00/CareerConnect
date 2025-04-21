<?php
// Get approved jobs with recruiter and company info
$approved_jobs = [];
$stmt = $conn->prepare("
    SELECT j.*, r.first_name, r.last_name, r.email, c.name AS company_name
    FROM job j
    JOIN recruiter r ON j.recruiter_id = r.id
    LEFT JOIN company c ON r.company_id = c.id
    WHERE j.status = 'approved'
    ORDER BY j.updated_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$approved_jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Approved Jobs</h5>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control search-input" placeholder="Search jobs...">
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($approved_jobs)): ?>
            <div class="alert alert-info">No approved jobs found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Recruiter</th>
                            <th>Status</th>
                            <th>Approved On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_jobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></td>
                                <td><span class="badge badge-approved">Approved</span></td>
                                <td><?= date('M d, Y', strtotime($job['updated_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                            data-bs-target="#approvedJobModal<?= $job['id'] ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- Approved Job Details Modal -->
                            <div class="modal fade" id="approvedJobModal<?= $job['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <?= htmlspecialchars($job['title']) ?>
                                                <span class="badge badge-approved ms-2">Approved</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name'] ?? 'Not specified') ?></p>
                                                    <p><strong>Posted by:</strong> <?= htmlspecialchars($job['first_name'].' '.$job['last_name']) ?></p>
                                                    <p><strong>Email:</strong> <?= htmlspecialchars($job['email']) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Contract Type:</strong> <?= htmlspecialchars($job['type_contract']) ?></p>
                                                    <p><strong>Salary:</strong> 
                                                        <?= $job['salary'] ? number_format($job['salary'], 2) . ' DZD' : 'Not specified' ?>
                                                    </p>
                                                    <p><strong>Approved on:</strong> <?= date('M d, Y H:i', strtotime($job['updated_at'])) ?></p>
                                                </div>
                                            </div>
                                            
                                            <h6>Job Description:</h6>
                                            <div class="border p-3 mb-3">
                                                <?= nl2br(htmlspecialchars($job['mission'])) ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times"></i> Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>