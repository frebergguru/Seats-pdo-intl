# Seats

A simple and customizable seat booking system with support for multiple languages and a custom room map. This project is designed for events, conferences, or any scenario where seat reservations are required.

---

## Features

- Multi-language support
- Customizable room map with various elements (seats, walls, doors, etc.)
- MySQL and PostgreSQL database support
- User-friendly interface
- Easily extensible for additional features

---

## Screenshot

![Screenshot](https://github.com/frebergguru/Seats-pdo-intl/raw/main/Docs/Screenshot.png)

*Example of the seat booking interface.*

---

## TO-DO List

### Planned Features
- Add functionality to change seat reservations, user information, and passwords.
- Make the system GDPR-compliant.
- Add support for sending rich emails.
- Create custom images instead of using Unicode characters.

### Improvements
- Remove duplicate and unnecessary code.
- Optimize the codebase for better performance.
- Add PDO `rollBack` functionality where needed.
- Improve mobile responsiveness of the main page.
- Add comments and documentation to the code.

---

## Room Map Legend

The `map.txt` file defines the layout of the room using the following symbols:

```
# = seat
f = floor
w = wall
k = kitchen
b = bathroom/toilet
d = door
e = emergency exit
```

---

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL or PostgreSQL database
- A web server (e.g., Apache or Nginx)

### Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/frebergguru/Seats-pdo-intl.git
   cd Seats-pdo-intl
   ```

2. Configure the database connection in `includes/config.php`.

3. Import the database schema:
   - For MySQL:
     ```bash
     mysql -u lanparty -p < Seats-MySQL.sql
     ```
   - For PostgreSQL:
     ```bash
     psql -U lanparty -d lanparty < Seats-PostgreSQL.sql
     ```

4. Grant the necessary permissions to the database user (see examples below).

---

## Database Setup

### MySQL Example

1. **Create a MySQL user:**
   ```sql
   CREATE USER 'lanparty'@'localhost' IDENTIFIED BY 'password';
   ```

2. **Create and import the MySQL database:**
   ```bash
   mysql -u lanparty -p < Seats-MySQL.sql
   ```

3. **Grant the user access to the database:**
   ```sql
   GRANT SELECT, INSERT, UPDATE, DELETE ON lanparty.* TO 'lanparty'@'localhost';
   FLUSH PRIVILEGES;
   ```

### PostgreSQL Example

1. **Create the PostgreSQL user:**
   ```sql
   CREATE USER lanparty WITH LOGIN PASSWORD 'password';
   ```

2. **Create and import the database:**
   ```bash
   psql -U lanparty -d lanparty < Seats-PostgreSQL.sql
   ```

3. **Grant the user access to the database and sequences:**
   ```sql
   GRANT CONNECT ON DATABASE lanparty TO lanparty;
   GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO lanparty;
   GRANT USAGE, SELECT ON SEQUENCE users_id_seq, reservations_id_seq TO lanparty;
   ```

---

## Usage

1. Open the application in your browser.
2. Log in or register as a new user.
3. Select a seat from the room map and confirm your reservation.

---

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or bugfix:
   ```bash
   git checkout -b feature-name
   ```
3. Commit your changes and push them to your fork:
   ```bash
   git push origin feature-name
   ```
4. Open a pull request on the main repository.

---

## License

This project is licensed under the GNU General Public License v3.0. See the [LICENSE](https://www.gnu.org/licenses/gpl-3.0.html) file for details.