# Assets

## Directory Structure
```
./
├── .htaccess
├── Docs/
│   ├── Argon2id.ods
│   ├── LICENSE
│   ├── README.md
│   ├── Screenshot.png
│   ├── Screenshot_logged_in.png
│   ├── Screenshot_map_editor.png
│   ├── Seats-MySQL.sql
│   ├── Seats-PostgreSQL.sql
│   └── migration-admin.sql
├── admin/
│   ├── .htaccess
│   ├── ajax-test-email.php
│   ├── index.php
│   ├── map.php
│   ├── reservations.php
│   ├── settings.php
│   ├── user_edit.php
│   └── users.php
├── ajax/
│   ├── ajax-email.php
│   ├── ajax-fullname.php
│   ├── ajax-nick.php
│   └── ajax-pwd.php
├── css/
│   ├── bubblePopup.css
│   └── default.css
├── img/
│   ├── exit.jpg
│   ├── green.jpg
│   ├── loader.gif
│   ├── red.jpg
│   ├── wall.jpg
│   └── yellow.jpg
├── includes/
│   ├── i18n/
│   │   ├── en.php
│   │   └── no.php
│   ├── config.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── i18n.php
├── js/
│   ├── formcheck.js
│   ├── jquery-3.7.1.min.js
│   ├── pwdcheck.js
│   └── pwdreq.js
├── Assets.md
├── CHANGELOG.md
├── CLAUDE.md
├── LICENSE
├── README.md
├── book.php
├── composer.json
├── deluser.php
├── export.php
├── forgot.php
├── index.php
├── login.php
├── logout.php
├── privacy.php
└── register.php
```

## Admin Panel
- `admin/index.php`: Dashboard with user, reservation, and seat count stats.
- `admin/users.php`: User list with role badges, edit/delete actions.
- `admin/user_edit.php`: Add/edit user form with role, optional password reset, language default.
- `admin/reservations.php`: Reservation list with individual delete and purge all.
- `admin/map.php`: Interactive visual map editor with tile palette, grid resize, text mode, import/download.
- `admin/settings.php`: All settings — site, SMTP (with test), per-language email templates (Code/Preview), regex generators with live testers, Argon2id.
- `admin/ajax-test-email.php`: AJAX endpoint for sending SMTP test emails.
- `admin/.htaccess`: Extra protection for admin directory.

## AJAX Endpoints
- `ajax/ajax-fullname.php`: Real-time fullname validation against regex patterns.
- `ajax/ajax-nick.php`: Real-time nickname validation and availability check.
- `ajax/ajax-email.php`: Real-time email validation and uniqueness check.
- `ajax/ajax-pwd.php`: Real-time password strength validation against regex.

## Public Pages
- `index.php`: Seat map display with interactive booking, seat info, and symbol legend.
- `book.php`: POST-only seat booking/changing handler with CSRF protection.
- `login.php`: Login form with CSRF and rate limiting. Restores user's language preference.
- `register.php`: Registration form with CSRF, rate limiting, AJAX validation, privacy consent.
- `forgot.php`: Password reset flow — email request, token validation, password change.
- `deluser.php`: Account deletion (blocked for admin accounts).
- `logout.php`: Session destruction with cookie invalidation.
- `privacy.php`: GDPR privacy policy page.
- `export.php`: Personal data export as JSON download.

## JavaScript
- `js/formcheck.js`: Real-time AJAX validation for fullname, nickname, and email on registration.
- `js/jquery-3.7.1.min.js`: jQuery library version 3.7.1.
- `js/pwdcheck.js`: Real-time password strength validation using AJAX.
- `js/pwdreq.js`: Password requirements bubble popup with accessibility (Escape key, focus management).

## CSS
- `css/bubblePopup.css`: Password requirements bubble popup styles with responsive adjustments.
- `css/default.css`: Main stylesheet — global styles, seat map grid with CSS variables, form layout, legend, seat info/confirm popups, admin panel dark theme, email template editor, regex generators, accessibility (skip-link, sr-only, focus-visible), responsive breakpoints (768px, 480px).

## Includes
- `includes/config.php`: Session setup, DB connection, defaults, DB settings override, `$email_templates` from DB.
- `includes/functions.php`: Shared functions — `getDBConnection()`, `getMapData()`, `saveMapData()`, `loadSettings()`, `saveSettings()`, `isAdmin()`, `requireAdmin()`, `setFlash()`, `getFlash()`, `noCacheHeaders()`, `renderAdminNav()`, `genRandomKey()`, `getClientIP()`, `checkRateLimit()`, `recordRateAttempt()`, `exportUserData()`, `getEmailTemplate()`, `emailTemplate()`, `renderTemplate()`, `sendMail()`.
- `includes/i18n.php`: Language loader with session-based selection and DB persistence for logged-in users.
- `includes/header.php`: HTML head, asset loading with `$basePath`, skip-to-content link, `<main>` open.
- `includes/footer.php`: Navigation menu with role-aware links, privacy/export links, language selector, `</main>` close.
- `includes/i18n/en.php`: English translations (211 keys).
- `includes/i18n/no.php`: Norwegian translations (211 keys).

## Images
- `img/exit.jpg`: Emergency exit icon for seat map.
- `img/green.jpg`: Vacant seat indicator.
- `img/loader.gif`: Loading spinner for AJAX requests.
- `img/red.jpg`: Occupied seat indicator.
- `img/wall.jpg`: Wall tile for seat map.
- `img/yellow.jpg`: Current user's selected seat indicator.

## Configuration
- `.htaccess`: Apache security headers, directory protection, sensitive file blocking, compression, caching.
- `admin/.htaccess`: Admin directory protection, blocks GET on AJAX endpoint.
- `composer.json`: PHPMailer dependency.

## Database
- `Docs/Seats-MySQL.sql`: Full MySQL schema with default seat map and email template seeds.
- `Docs/Seats-PostgreSQL.sql`: Full PostgreSQL schema with default seat map and email template seeds.
- `Docs/migration-admin.sql`: Migration for existing installs (role, language, privacy_consent columns; seatmap, settings, rate_limits tables; email template seeds).
- `Docs/Argon2id.ods`: LibreOffice spreadsheet for calculating Argon2id parameters.

## Database Tables
| Table | Purpose |
|-------|---------|
| `users` | User accounts (fullname, nickname, email, password, role, language, rseat, forgottoken, privacy_consent) |
| `reservations` | Seat reservations (taken seat number, user_id) |
| `seatmap` | Room layout data (map_data text, updated_at) |
| `settings` | Application settings as key-value pairs (including email templates) |
| `rate_limits` | Rate limiting records (ip_address, action, attempted_at) |
