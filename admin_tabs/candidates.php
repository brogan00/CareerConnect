<div class="animate__animated animate__fadeIn">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0">Manage Candidates</h2>
    <div class="input-group" style="width: 300px;">
      <input type="text" id="candidateSearch" class="form-control" placeholder="Search candidates...">
      <button class="btn btn-outline-secondary" type="button" id="searchCandidateButton">
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
              <th>Candidate</th>
              <th>Contact</th>
              <th>CV</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $candidates = $conn->query("
              SELECT id, first_name, last_name, email, phone, cv, status, created_at 
              FROM users 
              WHERE type = 'candidat'
              ORDER BY created_at DESC
            ");
            
            while ($candidate = $candidates->fetch_assoc()):
            ?>
            <tr>
              <td><?= $candidate['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?></strong>
                <div class="text-muted small">Joined: <?= date('M Y', strtotime($candidate['created_at'])) ?></div>
              </td>
              <td>
                <div><?= htmlspecialchars($candidate['email']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($candidate['phone'] ?? 'No phone') ?></div>
              </td>
              <td>
                <?php if ($candidate['cv']): ?>
                  <a href="<?= htmlspecialchars($candidate['cv']) ?>" class="btn btn-sm btn-outline-info" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> View CV
                  </a>
                <?php else: ?>
                  <span class="badge bg-secondary">No CV</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-<?= $candidate['status'] === 'active' ? 'success' : 'secondary' ?>">
                  <?= ucfirst($candidate['status']) ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <button onclick="toggleCandidateStatus(<?= $candidate['id'] ?>, '<?= $candidate['status'] ?>')"
                    class="btn btn-sm btn-<?= $candidate['status'] === 'active' ? 'warning' : 'success' ?>">
                    <i class="fas fa-<?= $candidate['status'] === 'active' ? 'ban' : 'check' ?> me-1"></i>
                    <?= $candidate['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                  </button>
                  <button onclick="confirmDeleteCandidate(<?= $candidate['id'] ?>)" class="btn btn-sm btn-outline-danger">
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

<script>
  // Search functionality
  $(document).ready(function() {
    $("#candidateSearch").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#manage-candidates table tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });

    $("#searchCandidateButton").click(function() {
      $("#candidateSearch").trigger("keyup");
    });
  });
</script>