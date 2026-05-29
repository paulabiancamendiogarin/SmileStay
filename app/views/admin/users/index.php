<?php $pageTitle = 'User Approvals'; ?>
<?php include APP_PATH . '/views/layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-people me-2"></i>User Management</h4>
            <p class="text-muted mb-0 small">Approve new accounts before they can book hotels.</p>
        </div>
        <?php if (!empty($pendingUsers)): ?>
            <span class="badge bg-warning text-dark fs-6"><?= count($pendingUsers) ?> pending approval</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($pendingUsers)): ?>
    <div class="card shadow-sm mb-4 border-warning border-opacity-50">
        <div class="card-header bg-warning bg-opacity-10">
            <strong><i class="bi bi-hourglass-split me-2"></i>Pending Approval</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>QR Verified</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= !empty($u['qr_verified']) ? 'success' : 'secondary' ?>">
                                    <?= !empty($u['qr_verified']) ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>
                                <form method="POST" action="<?= APP_URL ?>/admin-approve-user" class="d-inline"
                                      onsubmit="return confirm('Approve this user?');">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg me-1"></i>Approve
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success mb-4">
        <i class="bi bi-check-circle me-2"></i>No users waiting for approval.
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>All Customers</strong>
        </div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
                <p class="text-muted text-center py-4 mb-0">No customer accounts yet.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>QR</th>
                            <th>Approval</th>
                            <th>Approved At</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= !empty($u['qr_verified']) ? 'success' : 'secondary' ?>">
                                    <?= !empty($u['qr_verified']) ? 'Verified' : 'No' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= approvalBadgeClass(!empty($u['is_approved'])) ?>">
                                    <?= !empty($u['is_approved']) ? 'Approved' : 'Pending' ?>
                                </span>
                            </td>
                            <td>
                                <?= !empty($u['approved_at']) ? date('M d, Y g:i A', strtotime($u['approved_at'])) : '—' ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
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
