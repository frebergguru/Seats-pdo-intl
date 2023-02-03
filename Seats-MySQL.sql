
-- Create the database lanparty if it not exists

CREATE DATABASE IF NOT EXISTS lanparty;

/*!40101 SET NAMES utf8mb4 */;

-- DROP the table "reservations" if it exists
DROP TABLE IF EXISTS `reservations`;

-- Table structure for table "reservations"
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `taken` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- DROP the table "users" if it exists
DROP TABLE IF EXISTS `users`;

-- Table structure for table "users" 
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `password` varchar(97) NOT NULL,
  `email` varchar(255) NOT NULL,
  `forgottoken` varchar(64) DEFAULT NULL,
  `rseat` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);