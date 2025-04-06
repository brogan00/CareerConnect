<?php
session_start();

if (isset($_SESSION['user_email'])) {
  header("Location: ../index.php");
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../assets/CSS/connexion.css">
  <link rel="stylesheet" href="../assets/icons/all.min.css">
  <link rel="stylesheet" href="../assets/CSS/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/CSS/style.css">
  <script src="../assets/JS/jquery-3.7.1.js"></script>
</head>

<body class="h-100 w-100 d-flex flex-column align-items-center justify-content-center background-radial-gradient">
  <!-- Section: Design Block -->
  <a id="returnBtn" class="btn position-absolute top-0 start-0 m-4 px-4 py-2 fw-bold text-white"
    style="background: linear-gradient(45deg, #6a15e0, #6a15e0); border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); transition: 0.3s;">
    ← Back
  </a>
  <section class="overflow-hidden">
    <div class="container px-4 py-5 px-md-5 text-center text-lg-start my-5">
      <div class="row gx-lg-5 align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0" style="z-index: 10">
          <h1 class="my-5 display-5 fw-bold ls-tight" style="color: hsl(218, 81%, 95%)">
            The best offer <br />
            <span style="color: hsl(218, 81%, 75%)">for your business</span>
          </h1>
          <p class="mb-4 opacity-70" style="color: hsl(218, 81%, 85%)">
            Struggling to reach your business goals? Discover the perfect solutions to overcome challenges and achieve
            lasting success.
          </p>
        </div>

        <div class="col-lg-6 mb-5 mb-lg-0 position-relative">
          <div id="radius-shape-1" class="position-absolute rounded-circle shadow-5-strong"></div>
          <div id="radius-shape-2" class="position-absolute shadow-5-strong"></div>

          <div class="card bg-glass">
            <div class="card-body px-4 py-5 px-md-5">
              <form id="signupForm" action="signup.php" method="POST">
                <!-- 2 column grid layout with text inputs for the first and last names -->
                <div id="error" class="form-outline mb-4 text-danger d-none"></div>
                <div class="row">
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <label class="form-label" for="first name">First name</label>
                      <input type="text" name="first_name" class="form-control" required placeholder="First Name">
                    </div>
                  </div>
                  <div class="col-md-6 mb-4">
                    <div data-mdb-input-init class="form-outline">
                      <label class="form-label" for="last name">Last name</label>
                      <input type="text" name="last_name" class="form-control" required placeholder="Last Name">
                    </div>
                  </div>
                </div>

                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-4">
                  <label class="form-label" for="email">Email address</label>
                  <input type="email" name="email" class="form-control" required placeholder="Email">

                </div>

                <!-- Password input -->
                <div data-mdb-input-init class="form-outline mb-4">
                  <label class="form-label" for="password">Password</label>
                  <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>

                <!-- Checkbox -->
                <div class="form-check d-flex justify-content-center mb-4">
                  <input class="form-check-input me-2" type="checkbox" value="" id="form2Example33" checked />
                  <label class="form-check-label" for="form2Example33">
                    Subscribe to our newsletter
                  </label>
                </div>

                <!-- Submit button -->
                <button type="submit" data-mdb-button-init data-mdb-ripple-init
                  class="btn btn-primary btn-block mb-4 w-100">
                  Sign up
                </button>

                <!-- Register buttons -->
                <div class="text-center">
                  <p>or sign up with:</p>
                  <button type="button" data-mdb-button-init data-mdb-ripple-init
                    class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-facebook-f color-primary"></i>
                  </button>

                  <button type="button" data-mdb-button-init data-mdb-ripple-init
                    class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-google color-primary"></i>
                  </button>

                  <button type="button" data-mdb-button-init data-mdb-ripple-init
                    class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-twitter color-primary"></i>
                  </button>

                  <button type="button" data-mdb-button-init data-mdb-ripple-init
                    class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-github color-primary"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Section: Design Block -->

  <script src="../assets/icons/all.min.js"></script>
  <script>
    $(function() {
      $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        let error = $('#error');
        error.addClass('d-none');
        $.ajax({
          type: 'POST',
          url: 'do.signup.php',
          data: $(this).serialize()
        }).then(function(res) {
          let data = JSON.parse(res);
          if (data.error) {
            error.removeClass('d-none').html(data.error);
            return;
          }
          //localStorage.setItem('token', data.token);
          if (document.referrer) {
            location.href = document.referrer;
          } else {
            location.href = "../index.php";
          }
        }).fail(function(res) {
          error.removeClass('d-none').html("Server Error");
        })
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