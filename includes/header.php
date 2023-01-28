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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo $langArray['header_title'];?></title>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo $site_description; ?>">
    <meta name="keywords" content="<?php echo $site_keywords; ?>">
    <meta name="author" content="<?php echo $site_author; ?>">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./css/default.css">
	<script src="./js/jquery-3.6.3.min.js"></script>
	<script>
		var langArray = <?php echo json_encode($langArray); ?>;
		var illegalChars = <?php echo $fullname_illegal_chars_regex; ?>;
		var validName = <?php echo $fullname_regex; ?>;
		var validNickname = <?php echo $nickname_regex; ?>
	</script>
</head>
<body>
<!--  main_wrapper start -->
<div class="main_wrapper">
    <!-- div wrapper start -->
    <div id="wrapper">