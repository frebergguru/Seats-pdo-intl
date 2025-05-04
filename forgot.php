<?php
/*
 * This file is part of Seats-pdl-intl.
 *
 * Copyright (C) 2023-2025 Morten Freberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getParam($name, $method = INPUT_GET, $sanitize = FILTER_SANITIZE_STRING) {
    return trim(filter_input($method, $name, $sanitize)) ?: null;
}

$nickname = mb_strtolower(getParam('nickname'));
$key = getParam('key');
$email = getParam('email', INPUT_POST, FILTER_VALIDATE_EMAIL);
$password = getParam('password', INPUT_POST);
$password2 = getParam('password2', INPUT_POST);
$pwdchanged = false;
$formstatus = null;

if ($password && !preg_match($pwd_regex, $password)) {
    echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_contains_illegal_characters'] . '</div><br><br>';
    $formstatus = 'FAIL';
}

if ($password && $password2 && $password !== $password2) {
    echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_dosent_match'] . '</div><br><br>';
    $formstatus = 'FAIL';
}

function getPDO() {
    global $dsn, $db_options;
    return new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
}

if ($password && $password2 && $key && $formstatus !== 'FAIL') {
    require 'includes/header.php';
    echo '<span class="srs-header">' . $langArray['new_password'] . '</span>
    <div class="srs-content">' . $langArray['password_changed_log_in'] . '.</div><br><br><br>';
    require 'includes/footer.php';

    $pwdhash = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
    try {
        $pdo = getPDO();
        $sql = DB_DRIVER === 'pgsql'
            ? "UPDATE users SET password = :password, forgottoken = NULL WHERE lower(nickname) = :nickname"
            : "UPDATE users SET password = :password, forgottoken = NULL WHERE nickname = :nickname";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password' => $pwdhash,
            ':nickname' => $nickname
        ]);
        $pwdchanged = true;
    } catch (PDOException $e) {
        error_log("Password Update Error: " . $e->getMessage());
    }
}

if ($nickname && $key && !$pwdchanged) {
    try {
        $pdo = getPDO();
        $sql = DB_DRIVER === 'pgsql'
            ? "SELECT forgottoken FROM users WHERE lower(nickname) = :nickname"
            : "SELECT forgottoken FROM users WHERE nickname = :nickname";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nickname' => $nickname]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Token Validation Error: " . $e->getMessage());
    }

    if ($result && mb_strtolower($key) === mb_strtolower($result['forgottoken'])) {
        require 'includes/header.php';
        $escapedNick = htmlspecialchars($nickname);
        $escapedKey = htmlspecialchars($key);
        echo <<<HTML
<form class="srs-container" method="POST" action="{$_SERVER["PHP_SELF"]}?nickname=$escapedNick&key=$escapedKey">
    <span class="srs-header">{$langArray['new_password']}</span>
    <div class="srs-content">
        <a href="#" id="passwordRequirements">{$langArray['password_requirements']}</a><br>
        <div class="bubble-container">
            <div class="bubble" id="bubblePopup">
                {$langArray['password_requirements_text']}
                <button id="closePopup">{$langArray['close_btn']}</button>
            </div>
        </div>
        <label for="password" class="srs-lb">{$langArray['password']}</label><input name="password" id="password" type="password" class="srs-tb"><br>
        <span id="pwstatus"></span><br>
        <label for="password2" class="srs-lb">{$langArray['repeat_password']}</label><input name="password2" id="password2" type="password" class="srs-tb"><br>
    </div>
    <div class="srs-footer">
        <div class="srs-button-container">
            <input type="submit" value="{$langArray['change_password_button']}" class="srs-btn">
        </div>
        <div class="srs-slope"></div>
    </div>
</form>
<script src="./js/pwdreq.js"></script>
<script src="./js/pwdcheck.js"></script>
HTML;
        require 'includes/footer.php';
    } else {
        require 'includes/header.php';
        echo "<span class=\"srs-header\">{$langArray['forgot_password_heading']} - {$langArray['error']}</span>
        <div class=\"srs-content\">{$langArray['wrong_nickname_or_verification_key']}</div><br><br><br>";
        require 'includes/footer.php';
    }
} elseif ($email) {
    require 'includes/header.php';
    echo "<span class=\"srs-header\">{$langArray['new_password']} - {$langArray['email']}</span>
    <div class=\"srs-content\">{$langArray['email_sent_instruction_page_text']}</div><br><br><br>";
    require 'includes/footer.php';

    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT nickname FROM users WHERE email=:email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $nickname = mb_strtolower($result['nickname']);
            $token = genRandomKey();

            $stmt = $pdo->prepare(
                DB_DRIVER === 'pgsql'
                ? "UPDATE users SET forgottoken=:token WHERE lower(nickname)=:nickname"
                : "UPDATE users SET forgottoken=:token WHERE nickname=:nickname"
            );
            $stmt->execute([':token' => $token, ':nickname' => $nickname]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp_server;
                $mail->SMTPAuth = true;
                $mail->Username = $smtp_username;
                $mail->Password = $smtp_password;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp_port;

                $mail->setFrom($from_mail, $from_name);
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = $mail_subject;

                $resetLink = htmlspecialchars((isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['SERVER_NAME']}" . dirname($_SERVER['REQUEST_URI']) . "/forgot.php?nickname=" . urlencode($nickname) . "&key=" . urlencode($token));

                $mail->Body = "{$langArray['email_change_password_body_hi']} " . htmlspecialchars($nickname) . "<br><br>" .
                              "{$langArray['email_change_password_body_link']}<br><br>" .
                              "<a href=\"$resetLink\">$resetLink</a>";

                $mail->send();
                error_log("Password reset email sent to: $email");
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
        }
    } catch (PDOException $e) {
        error_log("Email Reset Error: " . $e->getMessage());
    }
} elseif (!$pwdchanged) {
    require 'includes/header.php';
    $action = htmlspecialchars($_SERVER['PHP_SELF']);
    echo <<<HTML
<form class="srs-container" method="POST" action="$action">
    <span class="srs-header">{$langArray['forgot_password_heading']}</span>
    <div class="srs-content">
        <label for="email" class="srs-lb">{$langArray['email']}</label>
        <input name="email" id="email" class="srs-tb"><br>
    </div>
    <div class="srs-footer">
        <div class="srs-button-container">
            <input type="submit" class="submit" name="regsubmit" value="{$langArray['continue']}">
        </div>
        <div class="srs-slope"></div>
    </div>
</form><br>
HTML;
    require 'includes/footer.php';
}
?>