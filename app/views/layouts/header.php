<?php
$pageTitle = $pageTitle ?? APP_NAME;
$currentUrl = $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Find and book the best hotels in Bacolod City, Philippines.">
    <meta name="author" content="<?= APP_NAME ?>">

    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link rel="icon" href="<?= APP_URL ?>/images/favicon.ico">

   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
    <link href="<?= APP_URL ?>/css/style.css" rel="stylesheet">

  
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&libraries=places"></script>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">

        
        <a class="navbar-brand fw-bold" href="<?= isAdmin() ? APP_URL . '/admin' : APP_URL ?>">
            <i class="bi bi-building me-2"></i><?= APP_NAME ?>
        </a>

       
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        
        <div class="collapse navbar-collapse" id="navbarNav">

           
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link <?= $currentUrl == '/' ? 'active fw-semibold' : '' ?>" 
                       href="<?= isAdmin() ? APP_URL . '/admin' : APP_URL ?>">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= str_contains($currentUrl, 'hotels') ? 'active fw-semibold' : '' ?>" 
                       href="<?= APP_URL ?>/hotels">
                        <i class="bi bi-buildings me-1"></i>Hotels
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= str_contains($currentUrl, 'map') ? 'active fw-semibold' : '' ?>" 
                       href="<?= APP_URL ?>/map">
                        <i class="bi bi-geo-alt me-1"></i>Map View
                    </a>
                </li>

            </ul>

         
            <ul class="navbar-nav align-items-center">

                <?php if (isLoggedIn()): ?>

                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold d-flex align-items-center"
                           href="#" role="button" data-bs-toggle="dropdown">

                            <i class="bi bi-person-circle fs-5 me-2"></i>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                            <?php if (isAdmin()): ?>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/admin">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/admin-bookings">
                                        <i class="bi bi-calendar-check me-2"></i>Manage Bookings
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/profile">
                                        <i class="bi bi-person me-2"></i>My Profile
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                            <?php else: ?>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/dashboard">
                                        <i class="bi bi-grid me-2"></i>Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/my-bookings">
                                        <i class="bi bi-calendar-check me-2"></i>My Bookings
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/profile">
                                        <i class="bi bi-person me-2"></i>My Profile
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>

                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-2 px-3" href="<?= APP_URL ?>/register">
                            Register
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>


<?php $flash = getFlashMessage(); ?>
<?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>


<main class="min-vh-100">