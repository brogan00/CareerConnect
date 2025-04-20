<div class="animate-fade">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold mb-0">Dashboard Overview</h2>
    <div class="text-muted small"><?= date('l, F j, Y') ?></div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
              <i class="fas fa-users text-primary fs-4"></i>
            </div>
            <div>
              <h6 class="mb-1">Total Candidates</h6>
              <?php $count = $conn->query("SELECT COUNT(*) FROM users WHERE type = 'candidat'")->fetch_row()[0]; ?>
              <h2 class="mb-0"><?= number_format($count) ?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
              <i class="fas fa-user-tie text-warning fs-4"></i>
            </div>
            <div>
              <h6 class="mb-1">Total Recruiters</h6>
              <?php $count = $conn->query("SELECT COUNT(*) FROM recruiter")->fetch_row()[0]; ?>
              <h2 class="mb-0"><?= number_format($count) ?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
              <i class="fas fa-hourglass-half text-danger fs-4"></i>
            </div>
            <div>
              <h6 class="mb-1">Pending Jobs</h6>
              <?php $count = $conn->query("SELECT COUNT(*) FROM job WHERE status = 'pending'")->fetch_row()[0]; ?>
              <h2 class="mb-0"><?= number_format($count) ?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
              <i class="fas fa-check-circle text-success fs-4"></i>
            </div>
            <div>
              <h6 class="mb-1">Approved Jobs</h6>
              <?php $count = $conn->query("SELECT COUNT(*) FROM job WHERE status = 'approved'")->fetch_row()[0]; ?>
              <h2 class="mb-0"><?= number_format($count) ?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Recent Job Approvals</h6>
          <a href="?tab=approved-jobs" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Job Title</th>
                  <th>Company</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $jobs = $conn->query("
                  SELECT j.title, c.name as company, j.status 
                  FROM job j
                  LEFT JOIN recruiter r ON j.recruiter_id = r.id
                  LEFT JOIN company c ON r.company_id = c.id
                  WHERE j.status IN ('approved', 'rejected')
                  ORDER BY j.updated_at DESC LIMIT 5
                ");
                while ($job = $jobs->fetch_assoc()):
                ?>
                <tr>
                  <td><?= htmlspecialchars($job['title']) ?></td>
                  <td><?= htmlspecialchars($job['company'] ?? 'N/A') ?></td>
                  <td>
                    <span class="status-badge badge-<?= $job['status'] === 'approved' ? 'approved' : 'rejected' ?>">
                      <?= ucfirst($job['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Recent Candidate Registrations</h6>
          <a href="?tab=candidates" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $candidates = $conn->query("
                  SELECT first_name, last_name, email, status 
                  FROM users 
                  WHERE type = 'candidat'
                  ORDER BY created_at DESC LIMIT 5
                ");
                while ($candidate = $candidates->fetch_assoc()):
                ?>
                <tr>
                  <td><?= htmlspecialchars($candidate['first_name'].' '.$candidate['last_name']) ?></td>
                  <td><?= htmlspecialchars($candidate['email']) ?></td>
                  <td>
                    <span class="badge bg-<?= $candidate['status'] === 'active' ? 'success' : 'secondary' ?>">
                      <?= ucfirst($candidate['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>