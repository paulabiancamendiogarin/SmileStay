-- Analytics/reporting support + payment methods + API tokens + notifications
-- Run after hotel_system.sql + schema_updates_gcash_otp.sql + schema_updates_user_approval.sql

USE SmileStay;

-- Users: simple API token fields (optional)
ALTER TABLE users
    ADD COLUMN api_token VARCHAR(64) NULL DEFAULT NULL,
    ADD COLUMN token_expiration DATETIME NULL DEFAULT NULL;

CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_users_is_approved ON users(is_approved);

-- Payments: support multiple methods and transactions
ALTER TABLE payments
    ADD COLUMN payment_method ENUM('gcash','cash','card') NOT NULL DEFAULT 'gcash' AFTER amount,
    ADD COLUMN transaction_id VARCHAR(80) NULL DEFAULT NULL AFTER reference_code,
    ADD COLUMN card_brand VARCHAR(30) NULL DEFAULT NULL AFTER transaction_id,
    ADD COLUMN card_last4 VARCHAR(4) NULL DEFAULT NULL AFTER card_brand,
    -- Compatibility fields requested (mapped from existing status/proof_image in code when present)
    ADD COLUMN payment_status VARCHAR(20) NULL DEFAULT NULL AFTER status,
    ADD COLUMN payment_proof VARCHAR(255) NULL DEFAULT NULL AFTER proof_image;

-- Ensure timestamps exist (older schemas already include these)
ALTER TABLE payments
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE INDEX idx_payments_method ON payments(payment_method);
CREATE INDEX idx_payments_created_at ON payments(created_at);

-- Bookings: indexes for reporting
CREATE INDEX idx_bookings_created_at ON bookings(created_at);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_payment_status ON bookings(payment_status);
CREATE INDEX idx_bookings_hotel_id ON bookings(hotel_id);

-- Notifications: admin-side alerts for events
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(40) NOT NULL,
    title VARCHAR(140) NOT NULL,
    message TEXT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_is_read (is_read),
    INDEX idx_notifications_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

