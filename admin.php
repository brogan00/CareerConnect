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
    <title>Admin Dashboard - CareerConnect</title>
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/icons/all.min.css" />
    <link rel="stylesheet" href="assets/CSS/style.css" />

    <!-- Font Awesome Icons -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Custom Styles -->
    <style>
        .admin-sidebar {
            background: #3a0ca3;
            color: white;
            height: 100vh;
            padding: 20px;
        }

        .admin-sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .admin-sidebar a:hover {
            background: #480ca8;
        }

        .admin-content {
            padding: 20px;
            background: #f8f9fa;
        }

        .admin-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .admin-card h3 {
            color: #3a0ca3;
            margin-bottom: 20px;
        }

        .btn-admin {
            background: #3a0ca3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .btn-admin:hover {
            background: #480ca8;
        }

        .table-admin {
            width: 100%;
            border-collapse: collapse;
        }

        .table-admin th,
        .table-admin td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-admin th {
            background: #3a0ca3;
            color: white;
        }

        .table-admin tr:hover {
            background: #f1f1f1;
        }
    </style>
</head>

<body>
    <!-- Your Navbar -->

    <!-- php include "templates/header.php" -->

    <!-- Admin Dashboard Layout -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar">
                <h3 class="text-center mb-4">Admin Panel</h3>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="#users" onclick="loadSection('users')">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#jobs" onclick="loadSection('jobs')">
                            <i class="fas fa-briefcase me-2"></i>Manage Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="#companies"
                            onclick="loadSection('companies')">
                            <i class="fas fa-building me-2"></i>Manage Companies
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <!-- Users Section -->
                <div id="users" class="section">
                    <div class="admin-card">
                        <h3>Manage Users</h3>
                        <button class="btn btn-admin mb-3" onclick="addUser()">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                        <table class="table table-admin">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTable">
                                <!-- Users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Jobs Section -->
                <div id="jobs" class="section" style="display: none;">
                    <div class="admin-card">
                        <h3>Manage Jobs</h3>
                        <button class="btn btn-admin mb-3" onclick="addJob()">
                            <i class="fas fa-plus me-2"></i>Add Job
                        </button>
                        <table class="table table-admin">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Company</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="jobTable">
                                <!-- Jobs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Companies Section -->
                <div id="companies" class="section" style="display: none;">
                    <div class="admin-card">
                        <h3>Manage Companies</h3>
                        <button class="btn btn-admin mb-3" onclick="addCompany()">
                            <i class="fas fa-plus me-2"></i>Add Company
                        </button>
                        <table class="table table-admin">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="companyTable">
                                <!-- Companies will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- php include "templates/footer.php" -->


    <script src="../assets/JS/jquery-3.7.1.js"></script>
    <script src="../assets/JS/bootstrap.min.js"></script>
    <script src="../assets/icons/all.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Script -->
    <script>
        function loadSection(section) {
            document.querySelectorAll('.section').forEach((sec) => (sec.style.display = 'none'));
            document.getElementById(section).style.display = 'block';
            loadData(section);
        }

        function loadData(section) {
            fetch(`/api/${section}`)
                .then((response) => response.json())
                .then((data) => {
                    const table = document.getElementById(`${section}Table`);
                    table.innerHTML = '';
                    data.forEach((item) => {
                        const row = table.insertRow();
                        Object.values(item).forEach((text) => {
                            const cell = row.insertCell();
                            cell.textContent = text;
                        });
                        const actionCell = row.insertCell();
                        actionCell.innerHTML = `
                <button class="btn btn-sm btn-warning me-2" onclick="edit${section.charAt(0).toUpperCase() + section.slice(1)}(${item.id})">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger" onclick="delete${section.charAt(0).toUpperCase() + section.slice(1)}(${item.id})">
                  <i class="fas fa-trash"></i> Delete
                </button>
              `;
                    });
                });
        }

        function addUser() {
            // Implement add user logic
        }

        function editUser(id) {
            // Implement edit user logic
        }

        function deleteUser(id) {
            // Implement delete user logic
        }

        function addJob() {
            // Implement add job logic
        }

        function editJob(id) {
            // Implement edit job logic
        }

        function deleteJob(id) {
            // Implement delete job logic
        }

        function addCompany() {
            // Implement add company logic
        }

        function editCompany(id) {
            // Implement edit company logic
        }

        function deleteCompany(id) {
            // Implement delete company logic
        }

        // Load the default section
        loadSection('users');
    </script>
</body>

</html>