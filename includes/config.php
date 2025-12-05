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
$pwd_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#^&*(),.?":{}|<>+-\/\[\]=_`~\$%])(?=.{8,26})[A-Za-z\d!@#^&*(),.?":{}|<>+-\/\[\]=_`~\$%]+$/';
//$pwd_regex = '/^(?=.{8,26})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+-=:;<>,.?\/]).*$/';

//Regex to check if the nickname is valid.
$nickname_regex = '/^[a-zA-Z0-9_-]{4,}$/';

//Regex for checking if the fullname is valid.
$fullname_regex = '/^[a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-]{2,}(\s[a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-]{2,})*$/u';

//Regex to check for illegal characters in the fullname.
$fullname_illegal_chars_regex = '/[^a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-\s]/u';

$smtp_port = "587";
$smtp_server = "";
$smtp_username = "";
$smtp_password = "";
$from_name = "Seat reservation";
$mail_subject = "Seat reservation";
$from_mail = "";

//Setup the Argon2id options that you want to use (You can use Argon2id.ods to calculate memory_cost).
$argon2id_options = [
    'memory_cost' => 1 << 17,
    'time_cost' => 4,
    'threads' => 6,
];

//Set the default language (valid options: en or no).
if (!isset($_SESSION['langID'])) {
    $_SESSION['langID'] = "no";
}

//Which database server do you want to use? (valid options: mysql or pgsql)
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'mysql');
}
//Database server host
if (!defined('DB_HOST')) {
    define('DB_HOST', '');
}
//Database name
if (!defined('DB_NAME')) {
    define('DB_NAME', '');
}
//Database server username
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', '');
}
//Database server password
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}
//Some database configuration
switch (DB_DRIVER) {
    case "mysql":
        $dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4;";
        break;
    case "pgsql":
        $dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";options='--client_encoding=UTF8'";
        break;
    default:
        throw new Exception("unsupported_database_driver");
}
$db_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
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
