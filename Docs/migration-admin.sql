-- Migration script for existing installations.
-- This script is idempotent — safe to re-run.
--
-- Brings older installations up to the current schema:
--   v2.0: admin role + seatmap/settings tables
--   v2.1: language column + map storage
--   v2.2: GDPR (privacy_consent), rate_limits
--   v2.3: password column to TEXT, UNIQUE constraints on reservations

-- =====================
-- MySQL / MariaDB
-- =====================

DELIMITER //

DROP PROCEDURE IF EXISTS seats_add_col_if_missing//
CREATE PROCEDURE seats_add_col_if_missing(
    IN p_table VARCHAR(64),
    IN p_col   VARCHAR(64),
    IN p_def   TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = p_table
          AND column_name = p_col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_col, '` ', p_def);
        PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    END IF;
END//

DROP PROCEDURE IF EXISTS seats_add_unique_if_missing//
CREATE PROCEDURE seats_add_unique_if_missing(
    IN p_table   VARCHAR(64),
    IN p_index   VARCHAR(64),
    IN p_columns VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = p_table
          AND index_name = p_index
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD UNIQUE KEY `', p_index, '` (', p_columns, ')');
        PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    END IF;
END//

DROP PROCEDURE IF EXISTS seats_modify_col//
CREATE PROCEDURE seats_modify_col(
    IN p_table VARCHAR(64),
    IN p_col   VARCHAR(64),
    IN p_def   TEXT
)
BEGIN
    SET @sql = CONCAT('ALTER TABLE `', p_table, '` MODIFY COLUMN `', p_col, '` ', p_def);
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
END//

DELIMITER ;

-- Columns added across versions
CALL seats_add_col_if_missing('users', 'role',            'VARCHAR(20) NOT NULL DEFAULT ''user''');
CALL seats_add_col_if_missing('users', 'language',        'VARCHAR(5) NOT NULL DEFAULT ''en''');
CALL seats_add_col_if_missing('users', 'privacy_consent', 'TIMESTAMP NULL DEFAULT NULL');

-- Widen password column for forward compatibility with raised Argon2id costs.
CALL seats_modify_col('users', 'password', 'TEXT NOT NULL');

-- Tables added across versions
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

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_lookup` (`ip_address`, `action`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Enforce one-reservation-per-user and one-user-per-seat at the DB layer
-- to close the booking race condition. If your existing reservations table
-- already contains duplicates (same user_id or same `taken`), these ALTERs
-- will fail; clean up first with:
--     SELECT user_id, COUNT(*) FROM reservations GROUP BY user_id HAVING COUNT(*) > 1;
--     SELECT taken,   COUNT(*) FROM reservations GROUP BY taken   HAVING COUNT(*) > 1;
CALL seats_add_unique_if_missing('reservations', 'uq_reservations_user', '`user_id`');
CALL seats_add_unique_if_missing('reservations', 'uq_reservations_seat', '`taken`');

-- Seed the default map (skip if you already have map data):
INSERT IGNORE INTO `seatmap` (`id`, `map_data`) VALUES (1, 'wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww');

-- Seed email templates (skip if you already have them):
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('email_tpl_reset_en', '<p>Hello <strong>{{nickname}}</strong>,</p><p>We received a request to reset your password.</p><p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;\">Reset Password</a></p><p>Or copy this link: {{reset_link}}</p>'),
('email_tpl_reset_no', '<p>Hei <strong>{{nickname}}</strong>,</p><p>Vi har mottatt en forespørsel om å tilbakestille passordet ditt.</p><p style=\"text-align:center;margin:25px 0;\"><a href=\"{{reset_link}}\" style=\"display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;\">Tilbakestill passord</a></p><p>Eller kopier denne lenken: {{reset_link}}</p>'),
('email_tpl_test_en', '<p>Hello,</p><p>This is a test email from <strong>{{site_name}}</strong>. Your SMTP settings are working correctly.</p>'),
('email_tpl_test_no', '<p>Hei,</p><p>Dette er en test-e-post fra <strong>{{site_name}}</strong>. SMTP-innstillingene fungerer.</p>');

DROP PROCEDURE seats_add_col_if_missing;
DROP PROCEDURE seats_add_unique_if_missing;
DROP PROCEDURE seats_modify_col;

-- Promote a user to admin (replace 'your_nickname' with the actual nickname):
-- UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');


-- =====================
-- PostgreSQL
-- =====================
-- All of the following statements use IF NOT EXISTS so they are idempotent.

-- ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "role"            varchar(20) NOT NULL DEFAULT 'user';
-- ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "language"        varchar(5)  NOT NULL DEFAULT 'en';
-- ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "privacy_consent" timestamp   DEFAULT NULL;
-- ALTER TABLE "users" ALTER COLUMN "password" TYPE text;

-- CREATE TABLE IF NOT EXISTS "seatmap" (
--   "id" serial PRIMARY KEY,
--   "map_data" text NOT NULL,
--   "updated_at" timestamp DEFAULT CURRENT_TIMESTAMP
-- );

-- CREATE TABLE IF NOT EXISTS "settings" (
--   "setting_key" varchar(100) PRIMARY KEY,
--   "setting_value" text NOT NULL
-- );

-- CREATE TABLE IF NOT EXISTS "rate_limits" (
--   "id" serial PRIMARY KEY,
--   "ip_address" varchar(45) NOT NULL,
--   "action" varchar(20) NOT NULL,
--   "attempted_at" timestamp DEFAULT CURRENT_TIMESTAMP
-- );
-- CREATE INDEX IF NOT EXISTS idx_rate_lookup ON "rate_limits" ("ip_address", "action", "attempted_at");

-- DO $$ BEGIN
--   IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uq_reservations_user') THEN
--     ALTER TABLE "reservations" ADD CONSTRAINT uq_reservations_user UNIQUE ("user_id");
--   END IF;
--   IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uq_reservations_seat') THEN
--     ALTER TABLE "reservations" ADD CONSTRAINT uq_reservations_seat UNIQUE ("taken");
--   END IF;
--   IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_nickname_key') THEN
--     ALTER TABLE "users" ADD CONSTRAINT users_nickname_key UNIQUE ("nickname");
--   END IF;
--   IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_email_key') THEN
--     ALTER TABLE "users" ADD CONSTRAINT users_email_key UNIQUE ("email");
--   END IF;
-- END $$;

-- Seed the default map (skip if you already have map data):
-- INSERT INTO "seatmap" ("map_data") VALUES (E'wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww')
--   ON CONFLICT DO NOTHING;

-- Promote a user to admin (replace 'your_nickname' with the actual nickname):
-- UPDATE users SET role = 'admin' WHERE lower(nickname) = 'your_nickname';
