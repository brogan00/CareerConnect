<?php
include "connexion/config.php";
define('SECURE_ACCESS', true);
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// Get user info from DB
$email = $_SESSION['user_email'];
$query = "SELECT first_name,last_name, address, phone,cv, sexe, about, profile_picture FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($first_name,$last_name, $address,$phone,$cv,$sexe,$about,$profile_picture);
$stmt->fetch();
$stmt->close();

if($profile_picture==null){
  $profile_picture = "./assets/images/hamidou.png";
}

// Optional feedback
//$msg = $_GET['msg'] ?? '';
//$type = $_GET['type'] ?? 'info';
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
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }

    .profile-card img {
      border-radius: 50%;
      width: 120px;
      height: 120px;
      object-fit: cover;
      margin-bottom: 20px;
    }

    .btn-group {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <?php include "templates/header.php"; ?>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
       
        <div class="profile-card text-center">
          <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" />

          

        

          <form action="editProfile.php" method="POST" id="profile-form">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="stname" class="form-control" value="<?php echo $first_name;?>" readonly>
                  </div>
                  <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="ltname" class="form-control" value="<?php echo $last_name;?>" readonly>
                  </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $_SESSION["user_email"];?>" readonly>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $phone;?>" readonly>
                </div>
                <div class="form-group">
                    <label for="address">Addresse</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo $address?>" readonly>
                </div>
                <div class="form-group">
                    <label for="">sexe</label>
                </div>
                <div class="form-group">
                    <label for="man">Man</label>
                    <input type="radio" id="man" name="sexe" class="m-2" value="man" <?php if($sexe=='man') echo "checked"; ?> disabled>
                    <label for="woman">Woman</label>
                    <input type="radio" id="woman" name="sexe" class="m-2" value="woman" <?php if($sexe=='woman') echo "checked"; ?> disabled>
                </div>
                <div class="form-group">
                    <label for="about">About</label>
                    <textarea id="about" name="about" class="form-control" rows="5" readonly><?php echo $about?></textarea>
                </div>
                <div class="form-group mt-3">
                    <button type="button" class="btn btn-primary" onclick="editProfile()">Edit</button>
                    <button type="submit" class="btn btn-primary" style="display: none;" id="save-btn">Save</button>
                </div>
            </form>


            <h3>Change Password</h3>
            <form action="editPassword.php" method="POST" id="password-form">
                <div class="form-group">
                    <label for="old-password">Old Password</label>
                    <input type="password" id="old-password" name="old-password" class="form-control" placeholder="Enter old password">
                </div>
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new-password" class="form-control" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </div>
            </form>


          <!-- Buttons -->
          <div class="btn-group">
            <a href="upload_cv.php" class="btn btn-primary">
              <?php 
                if($cv==null) echo "Upload CV"; 
                else echo "Update CV";
                ?>
            </a>
            <a href="job_search.php" class="btn btn-outline-primary">Search Jobs</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>

  function editProfile() {
      document.getElementById("first_name").readOnly = false;
      document.getElementById("last_name").readOnly = false;
      document.getElementById("email").readOnly = false;
      document.getElementById("phone").readOnly = false;
      document.getElementById("address").readOnly = false;
      document.getElementById("man").disabled = false;
      document.getElementById("woman").disabled = false;
      document.getElementById("about").readOnly = false;
      document.getElementById("save-btn").style.display = "inline-block";
    }
</script>

  <?php include "templates/footer.php"; ?>
</body>

</html>
