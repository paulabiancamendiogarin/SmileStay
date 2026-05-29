-- =====================================================
-- SmileStay Database
-- For Bacolod City, Philippines
-- =====================================================
CREATE DATABASE IF NOT EXISTS SmileStay;
USE SmileStay;
-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
google_auth_secret VARCHAR(64) DEFAULT NULL,
role ENUM('admin', 'customer') DEFAULT 'customer',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- =====================================================
-- HOTELS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS hotels (
id INT AUTO_INCREMENT PRIMARY KEY,
hotel_name VARCHAR(150) NOT NULL,
location VARCHAR(255) NOT NULL,
latitude DECIMAL(10, 8) NOT NULL,
longitude DECIMAL(11, 8) NOT NULL,
description TEXT,
price_per_night DECIMAL(10, 2) NOT NULL,
image VARCHAR(255) DEFAULT 'default_hotel.jpg',
amenities TEXT,
rating DECIMAL(2, 1) DEFAULT 0,
status ENUM('active', 'inactive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- ROOMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS rooms (
id INT AUTO_INCREMENT PRIMARY KEY,
hotel_id INT NOT NULL,
room_type VARCHAR(100) NOT NULL,
capacity INT NOT NULL,
price DECIMAL(10, 2) NOT NULL,
description TEXT,
available INT DEFAULT 1,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
CURRENT_TIMESTAMP,
FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- =====================================================
-- BOOKINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS bookings (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
hotel_id INT NOT NULL,
room_id INT NOT NULL,
check_in DATE NOT NULL,
check_out DATE NOT NULL,
total_price DECIMAL(10, 2) NOT NULL,
guests INT DEFAULT 1,
special_requests TEXT,
status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
booking_reference VARCHAR(20) UNIQUE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- =====================================================
-- INSERT DEFAULT ADMIN USER
-- Password: admin123 (hashed)
-- =====================================================

INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@hotellocator.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Customer', 'john@example.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');
-- =====================================================
-- INSERT SAMPLE HOTELS IN BACOLOD CITY
-- =====================================================
INSERT INTO hotels (hotel_name, location, latitude, longitude, description, price_per_night,
image, amenities, rating) VALUES
("L'Fisher Hotel",
'14th Lacson Street, Bacolod City, Negros Occidental 6100',
10.6765, 122.9509,
"L'Fisher Hotel is a premier business and leisure hotel in Bacolod City. Known for its elegant
accommodations, world-class amenities, and exceptional service. Features include an outdoor
pool, fitness center, spa, and multiple dining options. Perfect for both business travelers and
tourists exploring the City of Smiles.",
4500.00, 'lfisher_hotel.jpg',
'Free WiFi,Swimming Pool,Spa,Fitness Center,Restaurant,Bar,Room Service,Airport
Shuttle,Business Center,Meeting Rooms',
4.5),
('Seda Capitol Central',
'Gatuslao Street, Bacolod City, Negros Occidental 6100',
10.6797, 122.9547,
'Seda Capitol Central is a modern lifestyle hotel located in the heart of Bacolod City. Part of the
renowned Seda Hotels chain, it offers contemporary design, comfortable rooms, and excellent
facilities. Situated near the Provincial Capitol Lagoon, it provides easy access to shopping,
dining, and entertainment.',
3800.00, 'seda_capitol.jpg',
'Free WiFi,Swimming Pool,Fitness Center,Restaurant,Bar,Room Service,Business
Center,Parking,Concierge',
4.6),
('Circle Inn Hotel & Suites Bacolod',
'Rizal Street corner Luzuriaga Street, Bacolod City, Negros Occidental 6100',
10.6743, 122.9511,
'Circle Inn Hotel & Suites offers comfortable and affordable accommodations in downtown
Bacolod City. Ideal for budget-conscious travelers who do not want to compromise on quality.
Features clean rooms, friendly service, and convenient location near major attractions and
transportation hubs.',
1800.00, 'circle_inn.jpg',

'Free WiFi,Air Conditioning,Restaurant,Parking,24-hour Front Desk,Laundry Service',
4.0),
('Go Hotels Bacolod',
'Robinsons Place Bacolod, Lacson Street, Bacolod City, Negros Occidental 6100',
10.6712, 122.9485,
'Go Hotels Bacolod is a value-for-money hotel located within Robinsons Place Bacolod. Perfect
for travelers who want affordable yet comfortable accommodations with easy access to
shopping and entertainment. Modern rooms with essential amenities for a pleasant stay.',
1500.00, 'go_hotels.jpg',
'Free WiFi,Air Conditioning,24-hour Front Desk,Parking,Mall Access',
3.8),
('Planta Centro Bacolod',
'San Juan Street, Bacolod City, Negros Occidental 6100',
10.6789, 122.9534,
'Planta Centro Bacolod is a charming boutique hotel offering personalized service and
comfortable accommodations. Located in the city center, it features well-appointed rooms with
modern amenities. Known for its warm hospitality and excellent value.',
2200.00, 'planta_centro.jpg',
'Free WiFi,Air Conditioning,Restaurant,Room Service,Parking,Laundry Service,Business
Center',
4.2),
('East View Hotel',
'BS Aquino Drive, Bacolod City, Negros Occidental 6100',
10.6856, 122.9612,
'East View Hotel offers modern accommodations with stunning views of Bacolod City. Features
spacious rooms, excellent service, and convenient location. Popular among business travelers
and tourists alike for its quality facilities and competitive rates.',
2500.00, 'east_view.jpg',
'Free WiFi,Swimming Pool,Restaurant,Bar,Fitness Center,Meeting Rooms,Parking',
4.1),
('MO2 Westown Hotel',
'Lacson Street, Bacolod City, Negros Occidental 6100',
10.6678, 122.9456,
'MO2 Westown Hotel is a modern entertainment and hospitality complex in Bacolod City.
Features stylish rooms, multiple dining options, entertainment venues, and excellent facilities.
Perfect for those seeking a vibrant nightlife experience combined with comfortable
accommodations.',
3200.00, 'mo2_westown.jpg',
'Free WiFi,Swimming Pool,Restaurant,Bar,KTV,Fitness Center,Spa,Parking,Entertainment',
4.3),

('Business Inn Bacolod',
'Lacson-Burgos Streets, Bacolod City, Negros Occidental 6100',
10.6734, 122.9498,
'Business Inn Bacolod caters primarily to business travelers with practical amenities and central
location. Offers comfortable rooms, meeting facilities, and efficient service. Affordable rates
make it an excellent choice for corporate visits and short stays.',
1600.00, 'business_inn.jpg',
'Free WiFi,Air Conditioning,Meeting Rooms,Restaurant,Parking,24-hour Front Desk',
3.9),
('11th Street Bed and Breakfast',
'11th Street, Bacolod City, Negros Occidental 6100',
10.6751, 122.9523,
'11th Street Bed and Breakfast offers a cozy, home-away-from-home experience in Bacolod
City. Features comfortable rooms, hearty breakfast, and personalized service. Ideal for travelers
seeking a more intimate accommodation experience at affordable rates.',
1200.00, '11th_street_bnb.jpg',
'Free WiFi,Air Conditioning,Breakfast Included,Parking,Garden,Common Area',
4.4),
('The Suites at Calle Nueva',
'Calle Nueva, Bacolod City, Negros Occidental 6100',
10.6801, 122.9567,
'The Suites at Calle Nueva is a sophisticated serviced apartment hotel offering spacious suites
with full kitchen facilities. Perfect for extended stays, families, and guests who prefer a more
independent living arrangement with hotel-style services.',
3500.00, 'suites_calle_nueva.jpg',
'Free WiFi,Kitchen,Air Conditioning,Parking,Laundry,Housekeeping,24-hour Security',
4.3);
-- =====================================================
-- INSERT SAMPLE ROOMS FOR EACH HOTEL
-- =====================================================
-- L'Fisher Hotel Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(1, 'Deluxe Room', 2, 4500.00, 'Spacious room with king-size bed, city view, work desk, and
premium amenities.', 5),
(1, 'Superior Room', 2, 5500.00, 'Elegant room with enhanced amenities, separate seating area,
and pool view.', 3),
(1, 'Executive Suite', 4, 8000.00, 'Luxurious suite with separate living room, dining area, and
panoramic city views.', 2),
(1, 'Family Room', 4, 6500.00, 'Comfortable room with two queen beds, perfect for families.', 4);

-- Seda Capitol Central Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(2, 'Deluxe Room', 2, 3800.00, 'Contemporary room with plush bedding and modern amenities.',
6),
(2, 'Premier Room', 2, 4500.00, 'Upgraded room with additional space and premium features.',
4),
(2, 'Club Room', 2, 5500.00, 'Club access included with exclusive lounge benefits.', 3),
(2, 'Suite', 4, 7500.00, 'Spacious suite with separate living area and enhanced amenities.', 2);
-- Circle Inn Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(3, 'Standard Room', 2, 1800.00, 'Comfortable room with essential amenities for a pleasant
stay.', 8),
(3, 'Superior Room', 2, 2200.00, 'Upgraded room with additional space and better views.', 5),
(3, 'Family Suite', 4, 3000.00, 'Spacious suite ideal for families or groups.', 3);
-- Go Hotels Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(4, 'Go Room', 2, 1500.00, 'Smart and efficient room with modern essentials.', 10),
(4, 'Go Plus Room', 2, 1800.00, 'Slightly larger room with enhanced comfort.', 6);
-- Planta Centro Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(5, 'Standard Room', 2, 2200.00, 'Well-appointed room with comfortable furnishings.', 6),
(5, 'Deluxe Room', 2, 2800.00, 'Spacious room with premium bedding and city view.', 4),
(5, 'Suite', 4, 4000.00, 'Large suite with separate living space.', 2);
-- East View Hotel Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(6, 'Standard Room', 2, 2500.00, 'Comfortable room with modern amenities.', 7),
(6, 'Deluxe Room', 2, 3200.00, 'Enhanced room with better views and space.', 5),
(6, 'Executive Room', 2, 4000.00, 'Premium room with executive amenities.', 3);
-- MO2 Westown Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(7, 'Superior Room', 2, 3200.00, 'Stylish room with modern entertainment features.', 8),
(7, 'Deluxe Room', 2, 4000.00, 'Upgraded room with enhanced amenities.', 5),
(7, 'Suite', 4, 5500.00, 'Luxurious suite with separate entertainment area.', 3);
-- Business Inn Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(8, 'Standard Room', 2, 1600.00, 'Practical room for business travelers.', 8),
(8, 'Business Room', 2, 2000.00, 'Room with work desk and business amenities.', 5);

-- 11th Street B&B Rooms
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(9, 'Cozy Room', 2, 1200.00, 'Charming room with breakfast included.', 4),
(9, 'Garden Room', 2, 1500.00, 'Room with garden view and enhanced amenities.', 3);
-- Suites at Calle Nueva
INSERT INTO rooms (hotel_id, room_type, capacity, price, description, available) VALUES
(10, 'Studio Suite', 2, 3500.00, 'Compact suite with kitchenette.', 5),
(10, 'One Bedroom Suite', 2, 4500.00, 'Full suite with separate bedroom and kitchen.', 4),
(10, 'Two Bedroom Suite', 4, 6000.00, 'Spacious suite ideal for families or groups.', 2); 

CREATE TABLE IF NOT EXISTS payments (
id INT AUTO_INCREMENT PRIMARY KEY,
booking_id INT NOT NULL UNIQUE,
amount DECIMAL(10, 2) NOT NULL,
reference_code VARCHAR(64) NOT NULL,
qr_image_path VARCHAR(255) DEFAULT NULL,
proof_image VARCHAR(255) DEFAULT NULL,
status ENUM('pending', 'verified') DEFAULT 'pending',
expires_at DATETIME NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
INDEX idx_payments_status (status),
INDEX idx_payments_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS otp_codes (
id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(100) NOT NULL,
otp_code VARCHAR(6) NOT NULL,
purpose ENUM('login', 'register') NOT NULL,
expires_at DATETIME NOT NULL,
failed_attempts INT NOT NULL DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
INDEX idx_otp_email_purpose (email, purpose),
INDEX idx_otp_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
