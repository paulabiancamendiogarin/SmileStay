
<?php $pageTitle = 'Edit Hotel'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="<?= APP_URL ?>/admin-edit-hotel/<?= $hotel['id'] ?>" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Hotel Name *</label>
                            <input type="text" class="form-control" name="hotel_name" required
                                   value="<?= htmlspecialchars($hotel['hotel_name']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= $hotel['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $hotel['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Location / Address *</label>
                            <input type="text" class="form-control" name="location" required
                                   value="<?= htmlspecialchars($hotel['location']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude *</label>
                            <input type="number" class="form-control" name="latitude" step="0.00000001" required
                                   value="<?= $hotel['latitude'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude *</label>
                            <input type="number" class="form-control" name="longitude" step="0.00000001" required
                                   value="<?= $hotel['longitude'] ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($hotel['description']) ?></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Price Per Night (₱) *</label>
                            <input type="number" class="form-control" name="price_per_night" step="0.01" required
                                   value="<?= $hotel['price_per_night'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rating (0-5)</label>
                            <input type="number" class="form-control" name="rating" step="0.1" min="0" max="5"
                                   value="<?= $hotel['rating'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hotel Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Current: <?= $hotel['image'] ?></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Amenities</label>
                            <input type="text" class="form-control" name="amenities"
                                   value="<?= htmlspecialchars($hotel['amenities']) ?>">
                        </div>

                        <div class="col-12">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check me-2"></i>Save Changes
                                </button>
                                <a href="<?= APP_URL ?>/admin-hotels" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>