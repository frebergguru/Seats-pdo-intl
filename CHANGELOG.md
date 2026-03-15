# Changelog

All notable changes to this project will be documented in this file.

## [v2.0] - 2026-03-15

### New Features
- **Admin panel** (`admin/`) — Dashboard with stats, user CRUD (add/edit/delete, role assignment, password reset), reservation management (view/delete), interactive map editor, and full settings page
- **Role-based access** — `users.role` column (`user`/`admin`). Admin link in footer menu. Admin pages protected by `requireAdmin()` guard. Admin accounts protected from self-deletion at 3 layers
- **Database-stored seat map** — `seatmap` table with default map seeded by SQL schema. Interactive visual editor with tile palette, click/drag painting, grid resize, text mode toggle, file import/upload, and download export
- **Database-stored settings** — `settings` table replaces hardcoded config values. Site metadata, SMTP/email, password policy regex, and Argon2id hashing parameters all editable from the admin panel
- **Seat booking confirmation** — Clicking a vacant seat shows a confirmation dialog before booking
- **Seat owner info** — Clicking any occupied seat shows the reserver's nickname. Works for all visitors including non-logged-in users
- **Migration script** — `Docs/migration-admin.sql` for upgrading existing installations
- **Flash messages** — PRG (Post-Redirect-Get) pattern with session-based flash messages across all admin pages
- **Admin UI** — Dark-themed admin panel with card layout, stat boxes, role badges, responsive tables, and form grid layouts

### Security Fixes
- **Password reset token bypass** — `forgot.php` now verifies the token against the database before allowing password changes
- **Password trimming** — Removed `trim()` from password inputs in `login.php`, `forgot.php`, and `ajax/ajax-pwd.php`
- **CSRF protection added** to `login.php` and `forgot.php` (both email and password change forms)
- **Timing-safe comparisons** — All CSRF and reset token checks use `hash_equals()`
- **Session fixation prevention** — `session_regenerate_id(true)` called after successful login
- **Secure session cookies** — `httponly`, `secure` (auto-detected), `samesite=Strict`, `use_strict_mode`
- **CSRF token regeneration** — Tokens regenerated on every GET request; PRG pattern prevents stale tokens on browser back
- **No-cache headers** — All admin pages send `Cache-Control: no-store` to prevent cached stale CSRF tokens
- **Deprecated filter removed** — Replaced `FILTER_SANITIZE_STRING` with `FILTER_DEFAULT` in `forgot.php`
- **XSS via PHP_SELF** — Escaped in `forgot.php` heredoc form actions
- **PHP 8.0+ syntax** — Replaced `match()` in `ajax/ajax-email.php` with PHP 7.4-compatible code
- **Close button form submission** — Added `type="button"` to popup close buttons in `register.php` and `forgot.php` to prevent accidental form submission

### Bug Fixes
- **Seat map rendering** — `index.php` now displays the interactive seat map (was previously only a booking handler)
- **PostgreSQL compatibility** — Fixed `FROM DUAL` syntax with DB driver switch; fixed duplicate named parameters
- **Nickname validation mismatch** — `ajax/ajax-nick.php` uses `$nickname_regex` from config
- **SQL wildcard abuse** — Changed `LIKE` to `=` in `ajax/ajax-nick.php`
- **Single session entry point** — Removed all redundant `session_start()` calls; `config.php` handles it
- **Logout security** — `logout.php` includes `config.php` for secure session cookie settings
- **Undefined variable** — Fixed `$stmt` reference in `login.php` error handler
- **Hardcoded HTTPS** — Redirects auto-detect scheme
- **CSRF token output escaping** — `htmlspecialchars()` on token values in all forms
- **Case-insensitive queries** — Unified `lower()` usage across MySQL and PostgreSQL
- **Double config include** — `i18n.php` uses `require_once`
- **Password reset form re-display** — Form shown again after validation errors
- **Missing i18n include** — `ajax/ajax-email.php` now includes `i18n.php`
- **CSRF key typo** — Consolidated `invalid_csfr_token` → `invalid_csrf_token` across all files
- **Language key mismatch** — Fixed `the_passwords_dosent_match` → `the_password_dosent_match` in `register.php`

### Improvements
- **Mobile responsive** — CSS variable `--cell-size` for seat map scaling, responsive breakpoints at 768px and 480px, stacked form labels on small screens
- **CSS overhaul** — Removed obsolete vendor prefixes, consolidated duplicate rules, `box-sizing: border-box`, dark-themed admin panel
- **Password policy** — Accepts any non-alphanumeric character as special character
- **Argon2id defaults** — 64 MiB memory, 3 iterations, 1 thread (OWASP-recommended, compatible with most hosts)
- **Removed `map.txt`** — Seat map stored exclusively in database; default map seeded by SQL schema
- **`getDBConnection()`** — Shared helper returns null on failure instead of throwing
- **`renderAdminNav()`** — Shared admin navigation function replaces duplicated HTML
- **Asset base path** — `$baseUrl` variable in header/footer for admin subdirectory compatibility

### Known Limitations
- **Seat booking via GET** — `book.php` accepts bookings via GET. Converting to POST requires frontend changes.
- **No rate limiting** — Login, registration, and password reset have no brute-force protection.
- **AJAX endpoints lack CSRF** — `ajax/ajax-email.php` and `ajax/ajax-nick.php` allow unauthenticated enumeration. Low severity since registration is public.
