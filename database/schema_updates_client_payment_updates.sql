-- Client payment updates + admin review (run once on SmileStay)
-- Safe to run multiple times only if you handle duplicate column errors manually.

USE SmileStay;

-- Extend payment status for admin rejection
ALTER TABLE payments
    MODIFY COLUMN status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending';

-- Add optional client update fields (ignore error if column already exists)
ALTER TABLE payments ADD COLUMN payment_reference VARCHAR(100) NULL DEFAULT NULL AFTER reference_code;
ALTER TABLE payments ADD COLUMN payment_notes TEXT NULL AFTER payment_reference;

-- Card / method support (ignore if exists)
ALTER TABLE payments ADD COLUMN payment_method ENUM('cash','card','gcash') NOT NULL DEFAULT 'cash' AFTER amount;
ALTER TABLE payments ADD COLUMN transaction_id VARCHAR(80) NULL DEFAULT NULL AFTER reference_code;
ALTER TABLE payments ADD COLUMN card_brand VARCHAR(30) NULL DEFAULT NULL AFTER transaction_id;
ALTER TABLE payments ADD COLUMN card_last4 VARCHAR(4) NULL DEFAULT NULL AFTER card_brand;

-- Booking payment tracking (ignore if exists)
ALTER TABLE bookings ADD COLUMN payment_status ENUM('unpaid','pending','paid') NOT NULL DEFAULT 'unpaid' AFTER status;
ALTER TABLE bookings ADD COLUMN payment_proof VARCHAR(255) NULL DEFAULT NULL AFTER payment_status;
