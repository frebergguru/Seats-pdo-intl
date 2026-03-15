# Assets

## Directory Structure
```
./
├── Docs/
│   ├── Argon2id.ods
│   ├── LICENSE
│   ├── README.md
│   ├── Screenshot.png
│   ├── Seats-MySQL.sql
│   ├── Seats-PostgreSQL.sql
│   └── migration-admin.sql
├── admin/
│   ├── index.php
│   ├── map.php
│   ├── reservations.php
│   ├── user_edit.php
│   └── users.php
├── ajax/
│   ├── ajax-email.php
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
├── forgot.php
├── index.php
├── login.php
├── logout.php
└── register.php
```

## JavaScript
- `js/formcheck.js`: Handles real-time validation for nickname and email inputs using AJAX. It checks for availability, validity, and usage, providing visual feedback to the user.
- `js/jquery-3.7.1.min.js`: jQuery library version 3.7.1.
- `js/pwdcheck.js`: Performs real-time password strength validation using AJAX. It checks if the password meets strength requirements and updates the UI accordingly.
- `js/pwdreq.js`: Manages the "Password Requirements" bubble popup. It handles showing/hiding the popup, focus management for accessibility, and closing via the Escape key or close button.

## CSS
- `css/bubblePopup.css`: Styles the password requirements bubble popup. It includes positioning, gradient backgrounds, borders, and responsive adjustments for smaller screens.
- `css/default.css`: The main stylesheet for the application. It defines global styles (body, links, forms), seat map CSS grid layout, responsive breakpoints (768px, 480px), and utility classes for status messages and buttons.

## Images
- `img/exit.jpg`: Emergency exit icon for seat map
- `img/green.jpg`: Vacant seat indicator
- `img/loader.gif`: Loading spinner for AJAX requests
- `img/red.jpg`: Occupied seat indicator
- `img/wall.jpg`: Wall tile for seat map
- `img/yellow.jpg`: Current user's selected seat indicator

## Data
- `Docs/Seats-MySQL.sql`: SQL script for creating the MySQL database schema.
- `Docs/Seats-PostgreSQL.sql`: SQL script for creating the PostgreSQL database schema.
