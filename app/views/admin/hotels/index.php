

<?php $pageTitle = 'Manage Hotels'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0"><?= count($hotels) ?> hotels listed</p>
    <a href="<?= APP_URL ?>/admin-add-hotel" class="btn btn-primary">
        <i class="bi bi-plus me-2"></i>Add Hotel
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($hotels)): ?>
            <div class="text-center py-5">
                <i class="bi bi-building display-1 text-muted"></i>
                <h4 class="mt-3">No Hotels Yet</h4>
                <p class="text-muted">Start by adding your first hotel.</p>
                <a href="<?= APP_URL ?>/admin-add-hotel" class="btn btn-primary">
                    <i class="bi bi-plus me-2"></i>Add Hotel
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Hotel</th>
                            <th>Location</th>
                            <th>Price/Night</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hotels as $hotel): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($hotel['hotel_name']) ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars(substr($hotel['location'], 0, 40)) ?>...</small>
                                </td>
                                <td>₱<?= number_format($hotel['price_per_night'], 0) ?></td>
                                <td>
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <?= number_format($hotel['rating'], 1) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $hotel['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($hotel['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= APP_URL ?>/hotel/<?= $hotel['id'] ?>" class="btn btn-outline-secondary" target="_blank" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/admin-edit-hotel/<?= $hotel['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <form action="<?= APP_URL ?>/admin-delete-hotel" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this hotel?');">
                                            <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
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