# Changelog

All notable changes to this project will be documented in this file.

## [v2.1] - 2026-03-15

### New Features
- **Seat changing** — users can click a vacant seat while already having one to change their reservation (old seat released, new seat booked atomically)
- **Purge all reservations** — admin reservations page has a "Purge all" button with confirmation
- **SMTP test email** — admin settings page can send a test email to verify SMTP configuration
- **Regex generators** — admin settings has visual builders for password, nickname, and fullname regex with live testers on every regex field
- **Fullname AJAX validation** — real-time fullname check on the registration form (`ajax/ajax-fullname.php`)
- **Apache `.htaccess`** — security headers, directory protection, sensitive file blocking, compression, browser caching, PHP hardening
- **Skip-to-content link** — accessible keyboard shortcut to skip navigation
- **Semantic HTML** — `<main>`, `<nav>`, `aria-live` regions, `aria-label` attributes, `role` attributes on popups
- **Keyboard focus indicators** — global `focus-visible` outline for all interactive elements
- **Kitchen and bathroom tiles** — seat map renders `k` as 🍽️ and `b` as 🚽 with legend entries
- **Floor visibility** — floor tiles now have a distinct beige color instead of being invisible

### Security
- **Seat booking CSRF protection** — `book.php` now requires POST with CSRF token (was GET)
- **Security headers** — `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy` via `.htaccess`
- **`.htaccess` hardening** — blocks access to `includes/`, `vendor/`, `Docs/`, dotfiles, and sensitive file extensions
- **CSRF null-safety** — all `hash_equals()` calls use `?? ''` fallback for expired sessions
- **All buttons typed** — every `<button>` has explicit `type="button"` or `type="submit"`

### Bug Fixes
- **Registration form close button** — `type="button"` prevents popup close from submitting the form
- **Language key fixes** — removed `invalid_csfr_token` typo, fixed `the_passwords_dosent_match` key mismatch
- **Missing i18n include** — `ajax/ajax-email.php` now includes language file
- **PHP 7.4 compatibility** — replaced `match()` expression in `ajax/ajax-email.php`
- **Symbol legend layout** — replaced inline `&nbsp;` spacing with flex grid for clean wrapping

### Cleanup
- **Removed 14 unused language keys** across EN and NO
- **Removed 5 dead variables** from `config.php` (`$formstatus`, `$pwdchanged`, `$email`, `$nickname`, `$fullname`, `$left`, `$deluser`)
- **Removed 4 unused table constants** (`USERS_TABLE`, `RSEAT_TABLE`, `CONFIG_TABLE`, `SEATMAP_TABLE`)
- **Removed unused `$register_page`** variable from `register.php`
- **Removed PHP security headers** from `config.php` (now handled by `.htaccess`)
- **Updated default seat map** with floor tiles around seats for better visibility

---

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

### Improvements
- **Mobile responsive** — CSS variable `--cell-size` for seat map scaling, responsive breakpoints at 768px and 480px
- **CSS overhaul** — Removed obsolete vendor prefixes, consolidated duplicate rules, `box-sizing: border-box`, dark-themed admin panel
- **Password policy** — Accepts any non-alphanumeric character as special character
- **Argon2id defaults** — 64 MiB memory, 3 iterations, 1 thread (OWASP-recommended, compatible with most hosts)
- **Removed `map.txt`** — Seat map stored exclusively in database; default map seeded by SQL schema
- **`getDBConnection()`** — Shared helper returns null on failure instead of throwing
- **`renderAdminNav()`** — Shared admin navigation function replaces duplicated HTML
- **Asset base path** — `$baseUrl` variable in header/footer for admin subdirectory compatibility

### Known Limitations
- **No rate limiting** — Login, registration, and password reset have no brute-force protection.
- **AJAX endpoints lack CSRF** — `ajax/ajax-email.php`, `ajax/ajax-nick.php`, `ajax/ajax-fullname.php` allow unauthenticated enumeration. Low severity since registration is public.
