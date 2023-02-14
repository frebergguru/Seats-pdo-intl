# Seats
A simple seat booking system with support for multiple languages and a custom room map.

## Screenshot:
![Screenshot](https://github.com/frebergguru/Seats-pdo-intl/raw/main/Docs/Screenshot.png)

**THIS PROJECT IS NOT UPDATED FREQUENTLY**

**TO-DO list**
* Remove duplicate code
* Remove all unnecessary code
* Optimize the code
* Add more features like change seat reservation, user information and password, make it GDRP compliant, and so on.
* Add support for sending rich mail
* Make custom images instead of using unicode chars.
* Add PDO rollBack functionality everywhere it is needed.
* Make the mainpage more mobile friendly - Please contact me or create a new issue if you know how!
* Make comments in the code

## map.txt:
```
# = seat
f = floor
w = wall
k = kitchen
b = bathroom/toilet
d = door
e = emergency exit
```

## MySQL example:

1. **Create a MySQL user:**
```mysql
CREATE USER 'lanparty'@'localhost' IDENTIFIED BY 'password';
```

2. **Create and import the MySQL database with:**
```shell
mysql -u lanparty -p < Seats-MySQL.sql
```
3. **Grant the user access to the database:**
```mysql
GRANT SELECT, INSERT, UPDATE, DELETE ON lanparty.* TO 'lanparty'@'localhost';
FLUSH PRIVILEGES;
```

## PostgreSQL example:

1. **Create the PostgreSQL user:**
```pgsql
CREATE USER lanparty WITH LOGIN PASSWORD 'password';
```

2. **Create and import the database from the SQL:**
```shell
psql -U lanparty -d lanparty < Seats-PostgreSQL.sql
```

3. **Give the database user access to the database and to the sequences user_id_seq and reservations_id_seq:**
```pgsql
GRANT CONNECT ON DATABASE lanparty TO lanparty;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO lanparty;
GRANT USAGE, SELECT ON SEQUENCE users_id_seq, reservations_id_seq TO lanparty;
```
