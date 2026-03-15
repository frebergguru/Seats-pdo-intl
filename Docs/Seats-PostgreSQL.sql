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
"role" varchar(20) NOT NULL DEFAULT 'user'
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
INSERT INTO "seatmap" ("map_data") VALUES (E'wwwwwwwwwwwwwwwww\nweww#########wkkw\nwffw#########wkkw\nwffd#########dkkw\nwdww#########wkkw\nwfbw#########wkkw\nwwwwwwwwdwwwwwwww\nw###############w\nw###############w\nw###############w\nw###############w\nw###############w\nwwwwwwwwwwwwwwwww');

--

-- Table structure for table "settings"
CREATE TABLE "settings" (
"setting_key" varchar(100) PRIMARY KEY,
"setting_value" text NOT NULL
);
