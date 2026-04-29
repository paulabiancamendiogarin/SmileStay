

<?php $pageTitle = 'Manage Rooms'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <?php if ($hotel): ?>
            <p class="text-muted mb-0">Rooms for: <strong><?= htmlspecialchars($hotel['hotel_name']) ?></strong></p>
        <?php else: ?>
            <p class="text-muted mb-0">All rooms across hotels</p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <?php if ($hotel): ?>
            <a href="<?= APP_URL ?>/admin-rooms" class="btn btn-outline-secondary">View All Rooms</a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/admin-add-room<?= $hotel ? '?hotel_id=' . $hotel['id'] : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus me-2"></i>Add Room
        </a>
    </div>
</div>

<!-- Filter by Hotel -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= APP_URL ?>/admin-rooms" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Filter by Hotel</label>
                <select name="hotel_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Hotels</option>
                    <?php foreach ($hotels as $h): ?>
                        <option value="<?= $h['id'] ?>" <?= ($_GET['hotel_id'] ?? '') == $h['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($h['hotel_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rooms)): ?>
            <div class="text-center py-5">
                <i class="bi bi-door-open display-1 text-muted"></i>
                <h4 class="mt-3">No Rooms Found</h4>
                <a href="<?= APP_URL ?>/admin-add-room" class="btn btn-primary">
                    <i class="bi bi-plus me-2"></i>Add Room
                </a>
                <a href="<?= APP_URL ?>/admin-edit-room/<?= $room['id'] ?>" 
   class="btn btn-outline-primary" title="Edit">
    <i class="bi bi-pencil"></i>
</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Hotel</th>
                            <th>Room Type</th>
                            <th>Capacity</th>
                            <th>Price/Night</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?= htmlspecialchars($room['hotel_name'] ?? 'N/A') ?></td>
                                <td><strong><?= htmlspecialchars($room['room_type']) ?></strong></td>
                                <td><?= $room['capacity'] ?> guests</td>
                                <td>₱<?= number_format($room['price'], 0) ?></td>
                                <td>
                                    <span class="badge bg-<?= $room['available'] > 0 ? 'success' : 'danger' ?>">
                                        <?= $room['available'] ?>
                                    </span>
                                </td>
             <td>
    <div class="btn-group btn-group-sm">

       

        <!-- EDIT ROOM -->
        <a href="<?= APP_URL ?>/admin-add-room?room_id=<?= $room['id'] ?>" 
   class="btn btn-outline-primary" title="Edit">
    <i class="bi bi-pencil"></i>
</a>

        <!-- DELETE ROOM -->
        <form action="<?= APP_URL ?>/admin-delete-room" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this room?');">
            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
            <input type="hidden" name="hotel_id" value="<?= $room['hotel_id'] ?>">
            <button type="submit" class="btn btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>

    </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>