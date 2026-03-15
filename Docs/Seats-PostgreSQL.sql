-- Create the database "lanparty" if it not exists
DO $$
BEGIN
  IF NOT EXISTS (SELECT FROM pg_database WHERE datname = 'lanparty') THEN
    CREATE DATABASE lanparty
      ENCODING = 'UTF8'
      TEMPLATE = template0;
  END IF;
END $$;

--

-- DROP the table "reservations" if it exists
DROP TABLE IF EXISTS "reservations";

-- DROP the table "seatmap" if it exists
DROP TABLE IF EXISTS "seatmap";

-- DROP the table "settings" if it exists
DROP TABLE IF EXISTS "settings";

--

-- DROP the table "users" if it exists
DROP TABLE IF EXISTS "users";

--

-- Table structure for table "users"
CREATE TABLE "users" (
"id" serial PRIMARY KEY,
"fullname" varchar(255) NOT NULL COLLATE "C",
"nickname" varchar(255) NOT NULL COLLATE "C",
"password" varchar(255) NOT NULL COLLATE "C",
"email" varchar(255) NOT NULL COLLATE "C",
"forgottoken" varchar(64) COLLATE "C",
"rseat" integer,
"role" varchar(20) NOT NULL DEFAULT 'user',
"language" varchar(5) NOT NULL DEFAULT 'en',
"privacy_consent" timestamp DEFAULT NULL
);

--

-- Table structure for table "reservations"
CREATE TABLE "reservations" (
"id" serial PRIMARY KEY,
"taken" integer,
"user_id" integer NOT NULL
);

--

-- Table structure for table "seatmap"
CREATE TABLE "seatmap" (
"id" serial PRIMARY KEY,
"map_data" text NOT NULL,
"updated_at" timestamp DEFAULT CURRENT_TIMESTAMP
);

--

-- Default seat map
INSERT INTO "seatmap" ("map_data") VALUES (E'wwwwwwwwwwwwwwwww\nwewwfffffffffwkkw\nwffwf#######fwkkw\nwffdf#######fdkkw\nwdwwf#######fwkkw\nwfbwfffffffffwkkw\nwwwwwwwwdwwwwwwww\nwfffffffffffffffw\nwf#############fw\nwf#############fw\nwf#############fw\nwfffffffffffffffw\nwwwwwwwwwwwwwwwww');

--

-- Table structure for table "rate_limits"
CREATE TABLE "rate_limits" (
"id" serial PRIMARY KEY,
"ip_address" varchar(45) NOT NULL,
"action" varchar(20) NOT NULL,
"attempted_at" timestamp DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_rate_lookup ON "rate_limits" ("ip_address", "action", "attempted_at");

--

-- Table structure for table "settings"
CREATE TABLE "settings" (
"setting_key" varchar(100) PRIMARY KEY,
"setting_value" text NOT NULL
);

-- Default email templates
INSERT INTO "settings" ("setting_key", "setting_value") VALUES
('email_tpl_reset_en', E'<p>Hello <strong>{{nickname}}</strong>,</p>\n<p>We received a request to reset your password. Click the button below to choose a new one:</p>\n<p style="text-align:center;margin:25px 0;"><a href="{{reset_link}}" style="display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;">Reset Password</a></p>\n<p>If the button does not work, copy and paste this link into your browser:<br><a href="{{reset_link}}" style="color:#4a90d9;word-break:break-all;">{{reset_link}}</a></p>\n<p style="color:#999;font-size:13px;">If you did not request a password reset, you can safely ignore this email.</p>'),
('email_tpl_reset_no', E'<p>Hei <strong>{{nickname}}</strong>,</p>\n<p>Vi har mottatt en forespørsel om å tilbakestille passordet ditt. Klikk på knappen nedenfor for å velge et nytt:</p>\n<p style="text-align:center;margin:25px 0;"><a href="{{reset_link}}" style="display:inline-block;padding:12px 30px;background-color:#4a90d9;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;">Tilbakestill passord</a></p>\n<p>Hvis knappen ikke fungerer, kopier og lim inn denne lenken i nettleseren din:<br><a href="{{reset_link}}" style="color:#4a90d9;word-break:break-all;">{{reset_link}}</a></p>\n<p style="color:#999;font-size:13px;">Hvis du ikke har bedt om å tilbakestille passordet, kan du trygt ignorere denne e-posten.</p>'),
('email_tpl_test_en', E'<p>Hello,</p>\n<p>This is a test email sent from <strong>{{site_name}}</strong>.</p>\n<p>If you are reading this, your SMTP settings are configured correctly and emails are being delivered.</p>\n<p style="color:#999;font-size:13px;">No action is required. This email was sent by an administrator to verify the email configuration.</p>'),
('email_tpl_test_no', E'<p>Hei,</p>\n<p>Dette er en test-e-post sendt fra <strong>{{site_name}}</strong>.</p>\n<p>Hvis du leser dette, er SMTP-innstillingene konfigurert riktig og e-poster blir levert.</p>\n<p style="color:#999;font-size:13px;">Ingen handling er nødvendig. Denne e-posten ble sendt av en administrator for å bekrefte e-postkonfigurasjonen.</p>');
