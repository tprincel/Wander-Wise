-- =======================================================
-- Wander Wise Travel Web Application Database Schema
-- Database Name: wanderwise_db
-- Compatibility: MySQL 5.7+ / MySQL 8.0+ / MariaDB (XAMPP)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `wanderwise_db`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `wanderwise_db`;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `places`;
DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table (Customer & Admin Accounts)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `role` ENUM('customer', 'admin') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Places / Tour Destinations
CREATE TABLE `places` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `state_region` VARCHAR(100) NOT NULL,
    `category` ENUM('Adventure', 'Heritage', 'Beach', 'Hill Station', 'Spiritual') NOT NULL,
    `price_per_person` DECIMAL(10, 2) NOT NULL,
    `duration_days` INT NOT NULL,
    `image_url` VARCHAR(255),
    `description` TEXT,
    `is_featured` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bookings (Who booked which place, passenger count, travel dates)
CREATE TABLE `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `place_id` INT NOT NULL,
    `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `travel_date` DATE NOT NULL,
    `number_of_guests` INT NOT NULL DEFAULT 1,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `booking_status` ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending',
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`place_id`) REFERENCES `places`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payments Table
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `transaction_id` VARCHAR(100) UNIQUE,
    `payment_method` ENUM('Card', 'UPI', 'NetBanking', 'Cash') DEFAULT 'UPI',
    `amount_paid` DECIMAL(10, 2) NOT NULL,
    `payment_status` ENUM('Pending', 'Success', 'Failed', 'Refunded') DEFAULT 'Pending',
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. User Inquiries / Contact Form
CREATE TABLE `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200),
    `message` TEXT NOT NULL,
    `status` ENUM('Unread', 'Responded') DEFAULT 'Unread',
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Seed Data (Realistic destinations & sample records)
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`) VALUES
('Admin Princel', 'princetixeira@gmail.com', '$2y$10$e8Z4w3s0FvVv3j9U4P1o7eR5o7v2L5oWq8eR5o7v2L5oWq8eR5o7v', '+91 9876543210', 'admin'),
('Rashi Shukla', 'rashi@example.com', '$2y$10$e8Z4w3s0FvVv3j9U4P1o7eR5o7v2L5oWq8eR5o7v2L5oWq8eR5o7v', '+91 9812345678', 'customer');

INSERT INTO `places` (`title`, `state_region`, `category`, `price_per_person`, `duration_days`, `image_url`, `description`, `is_featured`) VALUES
('Manali Snow Peaks & Solang Valley', 'Himachal Pradesh', 'Hill Station', 12500.00, 5, 'images/WhatsApp Image 2025-09-18 at 22.18.17.jpeg', 'Scenic mountain views, paragliding, and historic temples.', 1),
('Goa Coastal Cruise & Heritage Churches', 'Goa', 'Beach', 9500.00, 4, 'images/WhatsApp Image 2025-09-18 at 22.18.23 (1).jpeg', 'Golden beaches, water sports, night markets, and Portuguese architecture.', 1),
('Jaipur Royal Forts & Palaces', 'Rajasthan', 'Heritage', 11000.00, 4, 'images/WhatsApp Image 2025-09-18 at 22.18.14.jpeg', 'Amber Fort, Hawa Mahal, authentic Rajasthani dining, and bazaars.', 1),
('Rishikesh River Rafting & Camping', 'Uttarakhand', 'Adventure', 8000.00, 3, 'images/WhatsApp Image 2025-09-18 at 22.18.26.jpeg', 'White-water rafting on the Ganges, cliff jumping, and riverside camps.', 0),
('Varanasi Ghats & Ganga Aarti', 'Uttar Pradesh', 'Spiritual', 7500.00, 3, 'images/WhatsApp Image 2025-09-18 at 22.21.47 (1).jpeg', 'Spiritual boat tours, ancient ghats, and evening Aarti ceremonies.', 0),
('Kerala Backwaters & Tea Hills', 'Kerala', 'Hill Station', 18500.00, 5, 'images/WhatsApp Image 2025-09-18 at 22.21.42 (1).jpeg', 'Cruise palm-fringed backwaters and trek lush Munnar tea gardens.', 1),
('Spiti Valley High Altitude Safari', 'Himachal Pradesh', 'Adventure', 22000.00, 7, 'images/WhatsApp Image 2025-09-18 at 22.21.43 (1).jpeg', 'Rugged Himalayan terrain, cliffside monasteries, and turquoise lakes.', 0),
('Tirupati & Tamil Temple Trail', 'Tamil Nadu', 'Spiritual', 16000.00, 6, 'images/WhatsApp Image 2025-09-18 at 22.21.42 (2).jpeg', 'Magnificent Dravidian architecture and sacred coastal temple grounds.', 0),
('Bandhavgarh & Kanha Tiger Safari', 'Madhya Pradesh', 'Adventure', 27000.00, 5, 'images/WhatsApp Image 2025-09-18 at 22.21.44 (1).jpeg', 'Premier tiger reserves featuring dense sal forests and guided jeep safaris.', 0),
('Ajanta & Ellora Rock Heritage', 'Maharashtra', 'Heritage', 17500.00, 5, 'images/WhatsApp Image 2025-09-18 at 22.21.46.jpeg', 'Ancient UNESCO World Heritage rock-cut cave temples and sculptures.', 0);

INSERT INTO `bookings` (`user_id`, `place_id`, `travel_date`, `number_of_guests`, `total_amount`, `booking_status`) VALUES
(2, 1, '2026-11-15', 2, 25000.00, 'Confirmed');

INSERT INTO `payments` (`booking_id`, `transaction_id`, `payment_method`, `amount_paid`, `payment_status`) VALUES
(1, 'TXN_WW_20261023_001', 'UPI', 25000.00, 'Success');
