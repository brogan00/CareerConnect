<div class="animate__animated animate__fadeIn">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Manage Recruiters</h2>
    <div class="input-group" style="width: 300px;">
      <input type="text" id="recruiterSearch" class="form-control" placeholder="Search recruiters...">
      <button class="btn btn-outline-secondary" type="button" id="searchRecruiterButton">
        <i class="fas fa-search"></i>
      </button>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Recruiter</th>
              <th>Company</th>
              <th>Contact</th>
              <th>Jobs</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $recruiters = $conn->query("
              SELECT r.id, r.first_name, r.last_name, r.email,  r.created_at, 
              c.name as company_name,
              (SELECT COUNT(*) FROM job WHERE recruiter_id = r.id) as job_count
              FROM recruiter r
              LEFT JOIN company c ON r.company_id = c.id
              ORDER BY r.created_at DESC
            ");
            
            while ($recruiter = $recruiters->fetch_assoc()):
            ?>
            <tr>
              <td><?= $recruiter['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($recruiter['first_name'] . ' ' . $recruiter['last_name']) ?></strong>
                <div class="text-muted small">Joined: <?= date('M Y', strtotime($recruiter['created_at'])) ?></div>
              </td>
              <td><?= htmlspecialchars($recruiter['company_name'] ?? 'Independent') ?></td>
              <td>
                <div><?= htmlspecialchars($recruiter['email']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($recruiter['phone'] ?? 'No phone') ?></div>
              </td>
              <td>
                <span class="badge bg-primary rounded-pill"><?= $recruiter['job_count'] ?></span>
              </td>
              <td>
                <button onclick="confirmDeleteRecruiter(<?= $recruiter['id'] ?>)" class="btn btn-sm btn-outline-danger">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  // Search functionality
  $(document).ready(function() {
    $("#recruiterSearch").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#manage-recruiters table tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });

    $("#searchRecruiterButton").click(function() {
      $("#recruiterSearch").trigger("keyup");
    });
  });
</script>