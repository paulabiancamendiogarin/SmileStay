-- Migrate from hotel_locatorr_db to SmileStay (MySQL / MariaDB on XAMPP)
-- Your server already has a `smilestay` database (same as SmileStay on Windows).

CREATE DATABASE IF NOT EXISTS `SmileStay` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- If hotel_locatorr_db still has tables, move them (run once per table):
-- RENAME TABLE `hotel_locatorr_db`.`users` TO `SmileStay`.`users`;
-- (repeat for bookings, hotels, rooms, payments, otp_codes, notifications, etc.)

-- After confirming data is in SmileStay, you may drop the old empty database:
-- DROP DATABASE IF EXISTS `hotel_locatorr_db`;
