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
INSERT INTO `seatmap` (`map_data`) VALUES ('wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww');

ALTER TABLE `users` ADD COLUMN `language` VARCHAR(5) NOT NULL DEFAULT 'en';
ALTER TABLE `users` ADD COLUMN `privacy_consent` TIMESTAMP NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`ip_address`, `action`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed email templates (skip if you already have them):
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('email_tpl_reset_en', '<p>Hello <strong>{{nickname}}</strong>,</p><p>We received a request to reset your password.</p><p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;\">Reset Password</a></p><p>Or copy this link: {{reset_link}}</p>'),
('email_tpl_reset_no', '<p>Hei <strong>{{nickname}}</strong>,</p><p>Vi har mottatt en forespørsel om å tilbakestille passordet ditt.</p><p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;\">Tilbakestill passord</a></p><p>Eller kopier denne lenken: {{reset_link}}</p>'),
('email_tpl_test_en', '<p>Hello,</p><p>This is a test email from <strong>{{site_name}}</strong>. Your SMTP settings are working correctly.</p>'),
('email_tpl_test_no', '<p>Hei,</p><p>Dette er en test-e-post fra <strong>{{site_name}}</strong>. SMTP-innstillingene fungerer.</p>');

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
-- INSERT INTO "seatmap" ("map_data") VALUES (E'wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww');

-- ALTER TABLE "users" ADD COLUMN "language" varchar(5) NOT NULL DEFAULT 'en';
-- ALTER TABLE "users" ADD COLUMN "privacy_consent" timestamp DEFAULT NULL;

-- CREATE TABLE IF NOT EXISTS "rate_limits" (
--   "id" serial PRIMARY KEY,
--   "ip_address" varchar(45) NOT NULL,
--   "action" varchar(20) NOT NULL,
--   "attempted_at" timestamp DEFAULT CURRENT_TIMESTAMP
-- );
-- CREATE INDEX idx_rate_lookup ON "rate_limits" ("ip_address", "action", "attempted_at");

-- Promote a user to admin (replace 'your_nickname' with the actual nickname):
-- UPDATE users SET role = 'admin' WHERE lower(nickname) = 'your_nickname';
