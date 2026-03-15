# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**IMPORTANT**: When you modify CLAUDE.md, also review and update README.md, CHANGELOG.md, and Assets.md if the changes affect project documentation, features, file structure, or architecture.

## Project Overview

Seats is a PHP seat booking system for events/conferences/LAN parties. Users register, log in, and reserve or change seats from a configurable room map. Supports MySQL and PostgreSQL via PDO, with i18n (English and Norwegian). Includes a dark-themed admin panel for user/reservation/map/settings management. GDPR-compliant with privacy policy, data export, consent tracking, and rate limiting.

## Setup

```bash
composer install                          # installs PHPMailer
# Configure DB credentials in includes/config.php
mysql -u root -p < Docs/Seats-MySQL.sql   # MySQL (as root)
psql -U lanparty -d lanparty < Docs/Seats-PostgreSQL.sql  # PostgreSQL
# Promote a user to admin:
# UPDATE users SET role = 'admin' WHERE lower(nickname) = lower('your_nickname');
```

For existing installations, run `Docs/migration-admin.sql`. All other settings (SMTP, regex, Argon2id, email templates, etc.) are configured from Admin Panel > Settings.

No build step, test suite, or linter. Serve via Apache (`.htaccess` included) or Nginx with PHP 7.4+.

## Architecture

- **No framework** — plain PHP with PDO. Each top-level `.php` file is a page/endpoint.
- **Page flow**: `index.php` (seat map display) → `book.php` (POST booking handler with CSRF) → redirect back. Users can reserve or change seats. Auth pages: `login.php`, `register.php`, `forgot.php`, `deluser.php`, `logout.php`. GDPR pages: `privacy.php`, `export.php`.
- **Request flow**: Every page includes `includes/config.php` (session, DB config, defaults, DB settings override, `$email_templates` from DB) → `includes/i18n.php` (language loading, saves preference to DB on switch) → `includes/header.php` / `includes/footer.php` (HTML shell with `<main>`, `<nav>`, skip-link).
- **Session management**: `config.php` is the single entry point for `session_start()`. Security headers handled by `.htaccess`. No other file should call `session_start()`.
- **DB driver abstraction**: Queries use ternary on `DB_DRIVER` for MySQL vs PostgreSQL differences. Maintain this pattern.
- **Room map**: Stored exclusively in `seatmap` DB table. Parsed by `getMapData($pdo)`. Rendered as a CSS grid with `--cell-size` variable. Tiles: `#`=seat, `f`=floor, `w`=wall, `k`=kitchen, `b`=bathroom, `d`=door, `e`=exit. Default map seeded by SQL schema.
- **AJAX endpoints** (`ajax/`): JSON APIs for registration validation — fullname, nickname, email, password strength.
- **Auth**: Session-based. Login stores `$_SESSION['nickname']`, `$_SESSION['role']`, restores `$_SESSION['langID']` from `users.language`. Roles: `user`, `admin`. Admin self-deletion blocked at 3 layers.
- **Language persistence**: `users.language` column. Saved on language switch (`i18n.php`), on registration, on admin user creation. Restored on login.
- **Admin panel** (`admin/`): Dark-themed. Protected by `requireAdmin()`. PRG pattern with `setFlash()`/`getFlash()` and `noCacheHeaders()`. Pages: dashboard, users CRUD, reservations (with purge all), interactive map editor, settings (SMTP + test email, per-language email templates with Code/Preview editor, regex generators with live testers, Argon2id params).
- **Email system**: `sendMail()` in `functions.php` wraps PHPMailer with a styled HTML template. `getEmailTemplate($type)` resolves per-language templates from `$email_templates` at call time. Templates stored exclusively in DB `settings` table. Supports `{{nickname}}`, `{{reset_link}}`, `{{site_name}}` placeholders.
- **Settings**: Stored in `settings` DB table. `config.php` sets defaults then loads overrides. Email templates are DB-only (no config.php fallback).
- **GDPR**: Privacy policy page (`privacy.php`), data export as JSON (`export.php`), `users.privacy_consent` timestamp, consent checkbox on registration, privacy link in footer.
- **Rate limiting**: `rate_limits` DB table. `checkRateLimit()` / `recordRateAttempt()`. Login: 5/15min, Register: 3/15min, Forgot: 3/15min per IP. Auto-cleanup after 1 hour.
- **Security**: `.htaccess` handles headers, directory protection, sensitive file blocking, compression, caching. PHP handles CSRF (POST + `hash_equals()` with `?? ''`), Argon2id hashing, prepared statements, `htmlspecialchars()`. Passwords never trimmed.

## Key Conventions

- PDO prepared statements with named parameters. Never reuse the same parameter name.
- Output escaping: `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- Nickname comparisons: always `mb_strtolower()`.
- Language strings in `includes/i18n/en.php` and `no.php` as `$langArray` (211 keys). Both must stay in sync.
- Admin pages: set `$baseUrl = '../'` before header/footer, call `requireAdmin()` and `noCacheHeaders()`.
- `hash_equals($_SESSION['csrf_token'] ?? '', ...)` — always use `?? ''`.
- All `<button>` elements must have explicit `type="button"` or `type="submit"`.
- `getEmailTemplate($type)` resolves language at call time from `$_SESSION['langID']`, not at page load.
- `$email_templates` initialized before try/catch in config.php, populated from DB inside try.
- `sendMail()` auto-loads PHPMailer via `require_once`, no need for callers to include vendor/autoload.
