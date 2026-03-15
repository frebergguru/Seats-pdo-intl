# Changelog

All notable changes to this project will be documented in this file.

## [v2.2] - 2026-03-15

### New Features
- **GDPR compliance** — privacy policy page (`privacy.php`), personal data export as JSON (`export.php`), `users.privacy_consent` timestamp, consent checkbox on registration, privacy link in footer
- **Rate limiting** — `rate_limits` DB table. Login: 5/15min, Register: 3/15min, Forgot: 3/15min per IP. Auto-cleanup after 1 hour.
- **Rich HTML email templates** — styled email layout with `sendMail()` wrapper, `emailTemplate()` for consistent branding, `renderTemplate()` for `{{placeholder}}` substitution, plain-text `AltBody` fallback
- **Per-language email templates** — stored in DB as `email_tpl_reset_en`, `email_tpl_reset_no`, `email_tpl_test_en`, `email_tpl_test_no`. Resolved at send time via `getEmailTemplate($type)` based on `$_SESSION['langID']`
- **Email template editor** — admin settings panel with per-language Code/Preview tabs, placeholder insertion toolbar, live HTML preview with sample data
- **Language persistence** — `users.language` column. Saved on every language switch, on registration, on admin user creation. Restored automatically on login.

### Changes
- **Email templates moved to DB** — removed hardcoded templates from `config.php`. Defaults seeded by SQL schema INSERT statements. `getEmailTemplate()` resolves at call time, not page load.
- **`sendMail()` replaces inline PHPMailer** — `forgot.php` and `admin/ajax-test-email.php` now call `sendMail()` instead of configuring PHPMailer directly. Auto-loads vendor/autoload via `require_once`.

### Database Changes
- Added `users.language` VARCHAR(5) column (default 'en')
- Added `users.privacy_consent` TIMESTAMP column
- Added `rate_limits` table (id, ip_address, action, attempted_at) with index
- Added `settings` seed data for 4 email templates (EN + NO for reset + test)

---

## [v2.1.1] - 2026-03-15

### New Features
- **Seat changing** — users can click a vacant seat while already having one to change their reservation (old seat released, new seat booked atomically)
- **Purge all reservations** — admin reservations page has a "Purge all" button with confirmation
- **SMTP test email** — admin settings page can send a test email to verify SMTP configuration
- **Regex generators** — admin settings has visual builders for password, nickname, and fullname regex with live testers on every regex field
- **Fullname AJAX validation** — real-time fullname check on the registration form (`ajax/ajax-fullname.php`)
- **Apache `.htaccess`** — security headers, directory protection, sensitive file blocking, compression, browser caching, PHP hardening
- **Accessibility** — skip-to-content link, semantic HTML (`<main>`, `<nav>`), `aria-live` regions, keyboard focus indicators
- **Kitchen and bathroom tiles** — seat map renders `k` as 🍽️ and `b` as 🚽 with legend entries
- **Floor visibility** — floor tiles now have a distinct beige color

### Security
- **Seat booking CSRF protection** — `book.php` now requires POST with CSRF token (was GET)
- **Security headers** — `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` via `.htaccess`
- **`.htaccess` hardening** — blocks access to `includes/`, `vendor/`, `Docs/`, dotfiles, sensitive file extensions
- **CSRF null-safety** — all `hash_equals()` calls use `?? ''` fallback
- **All buttons typed** — every `<button>` has explicit `type` attribute

### Bug Fixes
- Registration form close button, language key typos, missing i18n include, PHP 7.4 compatibility, symbol legend layout

### Cleanup
- Removed 14+ unused language keys, 5 dead variables, 4 unused table constants, dead code from config.php
- Updated default seat map with floor tiles

---

## [v2.0] - 2026-03-15

### New Features
- **Admin panel** — Dashboard, user CRUD, reservation management, interactive map editor, full settings page
- **Role-based access** — `users.role` column, admin self-deletion protection at 3 layers
- **Database-stored seat map** — `seatmap` table with interactive visual editor
- **Database-stored settings** — `settings` table replaces hardcoded config values
- **Seat booking confirmation** and **seat owner info**
- **Migration script**, **flash messages**, **dark-themed admin UI**

### Security Fixes
- Password reset token bypass, password trimming, CSRF on login/forgot, timing-safe comparisons, session fixation, secure cookies, token regeneration, no-cache headers, deprecated filters, XSS via PHP_SELF

### Bug Fixes
- Seat map rendering, PostgreSQL compatibility, nickname validation, SQL wildcard, single session entry, logout security, undefined variables, hardcoded HTTPS, CSRF escaping, case-insensitive queries, double config include, password reset form re-display

### Improvements
- Mobile responsive CSS, vendor prefix cleanup, password policy, Argon2id defaults, removed map.txt, shared helpers, admin navigation function, asset base path

### Known Limitations
- AJAX registration endpoints lack CSRF (low severity since registration is public)
