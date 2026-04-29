<?php $pageTitle = 'Edit Booking'; ?>
<?php include APP_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="card">
        <div class="card-body">

            <h4 class="mb-4">Edit Booking</h4>

            <form action="<?= APP_URL ?>/update-booking/<?= $booking['id'] ?>" method="POST">

                <div class="mb-3">
                    <label>Check-in</label>
                    <input type="date" name="check_in" class="form-control"
                           value="<?= $booking['check_in'] ?>" required>
                </div>

                <div class="mb-3">
                    <label>Check-out</label>
                    <input type="date" name="check_out" class="form-control"
                           value="<?= $booking['check_out'] ?>" required>
                </div>

                <div class="mb-3">
                    <label>Guests</label>
                    <input type="number" name="guests" class="form-control"
                           value="<?= $booking['guests'] ?>" min="1">
                </div>

                <div class="mb-3">
                    <label>Special Requests</label>
                    <textarea name="special_requests" class="form-control"><?= $booking['special_requests'] ?></textarea>
                </div>

                <button class="btn btn-primary">Save Changes</button>
                <a href="<?= APP_URL ?>/my-bookings" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>
</div>