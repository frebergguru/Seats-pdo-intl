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

--

-- Table structure for table "reservations" 
CREATE TABLE "reservations" (
"id" serial PRIMARY KEY,
"taken" integer,
"user_id" integer NOT NULL
);

--

-- DROP the table "users" if it exists
DROP TABLE IF EXISTS "users";

--

-- Table structure for table "users"
CREATE TABLE "users" (
"id" serial PRIMARY KEY,
"fullname" varchar(255) NOT NULL COLLATE "C",
"nickname" varchar(255) NOT NULL COLLATE "C",
"password" varchar(97) NOT NULL COLLATE "C",
"email" varchar(255) NOT NULL COLLATE "C",
"forgottoken" varchar(64) COLLATE "C",
"rseat" integer
);