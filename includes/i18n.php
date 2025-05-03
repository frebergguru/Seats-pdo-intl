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
require 'config.php';

$allowedLangs = array_map(function ($file) {
    return basename($file, '.php');
}, glob(__DIR__ . '/i18n/*.php'));

// Validate and sanitize the language ID
$langID = $_SESSION['langID'] ?? 'en';
if (!in_array($langID, $allowedLangs, true)) {
    $langID = 'en'; // Default to English if invalid
}

// Construct the file path securely
$langFile = __DIR__ . '/i18n/' . $langID . '.php';

// Check if the language file exists before including it
if (file_exists($langFile)) {
    include $langFile;
} else {
    // Fallback to English if the file is missing
    include __DIR__ . '/i18n/en.php';
}
?>
