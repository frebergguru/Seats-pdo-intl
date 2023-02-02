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

//Check if session is started it not start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$site_description = 'Seat registration';
$site_keywords = 'seat, registration';
$site_author = 'Hypnotize';

//Regex to check if the password is valid.
$pwd_regex = '/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+-=:;<>,.?\/]).*$/';

//Regex to check if the nickname is valid.
$nickname_regex = '/^[a-zA-Z0-9_-]{4,}$/';

//Regex for checking if the fullname is valid.
$fullname_regex = '/^[a-zA-ZæøåÆØÅ]{2,}(\s[a-zA-ZæøåÆØÅ]{2,})*$/';

//Regex to check for illegal characters in the fullname.
$fullname_illegal_chars_regex = '/[^a-zA-ZæøåÆØÅ\s]/g';

$from_name = "Seat reservation";
$mail_subject = "Seat reservation";
$from_mail = "hypnotize@lastnetwork.net";

//Which database server do you want to use? (valid options: mysql or pgsql)
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'pgsql');
}
//Database server host
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
//Database name
if (!defined('DB_NAME')) {
    define('DB_NAME', 'lanparty');
}
//Database server username
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'lanparty');
}
//Database server password
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}

//DO NOT CHANGE ANYTHING FROM HERE!!
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
if (!isset($email)) {
    $email = null;
}
if (!isset($nickname)) {
    $nickname = null;
}
if (!isset($fullname)) {
    $fullname = null;
}
if (!isset($deluser)) {
    $deluser = null;
}
if (!isset($_SESSION['langID'])) {
    $_SESSION['langID'] = "en";
}
?>
