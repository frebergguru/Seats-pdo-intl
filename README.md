# Seats

A customizable seat booking system with multi-language support, an interactive room map editor, and a full admin panel. Designed for LAN parties, events, conferences, or any scenario where seat reservations are required.

---

## Features

- **Interactive seat map** — visual room layout with click-to-book confirmation, seat owner info, and seat changing
- **Multi-language support** — English and Norwegian built-in, easily extensible
- **Admin panel** — dark-themed dashboard for managing users, reservations, seat map, and all application settings
- **Interactive map editor** — visual drag-to-paint editor with tile palette, grid resize, text mode, file import, and download export
- **Database-stored settings** — site metadata, SMTP, password policy, and Argon2id parameters all configurable from the admin panel with live regex testers and SMTP test email
- **MySQL and PostgreSQL** — dual database support via PDO
- **Secure by default** — Argon2id password hashing, CSRF protection on all forms (including booking), security headers via `.htaccess`, session hardening, prepared statements, XSS-safe output escaping
- **Role-based access** — user and admin roles; admin accounts protected from self-deletion
- **Mobile responsive** — CSS grid seat map scales across screen sizes with accessible skip-to-content, semantic HTML, and keyboard focus indicators
- **Apache hardened** — `.htaccess` with directory protection, sensitive file blocking, compression, caching, and security headers

---

## Screenshots

![Screenshot - Not logged in](https://github.com/frebergguru/Seats-pdo-intl/raw/main/Docs/Screenshot.png)

*Seat map as seen by visitors (not logged in).*

![Screenshot - Logged in](https://github.com/frebergguru/Seats-pdo-intl/raw/main/Docs/Screenshot_logged_in.png)

*Seat map as seen by a logged-in admin user with a reserved seat.*

---

## Installation

### Prerequisites
- PHP 7.4 or higher (with Argon2id support)
- MySQL or PostgreSQL database
- Apache web server (for `.htaccess` support) or Nginx
- Composer

### Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/frebergguru/Seats-pdo-intl.git
   cd Seats-pdo-intl
   ```

2. Install PHPMailer with Composer:
   ```bash
   composer install
   ```

3. Configure the database connection in `includes/config.php`:
   - Set `DB_DRIVER` (`mysql` or `pgsql`)
   - Set `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`

4. Import the database schema (see [Database Setup](#database-setup) below for full details):
   - **MySQL** (as root):
     ```bash
     mysql -u root -p < Docs/Seats-MySQL.sql
     ```
   - **PostgreSQL** (as postgres superuser):
     ```bash
     psql -U lanparty -d lanparty < Docs/Seats-PostgreSQL.sql
     ```

5. Register a user through the web interface, then promote them to admin:
   ```sql
   UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');
   ```

6. Log out and back in. The **Admin Panel** link will appear in the footer menu.

All remaining settings (SMTP, site metadata, password policy, etc.) can be configured from **Admin Panel > Settings**.

### Upgrading existing installations

Run the migration script to add the admin, settings, and seatmap tables:

```bash
mysql -u root -p lanparty < Docs/migration-admin.sql
```

See `Docs/migration-admin.sql` for both MySQL and PostgreSQL commands.

---

## Database Setup

### MySQL

1. Import the schema as root (creates the database, tables, and default seat map):
   ```bash
   mysql -u root -p < Docs/Seats-MySQL.sql
   ```

2. Create the application user and grant permissions:
   ```sql
   CREATE USER 'lanparty'@'localhost' IDENTIFIED BY 'password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON lanparty.* TO 'lanparty'@'localhost';
   FLUSH PRIVILEGES;
   ```

### PostgreSQL

1. Create the database and user as the postgres superuser:
   ```sql
   CREATE USER lanparty WITH LOGIN PASSWORD 'password';
   CREATE DATABASE lanparty OWNER lanparty ENCODING 'UTF8';
   ```

2. Import the schema:
   ```bash
   psql -U lanparty -d lanparty < Docs/Seats-PostgreSQL.sql
   ```

3. Grant permissions (if needed):
   ```sql
   GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO lanparty;
   GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO lanparty;
   ```

---

## Room Map

The room layout is stored in the database (`seatmap` table) and managed via the admin panel's interactive map editor. A default map is seeded during database setup.

Map symbols:
```
# = seat
f = floor
w = wall
k = kitchen (🍽️)
b = bathroom/toilet (🚽)
d = door (🚪)
e = emergency exit
```

The map editor supports:
- **Visual mode** — click/drag to paint tiles from a color-coded palette
- **Text mode** — edit the raw character grid directly
- **Import** — upload a `.txt` file or paste map text
- **Download** — export the current map as a file

---

## Admin Panel

Accessible to users with the `admin` role. Dark-themed interface.

| Page | Description |
|------|-------------|
| **Dashboard** | Overview with user, reservation, and seat counts |
| **Users** | Add, edit, delete users; assign roles; reset passwords |
| **Reservations** | View and delete individual reservations, or purge all |
| **Map Editor** | Interactive visual/text editor with tile palette, grid resize, import/download |
| **Settings** | Site metadata, SMTP/email (with test email), password/nickname/fullname regex (with generators and live testers), Argon2id hashing parameters |

Admin accounts are protected from self-deletion (both from the admin panel and the regular "Delete account" page).

---

## Configuration

Only database connection settings remain in `includes/config.php`. Everything else is stored in the `settings` database table and editable from the admin panel:

| Category | Settings |
|----------|----------|
| **Site** | Description, keywords, author, default language |
| **Email/SMTP** | Server, port, username, password, from name, from email, subject |
| **Security** | Password regex, nickname regex, fullname regex (each with visual generator and live tester) |
| **Hashing** | Argon2id memory cost, time cost, threads |

---

## Usage

1. Open the application in your browser.
2. Register a new account or log in.
3. Click a green (vacant) seat on the map and confirm to reserve it.
4. Click any red (occupied) seat to see who reserved it.
5. Click a green seat while you already have one to change your reservation.

---

## TO-DO List

### Planned Features
- Make the system GDPR-compliant (cookie consent, data export/deletion requests).
- Add support for sending rich HTML emails (templates, inline CSS).
- Add rate limiting to login, registration, and password reset.

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
