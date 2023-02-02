CREATE DATABASE lanparty
  WITH (
    ENCODING = 'UTF8',
    TEMPLATE = template0
  );

-- Table structure for table "reservations"
DROP TABLE IF EXISTS "reservations";
CREATE TABLE "reservations" (
"id" serial PRIMARY KEY,
"taken" integer,
"user_id" integer NOT NULL
);

--

-- Table structure for table "users"
DROP TABLE IF EXISTS "users";
CREATE TABLE "users" (
"id" serial PRIMARY KEY,
"fullname" varchar(255) NOT NULL,
"nickname" varchar(255) NOT NULL,
"password" varchar(97) NOT NULL,
"email" varchar(255) NOT NULL,
"forgottoken" varchar(64),
"rseat" integer
);