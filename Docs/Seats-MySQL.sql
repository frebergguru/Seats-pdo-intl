-- Create the database if it does not exist
CREATE DATABASE IF NOT EXISTS lanparty
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE lanparty;

/*!40101 SET NAMES utf8mb4 */;

-- Drop the table "reservations" if it exists
DROP TABLE IF EXISTS `reservations`;

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