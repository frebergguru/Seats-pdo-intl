# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**IMPORTANT**: When you modify CLAUDE.md, also review and update README.md, CHANGELOG.md, and Assets.md if the changes affect project documentation, features, file structure, or architecture.

## Project Overview

Seats is a PHP seat booking system for events/conferences/LAN parties. Users register, log in, and reserve or change seats from a configurable room map. Supports MySQL and PostgreSQL via PDO, with i18n (English and Norwegian). Includes a dark-themed admin panel for user/reservation/map/settings management.

## Setup

```bash
composer install                          # installs PHPMailer
# Configure DB credentials in includes/config.php
mysql -u root -p < Docs/Seats-MySQL.sql   # MySQL (as root)
psql -U lanparty -d lanparty < Docs/Seats-PostgreSQL.sql  # PostgreSQL
# Promote a user to admin:
# UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');
```

For existing installations, run `Docs/migration-admin.sql`. All other settings (SMTP, regex, Argon2id, etc.) are configured from Admin Panel > Settings.

No build step, test suite, or linter. Serve via Apache (`.htaccess` included) or Nginx with PHP 7.4+.

## Architecture

- **No framework** — plain PHP with PDO. Each top-level `.php` file is a page/endpoint.
- **Page flow**: `index.php` (seat map display) → `book.php` (POST booking handler with CSRF) → redirect back to `index.php`. Users can reserve a new seat or change their existing one. Auth pages: `login.php`, `register.php`, `forgot.php`, `deluser.php`, `logout.php`.
- **Request flow**: Every page includes `includes/config.php` (security headers via `.htaccess`, session, DB config, defaults, DB settings override) → `includes/i18n.php` (language loading) → `includes/header.php` / `includes/footer.php` (HTML shell with `<main>` and `<nav>`).
- **Session management**: `config.php` is the single entry point for `session_start()` with secure cookie flags. No other file should call `session_start()` directly. Security headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy) are handled by `.htaccess`.
- **DB driver abstraction**: Queries use ternary on `DB_DRIVER` for MySQL vs PostgreSQL differences (`lower()`, `FROM DUAL`). Maintain this pattern.
- **Room map**: Stored exclusively in `seatmap` DB table (no file fallback). Parsed by `getMapData($pdo)`. Rendered as a CSS grid with `--cell-size` variable. Tiles: `#`=seat, `f`=floor, `w`=wall, `k`=kitchen (🍽️), `b`=bathroom (🚽), `d`=door (🚪), `e`=exit. Default map seeded by SQL schema.
- **AJAX endpoints** (`ajax/`): JSON APIs for real-time registration validation — fullname (`ajax-fullname.php`), nickname, email, password strength. Called from `js/formcheck.js` and `js/pwdcheck.js`.
- **Auth**: Session-based. Login stores `$_SESSION['nickname']` and `$_SESSION['role']`. Session regenerated after login. Roles: `user` (default), `admin`. Admin self-deletion blocked at 3 layers.
- **Admin panel** (`admin/`): Dark-themed. Protected by `requireAdmin()`. Uses PRG pattern with flash messages and `noCacheHeaders()`. Pages: dashboard, users CRUD, reservations (with purge all), interactive map editor (visual + text + import/export), settings (with SMTP test email and regex generators).
- **Settings**: Stored in `settings` DB table. `config.php` sets defaults then loads overrides from DB. Editable from admin panel with live regex testers and visual password regex builder.
- **Security**: `.htaccess` handles headers, directory protection, static file blocking. PHP handles CSRF (POST + `hash_equals()` with `?? ''` fallback), Argon2id hashing, prepared statements, `htmlspecialchars()` output escaping. Passwords never trimmed.

## Key Conventions

- PDO prepared statements with named parameters (`:param`). Never reuse the same parameter name in one statement.
- Output escaping: `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- Nickname comparisons: always `mb_strtolower()`.
- Language strings in `includes/i18n/en.php` and `includes/i18n/no.php` as `$langArray`. Both files must stay in sync.
- Admin pages: set `$baseUrl = '../'` before header/footer, call `requireAdmin()` and `noCacheHeaders()`.
- `hash_equals($_SESSION['csrf_token'] ?? '', ...)` — always use `?? ''` fallback.
- All `<button>` elements inside or near forms must have explicit `type="button"` or `type="submit"`.
- `getDBConnection()` returns null on failure. `getMapData($pdo)` accepts optional PDO. `requireAdmin()` returns PDO for reuse.
- `setFlash()` / `getFlash()` for admin page messages after PRG redirects.
