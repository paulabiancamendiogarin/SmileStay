

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> - <?= APP_NAME ?></title>
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
   
    <link href="<?= APP_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="d-flex" id="wrapper">
       
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?= APP_URL ?>/admin" class="text-decoration-none text-white">
                    <i class="bi bi-building me-2"></i><?= APP_NAME ?>
                </a>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false && strpos($_SERVER['REQUEST_URI'], 'admin-') === false ? 'active' : '' ?>" href="<?= APP_URL ?>/admin">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'admin-hotels') !== false ? 'active' : '' ?>" href="<?= APP_URL ?>/admin-hotels">
                        <i class="bi bi-buildings me-2"></i>Hotels
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'admin-rooms') !== false ? 'active' : '' ?>" href="<?= APP_URL ?>/admin-rooms">
                        <i class="bi bi-door-open me-2"></i>Rooms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'admin-bookings') !== false ? 'active' : '' ?>" href="<?= APP_URL ?>/admin-bookings">
                        <i class="bi bi-calendar-check me-2"></i>Bookings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="<?= APP_URL ?>">
                        <i class="bi bi-house me-2"></i>View Site
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= APP_URL ?>/logout">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>

      
        <div class="admin-content">
           
            <nav class="admin-topnav">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h4 class="mb-0"><?= $pageTitle ?? 'Dashboard' ?></h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= $_SESSION['user_name'] ?? 'Admin' ?>
                        </span>
                    </div>
                </div>
            </nav>

            
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show mx-4 mt-3">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="admin-main">