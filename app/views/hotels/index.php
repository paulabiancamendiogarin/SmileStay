

<?php $pageTitle = 'Hotels in Bacolod City - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
      
        <div class="row mb-4">
            <div class="col-lg-8">
                <h1 class="fw-bold">Hotels in Bacolod City</h1>
                <p class="text-muted">Browse and book from <?= count($hotels) ?> hotels in the City of Smiles</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= APP_URL ?>/map" class="btn btn-outline-primary">
                    <i class="bi bi-geo-alt me-2"></i>View on Map
                </a>
            </div>
        </div>

    
        <div class="card mb-4">
            <div class="card-body">
                <form action="<?= APP_URL ?>/hotels" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Search Hotels</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Hotel name or location..."
                                       value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Price</label>
                            <input type="number" name="min_price" class="form-control" 
                                   placeholder="₱0" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Price</label>
                            <input type="number" name="max_price" class="form-control" 
                                   placeholder="₱10000" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

       
        <?php if (empty($hotels)): ?>
            <div class="text-center py-5">
                <i class="bi bi-building display-1 text-muted"></i>
                <h4 class="mt-3">No hotels found</h4>
                <p class="text-muted">Try adjusting your search criteria</p>
                <a href="<?= APP_URL ?>/hotels" class="btn btn-primary">View All Hotels</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card hotel-card h-100">
                            <div class="hotel-card-img">
                              <img src="<?= APP_URL ?>/uploads/hotels/<?= $hotel['image'] ?? 'default.jpg' ?>"
     onerror="this.onerror=null;this.src='<?= APP_URL ?>/uploads/hotels/default.jpg';"
     class="img-fluid rounded shadow">
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
                                
                                
                                <?php $amenities = !empty($hotel['amenities']) ? array_slice(explode(',', $hotel['amenities']), 0, 3) : []; ?>
                                <?php if (!empty($amenities)): ?>
                                    <div class="mb-2">
                                        <?php foreach ($amenities as $amenity): ?>
                                            <span class="badge bg-light text-dark me-1 mb-1"><?= trim($amenity) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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
        <?php endif; ?>
    </div>
</section>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>