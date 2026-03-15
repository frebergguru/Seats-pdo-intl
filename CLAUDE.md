# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Seats is a PHP seat booking system for events/conferences/LAN parties. Users register, log in, and reserve seats from a configurable room map. Supports MySQL and PostgreSQL via PDO, with i18n (English and Norwegian). Includes an admin panel for user/reservation/map management.

## Setup

```bash
composer install                          # installs PHPMailer
# Configure DB and SMTP in includes/config.php
mysql -u lanparty -p < Docs/Seats-MySQL.sql       # MySQL
psql -U lanparty -d lanparty < Docs/Seats-PostgreSQL.sql  # PostgreSQL
# Promote a user to admin:
# UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');
```

For existing installations, run `Docs/migration-admin.sql` to add the `role` column and `seatmap` table.

No build step, test suite, or linter exists. Serve via Apache/Nginx with PHP 7.4+.

## Architecture

- **No framework** — plain PHP with PDO, no routing layer. Each top-level `.php` file is a page/endpoint.
- **Page flow**: `index.php` (seat map display) → `book.php` (booking handler) → redirect back to `index.php`. Auth pages: `login.php`, `register.php`, `forgot.php`, `deluser.php`, `logout.php`. Admin pages in `admin/` directory.
- **Request flow**: Every page includes `includes/config.php` (session with secure cookie settings, DB config, regex patterns, constants) → `includes/i18n.php` (language loading) → `includes/header.php` / `includes/footer.php` (HTML shell). Admin pages use `$baseUrl = '../'` before including header/footer so asset paths resolve correctly from the subdirectory.
- **Session management**: `config.php` is the single entry point for `session_start()` with secure cookie flags (httponly, secure, samesite=Strict, strict_mode). No other file should call `session_start()` directly.
- **DB driver abstraction**: Many queries use ternary on `DB_DRIVER` for MySQL vs PostgreSQL differences (e.g., `lower()` usage, `FROM DUAL`). This pattern must be maintained when adding/modifying queries.
- **Room map**: Stored in `seatmap` DB table. Parsed by `getMapData($pdo)` in `includes/functions.php`. Rendered as a CSS grid in `index.php`. Editable via the admin panel's interactive map editor. Default map is seeded by the SQL schema.
- **AJAX endpoints** (`ajax/`): JSON APIs for real-time registration form validation (email uniqueness, nickname uniqueness, password strength). Called from `js/formcheck.js` and `js/pwdcheck.js`.
- **Password reset flow** (`forgot.php`): generates token → stores in `users.forgottoken` → emails link via PHPMailer → validates token with `hash_equals()` → allows password change. All in one file with branching logic.
- **Auth**: Session-based. Login stores `$_SESSION['nickname']` and `$_SESSION['role']`. Session is regenerated after login. Roles: `user` (default), `admin`.
- **Admin panel** (`admin/`): Protected by `requireAdmin()` guard in `includes/functions.php`. Pages: dashboard (`index.php`), user CRUD (`users.php`, `user_edit.php`), reservation management (`reservations.php`), map editor (`map.php`).
- **Passwords**: Argon2id with configurable parameters in `config.php`. Never trimmed.
- **CSRF**: All forms use `hash_equals()` for timing-safe token comparison. Tokens regenerated per GET request.

## Key Conventions

- All DB queries use PDO prepared statements with named parameters (`:param`). Never reuse the same named parameter in one statement.
- Output escaping uses `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- Nickname comparisons are always lowercased (`mb_strtolower`).
- Language strings are in `includes/i18n/en.php` and `includes/i18n/no.php` as `$langArray` entries. Both files must be kept in sync when adding keys.
- Admin pages set `$baseUrl = '../'` before including header/footer.
- `getDBConnection()` in `functions.php` returns null on failure (no throw). `getMapData($pdo)` accepts optional PDO to avoid creating duplicate connections.
- `requireAdmin()` returns the PDO connection for reuse by the calling admin page.
