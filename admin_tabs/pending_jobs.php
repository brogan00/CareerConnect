<div class="animate-fade">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold mb-0">Pending Job Approvals</h2>
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" class="form-control search-input" placeholder="Search jobs...">
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Jobs Awaiting Approval</h6>
      <span class="badge bg-warning text-dark">
        <?= $conn->query("SELECT COUNT(*) FROM job WHERE status = 'pending'")->fetch_row()[0] ?> Pending
      </span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Job Title</th>
              <th>Company</th>
              <th>Salary</th>
              <th>Posted By</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $jobs = $conn->query("
              SELECT j.id, j.title, j.salary, j.created_at,
              CONCAT(r.first_name, ' ', r.last_name) as recruiter_name,
              c.name as company_name
              FROM job j
              JOIN recruiter r ON j.recruiter_id = r.id
              LEFT JOIN company c ON r.company_id = c.id
              WHERE j.status = 'pending'
              ORDER BY j.created_at DESC
            ");
            while ($job = $jobs->fetch_assoc()):
            ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($job['title']) ?></strong>
                <div class="text-muted small mt-1">
                  <?= date('M d, Y', strtotime($job['created_at'])) ?>
                </div>
              </td>
              <td><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></td>
              <td>
                <?= $job['salary'] ? number_format($job['salary'], 0).' DZD' : 'Negotiable' ?>
              </td>
              <td><?= htmlspecialchars($job['recruiter_name']) ?></td>
              <td><?= date('M d', strtotime($job['created_at'])) ?></td>
              <td>
                <div class="d-flex">
                  <a href="job_details.php?id=<?= $job['id'] ?>" 
                     class="btn btn-sm btn-outline-primary action-btn me-1"
                     data-bs-toggle="tooltip" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="admin_actions.php?action=approve_job&job_id=<?= $job['id'] ?>" 
                     class="btn btn-sm btn-outline-success action-btn me-1"
                     data-bs-toggle="tooltip" title="Approve Job">
                    <i class="fas fa-check"></i>
                  </a>
                  <a href="admin_actions.php?action=reject_job&job_id=<?= $job['id'] ?>" 
                     class="btn btn-sm btn-outline-danger action-btn"
                     data-bs-toggle="tooltip" title="Reject Job">
                    <i class="fas fa-times"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-white">
      <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0 justify-content-center">
          <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1">Previous</a>
          </li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item">
            <a class="page-link" href="#">Next</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>