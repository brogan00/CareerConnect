<div class="animate__animated animate__fadeIn">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Approved Job Postings</h2>
    <div class="text-muted"><?= date('M j, Y') ?></div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th width="5%">ID</th>
              <th width="25%">Job Title</th>
              <th width="20%">Company</th>
              <th width="15%">Type</th>
              <th width="15%">Salary</th>
              <th width="10%">Expires</th>
              <th width="10%">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $jobs = $conn->query("
              SELECT j.id, j.title, j.type_contract, j.salary, j.expiration_date, j.created_at,
              CONCAT(r.first_name, ' ', r.last_name) as recruiter_name, 
              c.name as company_name
              FROM job j
              JOIN recruiter r ON j.recruiter_id = r.id
              LEFT JOIN company c ON r.company_id = c.id
              WHERE j.status = 'approved'
              ORDER BY j.expiration_date ASC
            ");
            
            while ($job = $jobs->fetch_assoc()):
              $expires_soon = strtotime($job['expiration_date']) < strtotime('+7 days');
            ?>
            <tr class="<?= $expires_soon ? 'table-warning' : '' ?>">
              <td><?= $job['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($job['title']) ?></strong>
                <div class="text-muted small">Posted: <?= date('M d', strtotime($job['created_at'])) ?></div>
              </td>
              <td><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($job['type_contract']) ?></td>
              <td>
                <?= $job['salary'] ? '<span class="fw-bold">' . number_format($job['salary'], 0) . ' DZD</span>' : 'Negotiable' ?>
              </td>
              <td>
                <span class="<?= $expires_soon ? 'text-danger fw-bold' : '' ?>">
                  <?= date('M d', strtotime($job['expiration_date'])) ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button onclick="deleteJob(<?= $job['id'] ?>)" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>