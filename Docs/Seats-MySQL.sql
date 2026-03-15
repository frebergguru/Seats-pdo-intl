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
  `language` VARCHAR(5) NOT NULL DEFAULT 'en',
  `privacy_consent` TIMESTAMP NULL DEFAULT NULL,
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

-- Table structure for table "rate_limits"
CREATE TABLE `rate_limits` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`ip_address`, `action`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table "settings"
CREATE TABLE `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default email templates
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('email_tpl_reset_en', '<p>Hello <strong>{{nickname}}</strong>,</p>\n<p>We received a request to reset your password. Click the button below to choose a new one:</p>\n<p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;\">Reset Password</a></p>\n<p>If the button does not work, copy and paste this link into your browser:<br><a href=\"{{reset_link}}\" style=\"color:#4a90d9;word-break:break-all;\">{{reset_link}}</a></p>\n<p style=\"color:#999;font-size:13px;\">If you did not request a password reset, you can safely ignore this email.</p>'),
('email_tpl_reset_no', '<p>Hei <strong>{{nickname}}</strong>,</p>\n<p>Vi har mottatt en forespørsel om å tilbakestille passordet ditt. Klikk på knappen nedenfor for å velge et nytt:</p>\n<p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;\">Tilbakestill passord</a></p>\n<p>Hvis knappen ikke fungerer, kopier og lim inn denne lenken i nettleseren din:<br><a href=\"{{reset_link}}\" style=\"color:#4a90d9;word-break:break-all;\">{{reset_link}}</a></p>\n<p style=\"color:#999;font-size:13px;\">Hvis du ikke har bedt om å tilbakestille passordet, kan du trygt ignorere denne e-posten.</p>'),
('email_tpl_test_en', '<p>Hello,</p>\n<p>This is a test email sent from <strong>{{site_name}}</strong>.</p>\n<p>If you are reading this, your SMTP settings are configured correctly and emails are being delivered.</p>\n<p style=\"color:#999;font-size:13px;\">No action is required. This email was sent by an administrator to verify the email configuration.</p>'),
('email_tpl_test_no', '<p>Hei,</p>\n<p>Dette er en test-e-post sendt fra <strong>{{site_name}}</strong>.</p>\n<p>Hvis du leser dette, er SMTP-innstillingene konfigurert riktig og e-poster blir levert.</p>\n<p style=\"color:#999;font-size:13px;\">Ingen handling er nødvendig. Denne e-posten ble sendt av en administrator for å bekrefte e-postkonfigurasjonen.</p>');
