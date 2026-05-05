-- ========================================
-- ETHIOGO COMPLETE DATABASE SCHEMA
-- With Driver Approval System
-- ========================================

-- Create database
CREATE DATABASE IF NOT EXISTS ethiogo_db;
USE ethiogo_db;

-- ========================================
-- 1️⃣ USERS TABLE (ALL ROLES)
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('passenger', 'driver', 'admin') DEFAULT 'passenger',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- ========================================
-- 2️⃣ DRIVER PROFILES (WITH APPROVAL STATUS)
-- ========================================
CREATE TABLE IF NOT EXISTS driver_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    license_number VARCHAR(50) NOT NULL,
    license_photo VARCHAR(255),
    experience_years INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_license (license_number)
);

-- ========================================
-- 3️⃣ COMPANIES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- ========================================
-- 4️⃣ BUSES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    driver_id INT,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    bus_type ENUM('standard', 'executive', 'luxury', 'minibus') DEFAULT 'standard',
    total_capacity INT NOT NULL,
    photo VARCHAR(255),
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_company (company_id),
    INDEX idx_driver (driver_id),
    INDEX idx_status (status)
);

-- ========================================
-- 5️⃣ CITIES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- ========================================
-- 6️⃣ ROUTES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_city_id INT NOT NULL,
    to_city_id INT NOT NULL,
    distance INT NOT NULL COMMENT 'Distance in kilometers',
    duration TIME NOT NULL COMMENT 'Estimated travel time',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_city_id) REFERENCES cities(id) ON DELETE CASCADE,
    FOREIGN KEY (to_city_id) REFERENCES cities(id) ON DELETE CASCADE,
    INDEX idx_cities (from_city_id, to_city_id)
);

-- ========================================
-- 7️⃣ TRIPS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    route_id INT NOT NULL,
    driver_id INT,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    date DATE NOT NULL,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    available_seats INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_route (route_id)
);

-- ========================================
-- 8️⃣ BOOKINGS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trip_id INT NOT NULL,
    number_of_passengers INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('booked', 'cancelled', 'completed') DEFAULT 'booked',
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_trip (trip_id),
    INDEX idx_status (status)
);

-- ========================================
-- 9️⃣ PAYMENTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card') DEFAULT 'cash',
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100) UNIQUE,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_status (status)
);

-- ========================================
-- 🔟 NOTIFICATIONS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking', 'payment', 'reminder', 'alert', 'info', 'driver_approval') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- ========================================
-- 1️⃣1️⃣ REVIEWS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bus_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    INDEX idx_bus (bus_id)
);

-- ========================================
-- 1️⃣2️⃣ BUS TRACKING
-- ========================================
CREATE TABLE IF NOT EXISTS bus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    speed DECIMAL(5, 2),
    heading DECIMAL(5, 2),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    INDEX idx_bus_time (bus_id, timestamp)
);

-- ========================================
-- INSERT SAMPLE DATA
-- ========================================

