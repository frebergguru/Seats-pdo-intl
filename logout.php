<?php
/*
Copyright 2023 Morten Freberg

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the session is active and if the nickname session variable is set
if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['nickname'])) {
    // Destroy the session if both conditions are met
    session_destroy();
}

// Redirect the user to the current directory
header("Location: " . filter_var(dirname($_SERVER['REQUEST_URI']), FILTER_SANITIZE_URL));
// Exit the script
exit;
?>
