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
