

<?php $pageTitle = 'Hotel Map - ' . APP_NAME; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<section class="py-4">
    <div class="container-fluid px-4">
        <div class="row mb-3">
            <div class="col">
                <h2 class="fw-bold"><i class="bi bi-geo-alt me-2"></i>Hotels in Bacolod City</h2>
                <p class="text-muted mb-0">Explore all hotels on the interactive map</p>
            </div>
            <div class="col-auto">
                <a href="<?= APP_URL ?>/hotels" class="btn btn-outline-primary">
                    <i class="bi bi-list me-2"></i>List View
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Map -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="mainMap" style="height: 600px; width: 100%;"></div>
                    </div>
                </div>
            </div>

           
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Hotels (<?= count(json_decode($hotelsJson, true)) ?>)</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 540px; overflow-y: auto;">
                        <div class="list-group list-group-flush" id="hotelList">
                            <?php $hotelsArray = json_decode($hotelsJson, true); ?>
                            <?php foreach ($hotelsArray as $hotel): ?>
                                <a href="#" class="list-group-item list-group-item-action hotel-list-item" 
                                   data-lat="<?= $hotel['latitude'] ?>" data-lng="<?= $hotel['longitude'] ?>"
                                   data-id="<?= $hotel['id'] ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($hotel['hotel_name']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                                <?= number_format($hotel['rating'], 1) ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">₱<?= number_format($hotel['price_per_night'], 0) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hotels = <?= $hotelsJson ?>;
    const bacolodCenter = { lat: <?= BACOLOD_LAT ?>, lng: <?= BACOLOD_LNG ?> };

   
    const map = new google.maps.Map(document.getElementById('mainMap'), {
        center: bacolodCenter,
        zoom: 14,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });

    const markers = [];
    const infoWindow = new google.maps.InfoWindow();

   
    hotels.forEach(function(hotel) {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) },
            map: map,
            title: hotel.hotel_name,
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });

       
        const content = `
            <div style="max-width: 200px;">
                <h6>${hotel.hotel_name}</h6>
                <p class="small text-muted mb-1">
                    <i class="bi bi-star-fill text-warning"></i> ${parseFloat(hotel.rating).toFixed(1)}
                </p>
                <p class="small mb-2">₱${parseFloat(hotel.price_per_night).toLocaleString()} / night</p>
                <a href="<?= APP_URL ?>/hotel/${hotel.id}" class="btn btn-primary btn-sm">View Details</a>
            </div>
        `;

        marker.addListener('click', function() {
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
        });

        markers.push({ marker: marker, id: hotel.id });
    });

    
    document.querySelectorAll('.hotel-list-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            const id = parseInt(this.dataset.id);

            map.panTo({ lat: lat, lng: lng });
            map.setZoom(16);

          
            markers.forEach(function(m) {
                if (m.id === id) {
                    google.maps.event.trigger(m.marker, 'click');
                }
            });
        });
    });
});
</script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
