

<?php $pageTitle = 'Page Not Found - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
            <h1 class="display-4 mt-4">404</h1>
            <h2>Page Not Found</h2>
            <p class="text-muted">The page you are looking for does not exist or has been moved.</p>
            <a href="<?= APP_URL ?>" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-house me-2"></i>Go to Home
            </a>
        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>