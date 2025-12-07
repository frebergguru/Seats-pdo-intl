<?php

/*
 * This file is part of Seats-pdl-intl.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start(); // Ensure the session is started
require 'config.php';

// Get the list of allowed languages
$allowedLangs = array_map(function ($file) {
    return basename($file, '.php');
}, glob(__DIR__ . '/i18n/*.php'));

// Check if the user has selected a language
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs, true)) {
    // Sanitize and set the selected language in the session
    $_SESSION['langID'] = htmlspecialchars($_GET['lang']);
}

// Use the language from the session or default to English
$langID = $_SESSION['langID'] ?? 'en';
if (!in_array($langID, $allowedLangs, true)) {
    $langID = 'en'; // Default to English if invalid
}

// Construct the file path securely
$langFile = __DIR__ . '/i18n/' . $langID . '.php';

// Validate the file path strictly
if (in_array($langID, $allowedLangs, true) && file_exists($langFile)) {
    include $langFile;
} else {
    // Fallback to English if the file is missing or invalid
    include __DIR__ . '/i18n/en.php';
}
?>
