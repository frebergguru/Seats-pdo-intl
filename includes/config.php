<?php
/*
 * This file is part of Seats-pdo-intl.
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
 *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

//Set secure session cookie parameters before starting the session
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// ============================================================
// DATABASE CONNECTION SETTINGS (must remain in this file)
// ============================================================

//Which database server do you want to use? (valid options: mysql or pgsql)
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'mysql');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'lanparty');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'seatuser');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'seatpassword');
}

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

// Table name constants
if (!defined('USERS_TABLE')) {
    define('USERS_TABLE', 'users');
}
if (!defined('RSEAT_TABLE')) {
    define('RSEAT_TABLE', 'rseat');
}
if (!defined('CONFIG_TABLE')) {
    define('CONFIG_TABLE', 'config');
}
if (!defined('SEATMAP_TABLE')) {
    define('SEATMAP_TABLE', 'seatmap');
}

// ============================================================
// DEFAULT VALUES (overridden by database settings if available)
// ============================================================

// Site metadata
$site_description = 'Seat registration';
$site_keywords = 'seat, registration';
$site_author = 'Hypnotize';

// Default language (valid options: en or no)
$default_language = 'no';

// Regex patterns
$pwd_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,26}$/';
$nickname_regex = '/^[a-zA-Z0-9_-]{4,}$/';
$fullname_regex = '/^[a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-]{2,}(\s[a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-]{2,})*$/u';
$fullname_illegal_chars_regex = '/[^a-zA-ZæøåÆØÅÀ-ÖØ-öø-ÿ\'\-\s]/u';

// SMTP / Email settings
$smtp_port = '587';
$smtp_server = '';
$smtp_username = '';
$smtp_password = '';
$from_name = 'Seat reservation';
$mail_subject = 'Seat reservation';
$from_mail = '';

// Argon2id options (OWASP-recommended balance of security and compatibility)
// memory_cost: 65536 KiB (64 MiB) — PHP's default, works on most hosts with 512MB+ RAM
// time_cost: 3 — number of iterations
// threads: 1 — safest for compatibility; not all systems/builds support multiple threads
$argon2id_options = [
    'memory_cost' => 1 << 16,
    'time_cost' => 3,
    'threads' => 1,
];

// ============================================================
// LOAD OVERRIDES FROM DATABASE
// ============================================================

try {
    $_cfg_pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
    $_cfg_stmt = $_cfg_pdo->query("SELECT setting_key, setting_value FROM settings");
    $_db = [];
    while ($_row = $_cfg_stmt->fetch(PDO::FETCH_ASSOC)) {
        $_db[$_row['setting_key']] = $_row['setting_value'];
    }

    // Site
    if (isset($_db['site_description']))    $site_description = $_db['site_description'];
    if (isset($_db['site_keywords']))       $site_keywords = $_db['site_keywords'];
    if (isset($_db['site_author']))         $site_author = $_db['site_author'];
    if (isset($_db['default_language']))    $default_language = $_db['default_language'];

    // Regex
    if (isset($_db['pwd_regex']))                   $pwd_regex = $_db['pwd_regex'];
    if (isset($_db['nickname_regex']))              $nickname_regex = $_db['nickname_regex'];
    if (isset($_db['fullname_regex']))              $fullname_regex = $_db['fullname_regex'];
    if (isset($_db['fullname_illegal_chars_regex']))$fullname_illegal_chars_regex = $_db['fullname_illegal_chars_regex'];

    // SMTP
    if (isset($_db['smtp_port']))       $smtp_port = $_db['smtp_port'];
    if (isset($_db['smtp_server']))     $smtp_server = $_db['smtp_server'];
    if (isset($_db['smtp_username']))   $smtp_username = $_db['smtp_username'];
    if (isset($_db['smtp_password']))   $smtp_password = $_db['smtp_password'];
    if (isset($_db['from_name']))       $from_name = $_db['from_name'];
    if (isset($_db['mail_subject']))    $mail_subject = $_db['mail_subject'];
    if (isset($_db['from_mail']))       $from_mail = $_db['from_mail'];

    // Argon2id
    if (isset($_db['argon2id_memory_cost'])) $argon2id_options['memory_cost'] = (int)$_db['argon2id_memory_cost'];
    if (isset($_db['argon2id_time_cost']))   $argon2id_options['time_cost'] = (int)$_db['argon2id_time_cost'];
    if (isset($_db['argon2id_threads']))     $argon2id_options['threads'] = (int)$_db['argon2id_threads'];

    unset($_cfg_pdo, $_cfg_stmt, $_db, $_row);
} catch (PDOException $e) {
    // Settings table may not exist yet — use defaults
}

// ============================================================
// SESSION AND VARIABLE DEFAULTS
// ============================================================

if (!isset($_SESSION['langID'])) {
    $_SESSION['langID'] = $default_language;
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
