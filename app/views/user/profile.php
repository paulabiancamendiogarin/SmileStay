<?php $pageTitle = 'My Profile - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row">

          
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h5 class="mb-3">Account</h5>

                        <ul class="nav flex-column">
                            <li class="nav-item mb-2">
                                <a href="<?= APP_URL ?>/my-bookings" class="nav-link text-dark">
                                    <i class="bi bi-calendar-check me-2"></i>My Bookings
                                </a>
                            </li>

                            <li class="nav-item mb-2">
                                <a href="<?= APP_URL ?>/profile" class="nav-link active bg-light rounded">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= APP_URL ?>/logout" class="nav-link text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>

         
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-4">My Profile</h4>

                        <?php $flash = getFlashMessage(); ?>
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                                <?= htmlspecialchars($flash['message']) ?>
                            </div>
                        <?php endif; ?>

                      
                        <form action="<?= APP_URL ?>/update-profile" method="POST">

                         
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>"
                                       required>
                            </div>

                           
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" 
                                       name="email"
                                       class="form-control"
                                       value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>"
                                       readonly>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>

                            
                            <div class="mb-3">
                                <label class="form-label">New Password</label>

                                <div class="input-group">
                                    <input type="password" name="password" class="form-control">

                                    
                                </div>

                                <small class="text-muted">
                                    Leave blank if you don’t want to change password
                                </small>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>

                        </form>

                    </div>
                </div>

                
                <div class="text-center mt-4">
                    <form action="<?= APP_URL ?>/delete-account" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete your account?');">
                        <button class="btn btn-link text-danger">
                            Delete my account
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>