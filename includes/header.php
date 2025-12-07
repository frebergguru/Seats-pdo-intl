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

// Ensure required variables are set
$langID = htmlspecialchars($_SESSION['langID'] ?? 'en', ENT_QUOTES, 'UTF-8');
$headerTitle = htmlspecialchars($langArray['header_title'] ?? 'Default Title', ENT_QUOTES, 'UTF-8');
$siteDescription = htmlspecialchars($site_description ?? '', ENT_QUOTES, 'UTF-8');
$siteKeywords = htmlspecialchars($site_keywords ?? '', ENT_QUOTES, 'UTF-8');
$siteAuthor = htmlspecialchars($site_author ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?php echo $langID; ?>">

<head>
    <title><?php echo $headerTitle; ?></title>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo $siteDescription; ?>">
    <meta name="keywords" content="<?php echo $siteKeywords; ?>">
    <meta name="author" content="<?php echo $siteAuthor; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./css/default.css">
    <link rel="stylesheet" type="text/css" href="./css/bubblePopup.css">
    <script src="./js/jquery-3.7.1.min.js"></script>
    <script>
        var langArray = <?php echo json_encode($langArray ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var illegalChars = <?php echo json_encode($fullname_illegal_chars_regex ?? ''); ?>;
        var validName = <?php echo json_encode($fullname_regex ?? ''); ?>;
        var validNickname = <?php echo json_encode($nickname_regex ?? ''); ?>;
    </script>
</head>

<body>
    <div class="main_wrapper">
        <div id="wrapper">
