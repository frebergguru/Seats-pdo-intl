-- Migration script for adding admin functionality
-- Run this on existing installations to add the role column and seatmap table.

-- =====================
-- MySQL
-- =====================

ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'user';

CREATE TABLE IF NOT EXISTS `seatmap` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `map_data` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed the default map (skip if you already have map data):
INSERT INTO `seatmap` (`map_data`) VALUES ('wwwwwwwwwwwwwwwww\nweww#########wkkw\nwffw#########wkkw\nwffd#########dkkw\nwdww#########wkkw\nwfbw#########wkkw\nwwwwwwwwdwwwwwwww\nw###############w\nw###############w\nw###############w\nw###############w\nw###############w\nwwwwwwwwwwwwwwwww');

-- Promote a user to admin (replace 'your_nickname' with the actual nickname):
-- UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');

-- =====================
-- PostgreSQL
-- =====================

-- ALTER TABLE "users" ADD COLUMN "role" varchar(20) NOT NULL DEFAULT 'user';

-- CREATE TABLE IF NOT EXISTS "seatmap" (
--   "id" serial PRIMARY KEY,
--   "map_data" text NOT NULL,
--   "updated_at" timestamp DEFAULT CURRENT_TIMESTAMP
-- );

-- CREATE TABLE IF NOT EXISTS "settings" (
--   "setting_key" varchar(100) PRIMARY KEY,
--   "setting_value" text NOT NULL
-- );

-- Seed the default map (skip if you already have map data):
-- INSERT INTO "seatmap" ("map_data") VALUES (E'wwwwwwwwwwwwwwwww\nweww#########wkkw\nwffw#########wkkw\nwffd#########dkkw\nwdww#########wkkw\nwfbw#########wkkw\nwwwwwwwwdwwwwwwww\nw###############w\nw###############w\nw###############w\nw###############w\nw###############w\nwwwwwwwwwwwwwwwww');

-- Promote a user to admin (replace 'your_nickname' with the actual nickname):
-- UPDATE users SET role = 'admin' WHERE lower(nickname) = 'your_nickname';
