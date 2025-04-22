<?php
require_once '../config.php';

if (isset($_GET['id'])) {
    $candidateId = intval($_GET['id']);
    
    // Get candidate basic info
    $candidate = $conn->query("SELECT * FROM users WHERE id = $candidateId")->fetch_assoc();
    
    if ($candidate) {
        // Get education
        $education = $conn->query("SELECT * FROM education WHERE user_id = $candidateId");
        
        // Get experience
        $experience = $conn->query("SELECT * FROM experience WHERE user_id = $candidateId");
        
        // Get skills
        $skills = $conn->query("SELECT * FROM skills WHERE user_id = $candidateId");
        
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6>Personal Information</h6>';
        echo '<p><strong>Name:</strong> '.htmlspecialchars($candidate['first_name'].' '.$candidate['last_name']).'</p>';
        echo '<p><strong>Email:</strong> '.htmlspecialchars($candidate['email']).'</p>';
        echo '<p><strong>Phone:</strong> '.htmlspecialchars($candidate['phone'] ?? 'N/A').'</p>';
        echo '<p><strong>Address:</strong> '.htmlspecialchars($candidate['address'] ?? 'N/A').'</p>';
        echo '<p><strong>Gender:</strong> '.ucfirst($candidate['sexe'] ?? 'N/A').'</p>';
        echo '<p><strong>About:</strong> '.htmlspecialchars($candidate['about'] ?? 'N/A').'</p>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6>Professional Information</h6>';
        if ($candidate['cv']) {
            echo '<p><strong>CV:</strong> <a href="'.htmlspecialchars($candidate['cv']).'" target="_blank">Download CV</a></p>';
        }
        
        echo '<p><strong>CV Status:</strong> ';
        if ($candidate['cv']) {
            echo '<span class="badge badge-'.htmlspecialchars($candidate['cv_status'] ?? 'pending').'">';
            echo ucfirst(htmlspecialchars($candidate['cv_status'] ?? 'pending'));
            echo '</span>';
        } else {
            echo '<span class="badge bg-secondary">No CV</span>';
        }
        echo '</p>';
        
        echo '<p><strong>Education:</strong></p>';
        if ($education->num_rows > 0) {
            echo '<ul>';
            while($edu = $education->fetch_assoc()) {
                echo '<li>';
                echo htmlspecialchars($edu['level']).' in '.htmlspecialchars($edu['speciality']).' at '.htmlspecialchars($edu['univ_name']);
                echo ' ('.date('Y', strtotime($edu['start_date'])).'-'.date('Y', strtotime($edu['end_date'])).')';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No education information</p>';
        }
        
        echo '<p><strong>Experience:</strong></p>';
        if ($experience->num_rows > 0) {
            echo '<ul>';
            while($exp = $experience->fetch_assoc()) {
                echo '<li>';
                echo htmlspecialchars($exp['job_name']).' at '.htmlspecialchars($exp['company_name']);
                echo ' ('.date('M Y', strtotime($exp['start_date'])).'-'.date('M Y', strtotime($exp['end_date'])).')';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No experience information</p>';
        }
        
        echo '<p><strong>Skills:</strong></p>';
        if ($skills->num_rows > 0) {
            echo '<div class="d-flex flex-wrap gap-2">';
            while($skill = $skills->fetch_assoc()) {
                echo '<span class="badge bg-primary">'.htmlspecialchars($skill['content']).'</span>';
            }
            echo '</div>';
        } else {
            echo '<p>No skills listed</p>';
        }
        
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Candidate not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request</div>';
}
?>