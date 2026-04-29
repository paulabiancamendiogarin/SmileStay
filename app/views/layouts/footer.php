    </main>


<footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">

            
            <div class="col-lg-4 mb-4">
                <h5 class="fw-bold">
                    <i class="bi bi-building me-2"></i><?= APP_NAME ?>
                </h5>
                <p class="text-muted">
                    Find the perfect hotel in Bacolod City, Philippines. 
                    Explore from budget-friendly to luxury stays.
                </p>
            </div>

          
            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="fw-semibold">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= APP_URL ?>" class="text-decoration-none text-muted">Home</a></li>
                    <li><a href="<?= APP_URL ?>/hotels" class="text-decoration-none text-muted">Hotels</a></li>
                    <li><a href="<?= APP_URL ?>/map" class="text-decoration-none text-muted">Map</a></li>
                </ul>
            </div>

           
            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="fw-semibold">Account</h6>
                <ul class="list-unstyled">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= APP_URL ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li><a href="<?= APP_URL ?>/my-bookings" class="text-decoration-none text-muted">Bookings</a></li>
                        <li><a href="<?= APP_URL ?>/logout" class="text-decoration-none text-danger">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= APP_URL ?>/login" class="text-decoration-none text-muted">Login</a></li>
                        <li><a href="<?= APP_URL ?>/register" class="text-decoration-none text-muted">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            
            <div class="col-lg-4 col-md-4 mb-4">
                <h6 class="fw-semibold">Contact</h6>
                <ul class="list-unstyled text-muted">
                    <li><i class="bi bi-geo-alt me-2"></i>Bacolod City, Philippines</li>
                    <li><i class="bi bi-envelope me-2"></i>info@bacolodhotels.com</li>
                    <li><i class="bi bi-phone me-2"></i>+63 34 123 4567</li>
                </ul>
            </div>

        </div>

        <hr class="border-secondary">

        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 text-muted">
                    Made with <i class="bi bi-heart-fill text-danger"></i> in Bacolod
                </p>
            </div>
        </div>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/js/main.js"></script>

</body>
</html>