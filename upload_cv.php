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
    <title>Upload CV - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />
    <link rel="icon" type="image/png" href="./assets/images/hamidou.png" width="8" />
</head>

<body>
    <!-- Navbar -->

    <?php include "templates/header.php" ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Upload Your CV</h2>
        <form>
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Personal Information</h5>
                    <div class="mb-3">
                        <label for="full-name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full-name" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" placeholder="Enter your phone number" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" placeholder="Enter your address">
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Education</h5>
                    <div class="mb-3">
                        <label for="degree" class="form-label">Degree</label>
                        <input type="text" class="form-control" id="degree" placeholder="e.g., Bachelor of Science in Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label for="institution" class="form-label">Institution</label>
                        <input type="text" class="form-control" id="institution" placeholder="e.g., University of Example" required>
                    </div>
                    <div class="mb-3">
                        <label for="graduation-year" class="form-label">Graduation Year</label>
                        <input type="number" class="form-control" id="graduation-year" placeholder="e.g., 2022" required>
                    </div>
                </div>
            </div>

            <!-- Work Experience -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Work Experience</h5>
                    <div class="mb-3">
                        <label for="job-title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job-title" placeholder="e.g., Software Engineer" required>
                    </div>
                    <div class="mb-3">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" class="form-control" id="company" placeholder="e.g., TechCorp" required>
                    </div>
                    <div class="mb-3">
                        <label for="start-date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start-date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end-date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end-date">
                    </div>
                    <div class="mb-3">
                        <label for="job-description" class="form-label">Job Description</label>
                        <textarea class="form-control" id="job-description" rows="3" placeholder="Describe your role and responsibilities"></textarea>
                    </div>
                </div>
            </div>

        <!-- Skills -->
            <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Skills</h5>
                <div class="mb-3">
                <label for="skills" class="form-label">Add Skills</label>
                <input id="skills" name="skills" placeholder="Start typing..." class="form-control">
                </div>
            </div>
            </div>

            <!-- Tagify JS and CSS (from CDN) -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

            <script>
            const input = document.querySelector('#skills');
            const skillsList = [
                "JavaScript", "Python", "HTML", "CSS", "PHP", "Laravel", "Node.js", "React", "Vue.js", "Angular",
                "Java", "Spring Boot", "C#", ".NET", "SQL", "MySQL", "PostgreSQL", "MongoDB", "Firebase",
                "Git", "GitHub", "Docker", "Kubernetes", "AWS", "Azure", "Google Cloud", "DevOps",
                "Agile", "Scrum", "UI/UX", "Figma", "Photoshop", "Illustrator", "SEO", "Data Analysis",
                "Machine Learning", "AI", "TensorFlow", "Pandas", "NumPy", "C++", "Rust", "Go",
                "Shell Scripting", "TypeScript", "Excel", "WordPress", "JIRA", "Customer Service",
                "Marketing", "Public Speaking", "Project Management", "Business Analysis"
            ];

            new Tagify(input, {
                whitelist: skillsList,
                dropdown: {
                enabled: 1,
                maxItems: 20
                }
            });
            </script>


            <!-- Upload CV -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Upload Your CV</h5>
                    <div class="mb-3">
                        <label for="cv-upload" class="form-label">Choose File</label>
                        <input type="file" class="form-control" id="cv-upload" accept=".pdf,.doc,.docx" required>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Submit</button>
            </div>
        </form>
    </div>
    <script>
    const input = document.getElementById('skill-input');
    const container = document.getElementById('skills-container');
    const hiddenInput = document.getElementById('skills-hidden');
    const autocompleteList = document.getElementById('autocomplete-list');

    let skills = [];

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        autocompleteList.innerHTML = '';

        if (query) {
            const matches = availableSkills.filter(skill => skill.toLowerCase().startsWith(query) && !skills.includes(skill));
            matches.forEach(skill => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action';
                item.textContent = skill;
                item.onclick = () => {
                    addSkill(skill);
                    input.value = '';
                    autocompleteList.innerHTML = '';
                };
                autocompleteList.appendChild(item);
            });
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && input.value.trim()) {
            e.preventDefault();
            addSkill(input.value.trim());
            input.value = '';
            autocompleteList.innerHTML = '';
        }
    });

    function addSkill(skill) {
        if (!skills.includes(skill)) {
            skills.push(skill);
            updateSkillsDisplay();
            updateHiddenInput();
        }
    }

    function removeSkill(skill) {
        skills = skills.filter(s => s !== skill);
        updateSkillsDisplay();
        updateHiddenInput();
    }

    function updateSkillsDisplay() {
        container.innerHTML = '';
        skills.forEach(skill => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-2 p-2';
            badge.textContent = skill;

            const removeBtn = document.createElement('span');
            removeBtn.className = 'ms-2 text-white';
            removeBtn.style.cursor = 'pointer';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => removeSkill(skill);

            badge.appendChild(removeBtn);
            container.appendChild(badge);
        });
    }

    function updateHiddenInput() {
        hiddenInput.value = skills.join(',');
    }

    // Hide suggestions on click outside
    document.addEventListener('click', function (e) {
        if (!autocompleteList.contains(e.target) && e.target !== input) {
            autocompleteList.innerHTML = '';
        }
    });
</script>

    <?php include "templates/footer.php" ?>
</body>

</html>