# Assets

## Directory Structure
```
./
├── Docs/
│   └── Seats-MySQL.sql
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
│   ├── config.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── i18n.php
├── js/
│   ├── formcheck.js
│   ├── jquery-3.6.3.min.js
│   ├── jquery-3.7.1.min.js
│   ├── pwdcheck.js
│   └── pwdreq.js
├── Assets.md
├── book.php
├── default.css
├── deluser.php
├── forgot.php
├── index.php
├── login.php
├── logout.php
├── map.txt
└── register.php
```

## JavaScript
- `js/formcheck.js`: Handles real-time validation for nickname and email inputs using AJAX. It checks for availability, validity, and usage, providing visual feedback to the user.
- `js/jquery-3.6.3.min.js`: jQuery library version 3.6.3.
- `js/jquery-3.7.1.min.js`: jQuery library version 3.7.1.
- `js/pwdcheck.js`: Performs real-time password strength validation using AJAX. It checks if the password meets strength requirements and updates the UI accordingly.
- `js/pwdreq.js`: Manages the "Password Requirements" bubble popup. It handles showing/hiding the popup, focus management for accessibility, and closing via the Escape key or close button.

## CSS
- `css/bubblePopup.css`: Styles the password requirements bubble popup. It includes positioning, gradient backgrounds, borders, and responsive adjustments for smaller screens.
- `css/default.css`: The main stylesheet for the application. It defines global styles (body, links, forms), seat map grid layout, responsive behaviors, and various utility classes for status messages and buttons.
- `default.css`: A duplicate of `css/default.css` located in the root directory.

## Images
- `img/exit.jpg`
- `img/green.jpg`
- `img/loader.gif`
- `img/red.jpg`
- `img/wall.jpg`
- `img/yellow.jpg`

## Data
- `map.txt`: Text-based definition of the seat map layout.
- `Docs/Seats-MySQL.sql`: SQL script for creating the database schema.
