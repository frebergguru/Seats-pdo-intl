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

require_once 'config.php';

// Get the list of allowed languages
$allowedLangs = array_map(function ($file) {
    return basename($file, '.php');
}, glob(__DIR__ . '/i18n/*.php'));

// Check if the user has selected a language
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs, true)) {
    $_SESSION['langID'] = $_GET['lang'];

    // Save preference to DB if logged in
    if (!empty($_SESSION['nickname'])) {
        try {
            $_i18n_pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
            $_i18n_stmt = $_i18n_pdo->prepare("UPDATE users SET language = :lang WHERE lower(nickname) = :nick");
            $_i18n_stmt->execute([':lang' => $_GET['lang'], ':nick' => mb_strtolower($_SESSION['nickname'])]);
            unset($_i18n_pdo, $_i18n_stmt);
        } catch (PDOException $e) {
            // Column may not exist yet
        }
    }
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
