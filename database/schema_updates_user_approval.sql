-- User approval, one-time QR verification, and booking payment fields
-- Run after hotel_system.sql and schema_updates_gcash_otp.sql

USE SmileStay;

ALTER TABLE users
    ADD COLUMN qr_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER google_auth_secret,
    ADD COLUMN is_approved TINYINT(1) NOT NULL DEFAULT 0 AFTER qr_verified,
    ADD COLUMN approved_at DATETIME NULL DEFAULT NULL AFTER is_approved;

-- Existing admins: auto-approved (QR flag set after first TOTP setup)
UPDATE users SET is_approved = 1, approved_at = NOW() WHERE role = 'admin';

-- Approve existing demo customers so they are not locked out
UPDATE users SET is_approved = 1, qr_verified = 1, approved_at = NOW()
WHERE role = 'customer' AND created_at < NOW();

-- Optional denormalized payment fields on bookings (synced from payments table in app)
ALTER TABLE bookings
    ADD COLUMN payment_status ENUM('unpaid', 'pending', 'paid') NOT NULL DEFAULT 'unpaid' AFTER status,
    ADD COLUMN payment_proof VARCHAR(255) NULL DEFAULT NULL AFTER payment_status;

-- Backfill booking payment fields from payments table when present
UPDATE bookings b
INNER JOIN payments p ON p.booking_id = b.id
SET b.payment_status = CASE WHEN p.status = 'verified' THEN 'paid' ELSE 'pending' END,
    b.payment_proof = p.proof_image
WHERE b.payment_status = 'unpaid' OR b.payment_proof IS NULL;
