<?php
/*
 * This file is part of Seats-pdl-intl.
 *
 * Copyright (C) 2023-2025 Morten Freberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session and invalidate the session cookie
if (session_status() === PHP_SESSION_ACTIVE) {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Invalidate the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
}

// Redirect the user to the home page (or a safe location)
$redirectUrl = filter_var('index.php', FILTER_SANITIZE_URL);
header("Location: " . $redirectUrl);
exit;
?>