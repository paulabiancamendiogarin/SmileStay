<?php $pageTitle = 'Edit Room'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Hotel</label>
                        <select name="hotel_id" class="form-select" required>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?= $hotel['id'] ?>" 
                                    <?= $room['hotel_id'] == $hotel['id'] ? 'selected' : '' ?>>
                                    <?= $hotel['hotel_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Room Type</label>
                        <input type="text" name="room_type" class="form-control"
                               value="<?= $room['room_type'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Capacity</label>
                        <input type="number" name="capacity" class="form-control"
                               value="<?= $room['capacity'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Price</label>
                        <input type="number" name="price" class="form-control"
                               value="<?= $room['price'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Available Rooms</label>
                        <input type="number" name="available" class="form-control"
                               value="<?= $room['available'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?= $room['description'] ?></textarea>
                    </div>

                    <button class="btn btn-primary">Update Room</button>
                    <a href="<?= APP_URL ?>/admin-rooms" class="btn btn-secondary">Cancel</a>

                </form>

            </div>
        </div>
    </div>
</div>