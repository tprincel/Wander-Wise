-- =======================================================
-- Wander Wise Travel Web Application Database Schema
-- Database Name: wanderwise_db
-- Compatibility: MySQL 5.7+ / MySQL 8.0+ / MariaDB (XAMPP)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `wanderwise_db`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `wanderwise_db`;

-- -------------------------------------------------------
-- Table 1: Users (for user authentication / login)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 2: Packages (available tours displayed in Dashboard)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `destination` VARCHAR(200) NOT NULL,
    `duration` INT NOT NULL COMMENT 'Duration in days',
    `price` DECIMAL(10,2) NOT NULL COMMENT 'Price per person in INR',
    `category` VARCHAR(50) NOT NULL COMMENT 'cultural, adventure, wildlife, beach, religious',
    `state` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 3: Bookings (records of confirmed tour bookings)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_reference` VARCHAR(50) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `travel_date` DATE NOT NULL,
    `people_count` VARCHAR(10) NOT NULL,
    `package_name` VARCHAR(200) NOT NULL,
    `destination` VARCHAR(200) NOT NULL,
    `duration` VARCHAR(50) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `package_type` VARCHAR(50) NOT NULL COMMENT 'fixed or custom',
    `booking_status` VARCHAR(50) DEFAULT 'Confirmed',
    `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 4: Inquiries (search leads submitted via form.html)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `destination_state` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Seed Data: Packages for Wander Wise
-- -------------------------------------------------------
INSERT INTO `packages` (`name`, `destination`, `duration`, `price`, `category`, `state`, `description`) VALUES
('Golden Triangle Explorer', 'Delhi, Agra, Jaipur', 7, 25000.00, 'cultural', 'Rajasthan', 'Discover northern India heritage: Red Fort in Delhi, Taj Mahal in Agra, and Amber Fort & Hawa Mahal in Jaipur.'),
('Royal Rajasthan Heritage Tour', 'Jaipur, Jodhpur, Udaipur', 8, 32000.00, 'cultural', 'Rajasthan', 'Immerse yourself in royal palaces, desert bastions, and the serene lakes of Udaipur.'),
('Kerala Backwaters & Tea Hills', 'Munnar, Alleppey, Kochi', 5, 18500.00, 'wildlife', 'Kerala', 'Cruise the palm-fringed backwaters on a traditional houseboat and trek the tea garden hills of Munnar.'),
('Wayanad & Periyar Nature Expedition', 'Wayanad, Thekkady', 4, 14000.00, 'adventure', 'Kerala', 'Bamboo rafting, spice plantation trails, and wildlife safaris in Periyar National Park.'),
('Himachal Snow Valley Tour', 'Shimla, Kullu, Manali', 6, 22000.00, 'adventure', 'Himachal Pradesh', 'Experience majestic snow-covered peaks, Solang Valley sports, and high-altitude Himalayan beauty.'),
('Spiti Valley High Altitude Safari', 'Kaza, Tabo, Chandratal', 9, 36000.00, 'adventure', 'Himachal Pradesh', 'Rugged Himalayan terrain, ancient cliffside monasteries, and pristine turquoise mountain lakes.'),
('Goa Coastal Sunshine & Heritage', 'Calangute, Anjuna, Panaji, Old Goa', 4, 15000.00, 'beach', 'Goa', 'Golden sand beaches, historic Portuguese churches, vibrant night bazaars, and water adventures.'),
('Goa Backwater & Island Kayaking', 'Divar Island, Palolem', 3, 11500.00, 'beach', 'Goa', 'Unexplored tranquil backwaters, dolphin spotting cruises, and quiet sunset shores.'),
('Spiritual Ganga & Sacred Ghats', 'Varanasi, Sarnath, Ayodhya', 5, 16000.00, 'religious', 'Uttar Pradesh', 'Witness the evening Ganga Aarti, explore ancient Sarnath stupas, and visit the temple city of Ayodhya.'),
('Tirupati & Tamil Temple Trail', 'Madurai, Rameswaram, Kanchipuram', 6, 21000.00, 'religious', 'Tamil Nadu', 'Magnificent Dravidian architecture, sacred shore temples, and timeless cultural traditions.'),
('Bandhavgarh & Kanha Tiger Safari', 'Kanha, Bandhavgarh', 5, 27000.00, 'wildlife', 'Madhya Pradesh', 'Premier tiger reserves of central India featuring dense sal forests and guided jeep safaris.'),
('Maharashtra Forts & Caves Journey', 'Ajanta, Ellora, Aurangabad, Raigad', 5, 17500.00, 'cultural', 'Maharashtra', 'UNESCO World Heritage rock-cut caves of Ajanta & Ellora alongside historic Maratha hill forts.'),
('Uttarakhand Alpine Trek', 'Rishikesh, Haridwar, Mussoorie', 5, 16500.00, 'adventure', 'Uttarakhand', 'White water rafting on the Ganges, yoga retreats, and cool mountain retreats in the Garhwal hills.'),
('Darjeeling & Dooars Explorer', 'Darjeeling, Kalimpong, Dooars', 6, 23000.00, 'cultural', 'West Bengal', 'Toy train rides, sweeping views of Mount Kanchenjunga, and lush Dooars tea plantations.');
