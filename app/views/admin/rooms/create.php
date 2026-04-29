<?php $pageTitle = isset($room) ? 'Edit Room' : 'Add Room'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form action="<?= APP_URL ?>/admin-add-room" method="POST">

                    <!-- ✅ IMPORTANT: hidden ID for edit -->
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?? '' ?>">

                    <div class="row g-3">

                        <!-- HOTEL -->
                        <div class="col-12">
                            <label class="form-label">Hotel *</label>
                            <select name="hotel_id" class="form-select" required>
                                <option value="">Select Hotel</option>
                                <?php foreach ($hotels as $h): ?>
                                    <option value="<?= $h['id'] ?>" 
                                        <?= ($room['hotel_id'] ?? ($_GET['hotel_id'] ?? '')) == $h['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($h['hotel_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ROOM TYPE -->
                        <div class="col-md-6">
                            <label class="form-label">Room Type *</label>
                            <input type="text" class="form-control" name="room_type" required
                                   value="<?= $room['room_type'] ?? '' ?>"
                                   placeholder="e.g., Deluxe Room, Suite, Standard">
                        </div>

                        <!-- CAPACITY -->
                        <div class="col-md-6">
                            <label class="form-label">Capacity (Guests) *</label>
                            <input type="number" class="form-control" name="capacity"
                                   min="1" max="10" required
                                   value="<?= $room['capacity'] ?? 2 ?>">
                        </div>

                        <!-- PRICE -->
                        <div class="col-md-6">
                            <label class="form-label">Price per Night (₱) *</label>
                            <input type="number" class="form-control" name="price"
                                   step="0.01" required
                                   value="<?= $room['price'] ?? '' ?>">
                        </div>

                        <!-- AVAILABLE -->
                        <div class="col-md-6">
                            <label class="form-label">Available Rooms *</label>
                            <input type="number" class="form-control" name="available"
                                   min="0" required
                                   value="<?= $room['available'] ?? 1 ?>">
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?= $room['description'] ?? '' ?></textarea>
                        </div>

                        <!-- BUTTONS -->
                        <div class="col-12">
                            <hr>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi <?= isset($room) ? 'bi-save' : 'bi-plus' ?> me-2"></i>
                                <?= isset($room) ? 'Update Room' : 'Add Room' ?>
                            </button>

                            <a href="<?= APP_URL ?>/admin-rooms<?= isset($room) ? '?hotel_id=' . $room['hotel_id'] : '' ?>" 
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>

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