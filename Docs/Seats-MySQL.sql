-- Create the database if it does not exist
CREATE DATABASE IF NOT EXISTS lanparty
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE lanparty;

/*!40101 SET NAMES utf8mb4 */;

-- Drop the table "reservations" if it exists
DROP TABLE IF EXISTS `reservations`;

-- Drop the table "seatmap" if it exists
DROP TABLE IF EXISTS `seatmap`;

-- Drop the table "settings" if it exists
DROP TABLE IF EXISTS `settings`;

-- Drop the table "users" if it exists
DROP TABLE IF EXISTS `users`;

-- Table structure for table "users"
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `nickname` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `forgottoken` VARCHAR(64) DEFAULT NULL,
  `rseat` INT DEFAULT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table "reservations"
CREATE TABLE `reservations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `taken` TINYINT(1) NOT NULL DEFAULT 0,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table "seatmap"
CREATE TABLE `seatmap` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `map_data` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default seat map
INSERT INTO `seatmap` (`map_data`) VALUES ('wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww');

-- Table structure for table "settings"
CREATE TABLE `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
