

<?php $pageTitle = 'Welcome - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>


<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    Discover the Best Hotels in <span class="text-primary">Bacolod City</span>
                </h1>
                <p class="lead text-muted mb-4">
                    Find your perfect stay in the City of Smiles. Browse our curated selection of hotels, from budget-friendly inns to luxury accommodations.
                </p>
                
               
                <form action="<?= APP_URL ?>/hotels" method="GET" class="search-form">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Search hotels in Bacolod City...">
                        <button type="submit" class="btn btn-primary px-4">Search</button>
                    </div>
                </form>

                <div class="mt-4 d-flex gap-4">
                    <div class="text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= $totalHotels ?></h3>
                        <small class="text-muted">Hotels Listed</small>
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold text-primary mb-0">4.5</h3>
                        <small class="text-muted">Avg Rating</small>
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold text-primary mb-0">1000+</h3>
                        <small class="text-muted">Happy Guests</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800" 
                         alt="Hotel in Bacolod" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Hotels in Bacolod City</h2>
            <p class="text-muted">Handpicked accommodations for an unforgettable stay</p>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredHotels as $hotel): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card hotel-card h-100">
                        <div class="hotel-card-img">
                           <img src="<?= APP_URL ?>/uploads/hotels/<?= $hotel['image'] ?? 'default.jpg' ?>"
     onerror="this.onerror=null;this.src='<?= APP_URL ?>/uploads/hotels/default.jpg';"
     class="card-img-top img-fluid rounded shadow"
     alt="<?= htmlspecialchars($hotel['hotel_name']) ?>">
                                 class="card-img-top" alt="<?= htmlspecialchars($hotel['hotel_name']) ?>">
                            <div class="hotel-rating">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <?= number_format($hotel['rating'], 1) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($hotel['hotel_name']) ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($hotel['location']) ?>
                            </p>
                            <p class="card-text text-muted small">
                                <?= substr(htmlspecialchars($hotel['description']), 0, 100) ?>...
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h5 text-primary mb-0">₱<?= number_format($hotel['price_per_night'], 0) ?></span>
                                    <small class="text-muted">/ night</small>
                                </div>
                                <a href="<?= APP_URL ?>/hotel/<?= $hotel['id'] ?>" class="btn btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= APP_URL ?>/hotels" class="btn btn-primary btn-lg">
                <i class="bi bi-buildings me-2"></i>View All Hotels
            </a>
            <a href="<?= APP_URL ?>/map" class="btn btn-outline-primary btn-lg ms-2">
                <i class="bi bi-geo-alt me-2"></i>View on Map
            </a>
        </div>
    </div>
</section>


<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5>Easy Search</h5>
                    <p class="text-muted">Find hotels quickly with our powerful search and filter options.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>Map Integration</h5>
                    <p class="text-muted">View all hotels on an interactive map with exact locations.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Easy Booking</h5>
                    <p class="text-muted">Book your stay in just a few clicks with instant confirmation.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="<?= APP_URL ?>/uploads/hotels/hotell.jpg"
     onerror="this.onerror=null;this.src='<?= APP_URL ?>/uploads/hotels/default.jpg';"
     class="img-fluid rounded shadow">
                    
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Why Stay in Bacolod City?</h2>
                <p class="text-muted mb-4">
                    Known as the "City of Smiles," Bacolod is a vibrant city in the Philippines famous for its warm hospitality, 
                    delicious cuisine, and the world-renowned MassKara Festival. Whether you're visiting for business or leisure, 
                    Bacolod offers a unique blend of culture, history, and modern amenities.
                </p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Home to the famous MassKara Festival</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Renowned for delicious local cuisine</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Rich heritage and cultural sites</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Gateway to Negros Island attractions</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
