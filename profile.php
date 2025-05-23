<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

if (!isset($_SESSION['user_email'])) {
  header("Location: login.php");
  exit();
}


if (isset($_SESSION['user_email'])) {
  if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] == "recruiter") {
      $stmt = $conn->prepare("SELECT first_name, last_name, address, profile_picture , company_id FROM recruiter WHERE email = ?");
    } else if ($_SESSION['user_type'] == "candidat" || $_SESSION['user_type'] == "admin") {
      $stmt = $conn->prepare("SELECT first_name, last_name, address, phone, cv, sexe, about, profile_picture FROM users WHERE email = ?");
    } else {
      die('Invalid user type!');
    }
  }
}

$email = $_SESSION['user_email'];
$stmt->bind_param("s", $email);
$stmt->execute();
if ($_SESSION['user_type'] == "recruiter") {
  $stmt->bind_result($first_name, $last_name, $address, $profile_picture, $company_id);
} else if ($_SESSION['user_type'] == "candidat" || $_SESSION['user_type'] == "admin") {
  $stmt->bind_result($first_name, $last_name, $address, $phone, $cv, $sexe, $about, $profile_picture);
}
$stmt->store_result();
$stmt->fetch();
if ($_SESSION['user_type'] == "recruiter") {
  if ($company_id != null) {
    $stmt = $conn->prepare("SELECT name FROM company WHERE id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $stmt->bind_result($company_name);
    $stmt->fetch();
  } else {
    $company_name = null;
  }
}
$stmt->close();

if (!$profile_picture) {
  $profile_picture = "./assets/images/hamidou.png";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>My Profile - CareerConnect</title>
  <link rel="stylesheet" href="assets/CSS/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/CSS/style.css" />
  <link rel="icon" type="image/png" href="./assets/images/hamidou.png" />
  <style>
    .profile-card {
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 30px;
      background-color: #ffffff;
    }

    .profile-card img {
      border-radius: 50%;
      width: 130px;
      height: 130px;
      object-fit: cover;
    }

    .form-group label {
      font-weight: bold;
    }

    .btn-group {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 25px;
    }
  </style>
</head>

<body>
  <?php include "templates/header.php"; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="profile-card text-center">
          <!-- Profile Image -->
          <form action="updatePicture.php" method="POST" enctype="multipart/form-data">
            <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" />
            <div class="form-group mt-3">
              <input type="file" name="profile_picture" accept="image/*" class="form-control">
              <button type="submit" class="btn btn-outline-secondary mt-2">Change Picture</button>
            </div>
          </form>

          <!-- Profile Info -->
          <form action="editProfile.php" method="POST" id="profile-form">
            <div class="form-group mt-3">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="stname" class="form-control" value="<?php echo $first_name; ?>" readonly required pattern="[A-Za-z]{2,}">
            </div>
            <div class="form-group">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="ltname" class="form-control" value="<?php echo $last_name; ?>" readonly required pattern="[A-Za-z]{2,}">
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" class="form-control" value="<?php echo $_SESSION["user_email"]; ?>" readonly required>
            </div>
            <div class="form-group">
              <label for="address">Address</label>
              <input type="text" id="address" name="address" class="form-control" value="<?php echo $address; ?>" readonly required>
            </div>

            <?php
            if ($_SESSION['user_type'] == "recruiter") {
            ?>
              <div class="form-group">
                <label for="company">company</label>
                <input type="text" id="company" name="company" class="form-control" value="<?php
                                                                                            if ($company_name) {
                                                                                              echo $company_name;
                                                                                            } else {
                                                                                              echo "Independent";
                                                                                            }
                                                                                            ?>" readonly required>
              </div>
            <?php
            } elseif ($_SESSION['user_type'] == "candidat" || $_SESSION['user_type'] == "admin") {
            ?>
              <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $phone; ?>" readonly required pattern="[0-9]{8,15}">
              </div>
              <div class="form-group">
                <label>Gender</label><br>
                <label for="man">Man</label>
                <input type="radio" id="man" name="sexe" value="man" class="mx-2" <?php if ($sexe == 'man') echo "checked"; ?> disabled>
                <label for="woman">Woman</label>
                <input type="radio" id="woman" name="sexe" value="woman" class="mx-2" <?php if ($sexe == 'woman') echo "checked"; ?> disabled>
              </div>
              <div class="form-group">
                <label for="about">About</label>
                <textarea id="about" name="about" class="form-control" rows="4" readonly><?php echo $about; ?></textarea>
              </div>
            <?php
            }
            ?>



            <div class="form-group mt-3">
              <button type="button" class="btn btn-primary" onclick="editProfile()">Edit</button>
              <button type="submit" class="btn btn-success" style="display: none;" id="save-btn">Save</button>
            </div>
          </form>

          <!-- Change Password -->
          <hr class="my-4">
          <h4>Change Password</h4>
          <form action="editPassword.php" method="POST" id="password-form">
            <div class="form-group">
              <label for="old-password">Old Password</label>
              <input type="password" id="old-password" name="old_password" class="form-control" required placeholder="Enter old password">
            </div>
            <div class="form-group">
              <label for="new-password">New Password</label>
              <input type="password" id="new-password" name="new_password" class="form-control" required minlength="6" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirm New Password</label>
              <input type="password" id="confirm-password" class="form-control" required placeholder="Re-enter new password">
            </div>
            <div class="form-group mt-2">
              <button type="submit" class="btn btn-warning btn-block">Update Password</button>
            </div>
          </form>



          <script>
            document.getElementById("password-form").addEventListener("submit", function(e) {
              const newPassword = document.getElementById("new-password").value;
              const confirmPassword = document.getElementById("confirm-password").value;

              if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert("New passwords do not match.");
              } else if (newPassword.length < 6) {
                e.preventDefault();
                alert("New password must be at least 6 characters long.");
              }
            });
          </script>


          <!-- Buttons -->
          <div class="btn-group">
            <?php
            if ($_SESSION['user_type'] == "candidat") {
            ?>
              <a href="upload_cv.php" class="btn btn-primary"><?php echo $cv ? "Update CV" : "Upload CV"; ?></a>
            <?php
            } else if ($_SESSION['user_type'] == "recruiter") {
            ?>
              <a href="post_a_job.php" class="btn btn-primary">Post Job</a>
            <?php
            }
            ?>
            <a href="job_search.php" class="btn btn-outline-primary">Search Jobs</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function editProfile() {
      const ids = ["first_name", "last_name", "email", "phone", "address", "about", "man", "woman"];
      ids.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          if (element.type === "radio") {
            element.disabled = false;
          } else {
            element.readOnly = false;
          }
        }
      });
      document.getElementById("save-btn").style.display = "inline-block";
    }
  </script>

  <?php include "templates/footer.php"; ?>
</body>

</html>