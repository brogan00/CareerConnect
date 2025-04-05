<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed!');
}
?>

<!-- Footer -->
<footer class="mt-5">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold text-primary mb-3">About CareerConnect</h5>
                <p class="text-muted">
                    CareerConnect is your gateway to finding the perfect job or
                    candidate. We connect top talent with leading companies worldwide.
                </p>

                <div class="d-flex align-items-center">
                    <img
                        src="assets/images/logo.png"
                        alt="CareerConnect Logo"
                        width="100"
                        class="" />
                    <div class="d-flex justify-content-center">
                        <a href="#">
                            <button type="button" class="btn btn-link btn-floating mx-1 color-primary">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                        </a>
                        <a href="">
                            <button type="button" class="btn btn-link btn-floating mx-1 color-primary">
                                <i class="fab fa-google"></i>
                            </button>
                        </a>

                        <a href="#">
                            <button type="button" class="btn btn-link btn-floating mx-1 color-primary">
                                <i class="fab fa-twitter"></i>
                            </button>
                        </a>
                        <a href="https://github.com/brogan00" target="_blank">
                            <button type="button" class="btn btn-link btn-floating mx-1 color-primary">
                                <i class="fab fa-github"></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold text-primary mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="">
                        <a
                            href="job_search.php"
                            class="btn text-muted text-decoration-none">Search Jobs</a>
                    </li>
                    <li class="">
                        <a
                            href="company_search.php"
                            class="btn text-muted text-decoration-none">Find Companies</a>
                    </li>
                    <li class="">
                        <a
                            href="post_a_job.php"
                            class="btn text-muted text-decoration-none">Post a Job</a>
                    </li>
                    <li class="">
                        <a
                            href="upload_cv.php"
                            class="btn text-muted text-decoration-none">Upload CV</a>
                    </li>
                    <li class="">
                        <a
                            href="career_resources.php"
                            class="btn text-muted text-decoration-none">Career Resources</a>
                    </li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-primary mb-3 ">Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <span class="text-muted">algeria .tiaret zaroura </span>
                    </li>
                    <li class="mb-2 ">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <span class="text-muted">+213552595513</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <span class="text-muted">fares.elhabash@univ-tiaret.dz</span>
                    </li>
                </ul>
            </div>
            <!-- Newsletter Subscription -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-primary mb-3">Subscribe to Our Newsletter</h5>
                <p class="text-muted">
                    Get the latest job alerts and career tips directly in your inbox.
                </p>
                <form>
                    <div class="input-group">
                        <input
                            type="email"
                            class="form-control"
                            placeholder="Your email"
                            aria-label="Your email" />
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Copyright -->
        <div class="border-top mt-3 mb-3"></div>
        <div class="text-center">
            <p class="text-muted">
                &copy; 2025 CareerConnect. All rights reserved.
            </p>
        </div>
    </div>
</footer>


<script src="../assets/JS/jquery-3.7.1.js"></script>
<script src="../assets/JS/bootstrap.min.js"></script>
<script src="../assets/icons/all.min.js"></script>