<?php
$lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
if (!isset($_SESSION['langID'])) {
	$_SESSION['langID'] = "en";
}
if (isset($lang) && !empty($lang)) {
	$_SESSION['langID'] = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
}

include 'i18n/'.$_SESSION['langID'].'.php';
?>
