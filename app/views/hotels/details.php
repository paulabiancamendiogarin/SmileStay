

<?php $pageTitle = $hotel['hotel_name'] . ' - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-5">
    <div class="container">
      

        <div class="row">
           
            <div class="col-lg-8">
                
                <div class="hotel-detail-img mb-4">
                   <img src="<?= APP_URL ?>/uploads/hotels/<?= $hotel['image'] ?? 'default.jpg' ?>"
     onerror="this.onerror=null;this.src='<?= APP_URL ?>/uploads/hotels/default.jpg';"
     class="img-fluid rounded shadow">
                        
                </div>

             
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="h2 fw-bold mb-1"><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($hotel['location']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="hotel-rating-lg mb-1">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <span class="h4 mb-0"><?= number_format($hotel['rating'], 1) ?></span>
                                </div>
                                <small class="text-muted">Rating</small>
                            </div>
                        </div>

                        <hr>

                        <h5>About This Hotel</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($hotel['description'])) ?></p>

                      
                        <?php if (!empty($amenities)): ?>
                            <h5 class="mt-4">Amenities</h5>
                            <div class="row">
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="col-6 col-md-4 mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <?= trim($amenity) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

          
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="hotelMap" style="height: 300px; width: 100%;"></div>
                    </div>
                </div>

                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-door-open me-2"></i>Available Rooms</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($rooms)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-door-closed display-4 text-muted"></i>
                                <p class="text-muted mt-2">No rooms available at this time</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($rooms as $room): ?>
                                <div class="room-card p-3 border rounded mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-1"><?= htmlspecialchars($room['room_type']) ?></h6>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-people me-1"></i>Max <?= $room['capacity'] ?> guests
                                            </p>
                                            <?php if (!empty($room['description'])): ?>
                                                <p class="text-muted small mb-0"><?= htmlspecialchars($room['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <span class="badge bg-success"><?= $room['available'] ?> Available</span>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="h5 text-primary mb-1">₱<?= number_format($room['price'], 0) ?></div>
                                            <small class="text-muted d-block mb-2">per night</small>
                                            <a href="<?= APP_URL ?>/booking?hotel_id=<?= $hotel['id'] ?>&room_id=<?= $room['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                                Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="h3 text-primary mb-0">₱<?= number_format($hotel['price_per_night'], 0) ?></div>
                            <small class="text-muted">Starting from / night</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Location</small>
                            <span><?= htmlspecialchars($hotel['location']) ?></span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Rating</small>
                            <span>
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <?= number_format($hotel['rating'], 1) ?> / 5.0
                            </span>
                        </div>

                        <?php if (!empty($rooms)): ?>
                            <a href="#rooms" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-calendar-check me-2"></i>Book Now
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 btn-lg" disabled>
                                No Rooms Available
                            </button>
                        <?php endif; ?>

                        <a href="<?= APP_URL ?>/hotels" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left me-2"></i>Back to Hotels
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?= $hotel['latitude'] ?>;
    const lng = <?= $hotel['longitude'] ?>;
    
    const map = new google.maps.Map(document.getElementById('hotelMap'), {
        center: { lat: lat, lng: lng },
        zoom: 15,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });

    new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: '<?= addslashes($hotel['hotel_name']) ?>'
    });
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
