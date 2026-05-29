<?php $pageTitle = 'Notifications'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-bell me-2"></i>Notifications</h4>
            <p class="text-muted mb-0 small">New bookings, approvals, and payment updates.</p>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin-notifications-read">
            <button class="btn btn-sm btn-outline-primary" type="submit">
                <i class="bi bi-check2-all me-1"></i>Mark all read
            </button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">No notifications yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $n): ?>
                            <tr class="<?= !empty($n['is_read']) ? '' : 'table-warning' ?>">
                                <td>
                                    <span class="badge bg-<?= !empty($n['is_read']) ? 'secondary' : 'warning text-dark' ?>">
                                        <?= !empty($n['is_read']) ? 'Read' : 'New' ?>
                                    </span>
                                </td>
                                <td><code><?= htmlspecialchars($n['type']) ?></code></td>
                                <td><strong><?= htmlspecialchars($n['title']) ?></strong></td>
                                <td class="text-muted small"><?= htmlspecialchars($n['message'] ?? '') ?></td>
                                <td class="text-nowrap small"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/admin_footer.php'; ?>

