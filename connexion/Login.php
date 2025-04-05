<?php
session_start();

if (isset($_SESSION['token'])) {
  header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="../assets/CSS/connexion.css">
  <link rel="stylesheet" href="../assets/icons/all.min.css">
  <link rel="stylesheet" href="../assets/CSS/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/CSS/style.css">
  <script src="../assets/JS/jquery-3.7.1.js"></script>
</head>

<body class="h-100 w-100 d-flex flex-column align-items-center justify-content-center background-radial-gradient">
  <!-- Section: Login Block -->
  <a id="returnBtn" class="btn position-absolute top-0 start-0 m-4 px-4 py-2 fw-bold text-white"
    style="background: linear-gradient(45deg, #6a15e0, #7a1bff); border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); transition: 0.3s;">
    ← Back
  </a>

  <section class="overflow-hidden">
    <div class="container px-4 py-5 px-md-5 text-center text-lg-start my-5">
      <div class="row gx-lg-5 align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0" style="z-index: 10">
          <h1 class="my-5 display-5 fw-bold ls-tight text-white">
            Welcome Back! <br />
            <span style="color: hsl(218, 81%, 75%)">Log in to your account</span>
          </h1>
          <p class="mb-4 opacity-70" style="color: hsl(218, 81%, 85%)">
            Enter your credentials to access your account and explore amazing features.
          </p>
        </div>
        <div class="col-lg-6 mb-5 mb-lg-0 position-relative">
          <div id="radius-shape-1" class="position-absolute rounded-circle shadow-5-strong"></div>
          <div id="radius-shape-2" class="position-absolute shadow-5-strong"></div>
          <div class="card bg-glass">
            <div class="card-body px-4 py-5 px-md-5">
              <form id="loginForm" action="login.php" method="POST">

                <div id="error" class="form-outline mb-4 text-danger d-none"></div>
                <!-- Email input -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="email">Email address</label>
                  <input class="form-control" type="email" name="email" required placeholder="Email">
                </div>

                <!-- Password input -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="password">Password</label>
                  <input class="form-control" type="password" name="password" required placeholder="Password">
                </div>

                <!-- Remember Me Checkbox -->
                <div class="form-check d-flex justify-content-between mb-4">
                  <div>
                    <input class="form-check-input me-2" type="checkbox" id="rememberMe" />
                    <label class="form-check-label" for="rememberMe"> Remember me </label>
                  </div>
                  <a href="#" class="text-decoration-none">Forgot password?</a>
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn btn-primary btn-block mb-4 w-100">
                  Log in
                </button>

                <!-- Register buttons -->
                <div class="text-center ">
                  <p>or log in with:</p>
                  <button type="button" class="btn btn-link btn-floating mx-1 ">
                    <i class="fab fa-facebook-f color-primary"></i>
                  </button>
                  <button type="button" class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-google color-primary"></i>
                  </button>
                  <button type="button" class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-twitter color-primary"></i>
                  </button>
                  <button type="button" class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-github color-primary"></i>
                  </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </section>
  <!--Section: Login Block-->
  <script src="../assets/icons/all.min.js"></script>
  <script>
    $(function() {
      $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        let error = $('#error');
        error.addClass('d-none');
        $.ajax({
          type: 'POST',
          url: 'do.login.php',
          data: $(this).serialize()
        }).then(function(res) {
          let data = JSON.parse(res);
          if (data.error) {
            error.removeClass('d-none').html(data.error);
            return;
          }
          localStorage.setItem('token', data.token);
          if (document.referrer) {
            location.href = document.referrer;
          } else {
            location.href = "../index.php";
          }
        }).fail(function(res) {
          error.removeClass('d-none').html("Server Error");
        });
      });
    });
    $(function() {
      $("#returnBtn").on('click', function() {
        if (document.referrer) {
          location.href = document.referrer;
        } else {
          location.href = "../index.php";
        }
      });
    });
  </script>
</body>

</html>