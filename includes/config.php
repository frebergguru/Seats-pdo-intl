<?php
//Check if session is started it not start session
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$site_description = 'Seat registration';
$site_keywords = 'seat, registration';
$site_author = 'Hypnotize';

$pwd_regex = '/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+-=:;<>,.?\/]).*$/';
$fullname_regex = '/^[a-zA-ZæøåÆØÅ]{2,}(\s[a-zA-ZæøåÆØÅ]{2,})*$/';
$nickname_regex = '/^[a-zA-Z0-9_-]{4,}$/';
$fullname_illegal_chars_regex = '/[^a-zA-ZæøåÆØÅ\s]/g';

$from_name = "Seat reservation";
$mail_subject = "Seat reservation";
$from_mail = "hypnotize@lastnetwork.net";

if (!defined('DB_HOST')) {
	define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
	define('DB_NAME', 'lanparty');
}
if (!defined('DB_USERNAME')) {
	define('DB_USERNAME', 'lanparty');
}
if (!defined('DB_PASSWORD')) {
	define('DB_PASSWORD', '');
}
if (!defined('USERS_TABLE')) {
	define('USERS_TABLE', 'users');
}
if (!defined('RSEAT_TABLE')) {
	define('RSEAT_TABLE', 'rseat');
}
if (!defined('CONFIG_TABLE')) {
	define('CONFIG_TABLE', 'config');
}
if (!isset($formstatus)) {
	$formstatus = false;
}
if (!isset($home)) {
	$home = null;
}
if (!isset($left)) {
	$left = null;
}
if (!isset($pwdchanged)) {
	$pwdchanged = null;
}
if (!isset($seatid)) {
	$seatid = null;
}
if (!isset($_SESSION['langID'])) {
	$_SESSION['langID'] = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
}
?>