-- Insert Admin User
INSERT INTO users (name, email, phone, password, role, is_active) VALUES 
('System Administrator', 'admin@ethiogo.com', '0911000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert Sample Companies
INSERT INTO companies (name, phone, email, address) VALUES
('Selam Bus', '0111234567', 'info@selambus.com', 'Megenagna, Addis Ababa'),
('Sky Bus Transport', '0117654321', 'contact@skybus.com', 'Bole, Addis Ababa');

-- Insert Sample Cities
INSERT INTO cities (name, latitude, longitude) VALUES
('Addis Ababa', 9.0320, 38.7469),
('Debre Birhan', 9.6800, 39.5300),
('Bahir Dar', 11.5742, 37.3613),
('Gondar', 12.6030, 37.4610),
('Hawassa', 7.0500, 38.4667);

-- Insert Sample Routes
INSERT INTO routes (from_city_id, to_city_id, distance, duration) VALUES
(1, 2, 130, '03:00:00'),  -- Addis to Debre Birhan
(1, 3, 550, '08:00:00'),  -- Addis to Bahir Dar
(1, 4, 740, '10:30:00');  -- Addis to Gondar

-- Insert Sample Passengers
INSERT INTO users (name, email, phone, password, role, is_active) VALUES 
('Abebe Kebede', 'abebe@gmail.com', '0912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', 1),
('Tigist Haile', 'tigist@gmail.com', '0923456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', 1);

-- Insert Sample Drivers (with different approval statuses)
INSERT INTO users (name, email, phone, password, role, is_active) VALUES 
('Driver Pending', 'pending@gmail.com', '0934567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 1),
('Driver Approved', 'approved@gmail.com', '0945678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 1),
('Driver Rejected', 'rejected@gmail.com', '0956789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 1);

-- Insert Driver Profiles
INSERT INTO driver_profiles (user_id, license_number, experience_years, status) VALUES
(4, 'DRV-PENDING-001', 2, 'pending'),
(5, 'DRV-APPROVED-001', 5, 'approved'),
(6, 'DRV-REJECTED-001', 1, 'rejected');

-- Insert Sample Buses
INSERT INTO buses (company_id, plate_number, bus_type, total_capacity, status) VALUES
(1, 'AA-1234', 'executive', 40, 'active'),
(1, 'AA-5678', 'standard', 50, 'active'),
(2, 'AA-9012', 'luxury', 35, 'active');

-- Insert Sample Trips
INSERT INTO trips (bus_id, route_id, departure_time, arrival_time, price, date, available_seats) VALUES
(1, 1, '08:00:00', '11:00:00', 150, CURDATE(), 40),
(1, 1, '14:00:00', '17:00:00', 150, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 40),
(2, 2, '07:00:00', '15:00:00', 450, CURDATE(), 50),
(3, 3, '06:30:00', '17:00:00', 550, CURDATE(), 35);

-- ========================================
-- STORED PROCEDURE FOR LOGIN (WITH ROLE DETECTION)
-- ========================================
DELIMITER //

CREATE PROCEDURE authenticate_user(
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255)
)
BEGIN
    SELECT 
        u.id,
        u.name,
        u.email,
        u.role,
        u.is_active,
        d.status as driver_status,
        CASE 
            WHEN u.role = 'admin' THEN 'admin_dashboard'
            WHEN u.role = 'driver' AND d.status = 'approved' THEN 'driver_dashboard'
            WHEN u.role = 'driver' AND d.status = 'pending' THEN 'pending_approval'
            WHEN u.role = 'driver' AND d.status = 'rejected' THEN 'rejected'
            WHEN u.role = 'passenger' THEN 'user_dashboard'
            ELSE 'login'
        END as redirect_page
    FROM users u
    LEFT JOIN driver_profiles d ON u.id = d.user_id
    WHERE u.email = p_email;
END//

-- ========================================
-- STORED PROCEDURE FOR BOOKING
-- ========================================
CREATE PROCEDURE book_trip(
    IN p_user_id INT,
    IN p_trip_id INT,
    IN p_passengers INT,
    IN p_payment_method VARCHAR(20)
)
BEGIN
    DECLARE v_available_seats INT;
    DECLARE v_price DECIMAL(10,2);
    DECLARE v_total_price DECIMAL(10,2);
    DECLARE v_booking_id INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'error' as status, 'Booking failed. Please try again.' as message;
    END;
    
    START TRANSACTION;
    
    SELECT available_seats, price INTO v_available_seats, v_price 
    FROM trips WHERE id = p_trip_id FOR UPDATE;
    
    IF v_available_seats >= p_passengers THEN
        
        UPDATE trips SET available_seats = available_seats - p_passengers WHERE id = p_trip_id;
        
        SET v_total_price = v_price * p_passengers;
        
        INSERT INTO bookings (user_id, trip_id, number_of_passengers, total_price) 
        VALUES (p_user_id, p_trip_id, p_passengers, v_total_price);
        
        SET v_booking_id = LAST_INSERT_ID();
        
        INSERT INTO payments (booking_id, amount, payment_method, status) 
        VALUES (v_booking_id, v_total_price, p_payment_method, 'paid');
        
        INSERT INTO notifications (user_id, title, message, type) 
        VALUES (p_user_id, 'Booking Confirmed', 
                CONCAT('Your booking for ', p_passengers, ' passenger(s) has been confirmed! Booking ID: ', v_booking_id), 
                'booking');
        
        COMMIT;
        
        SELECT 'success' as status, 'Booking successful!' as message, v_booking_id as booking_id;
        
    ELSE
        ROLLBACK;
        SELECT 'error' as status, 'Not enough seats available!' as message;
    END IF;
END//

DELIMITER ;

-- Show all tables
SHOW TABLES;