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

#declare(strict_types=1);

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

if (!empty($_GET['nickname'])) {
	$nickname = htmlspecialchars(mb_strtolower($_GET['nickname']));
}
if (!empty($_GET['key'])) {
	$key = htmlspecialchars($_GET['key']);
}
if (!empty($_POST['email'])) {
	$email = htmlspecialchars($_POST['email']);
}
if (!empty($_POST['password'])) {
	$password = htmlspecialchars($_POST['password']);
}
if (!empty($_POST['password2'])) {
	$password2 = htmlspecialchars($_POST['password2']);
}

if (isset($password) && !empty($password) && !preg_match_all($pwd_regex, $password)) {
	echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_contains_illegal_characters'] . '</div><br><br>';
	$formstatus = 'FAIL';
}

if (isset($password2) && !empty($password2) && $password !== $password2) {
	echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_dosent_match'] . '</div><br><br>';
	$formstatus = 'FAIL';
}

if (isset($password) && !empty($password) && isset($password2) && !empty($password2) && isset($key) && !empty($key) && $formstatus != "FAIL") {
	require 'includes/header.php';
	echo '<span class="srs-header">' . $langArray['new_password'] . '</span>
<div class="srs-content">
' . $langArray['password_changed_log_in'] . '.
</div>
</div><br><br><br>';
	require 'includes/footer.php';

	$pwdhash = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
	try {
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
		switch (DB_DRIVER) {
			case "mysql":
				$stmt = $pdo->prepare("UPDATE users SET password = :password, forgottoken = NULL WHERE nickname = :nickname");
				break;
			case "pgsql":
				$stmt = $pdo->prepare("UPDATE users SET password = :password, forgottoken = NULL WHERE lower(nickname) = :nickname");
				break;
			default:
				throw new Exception("unsupported_database_driver");
		}
		$stmt->bindValue(":password", $pwdhash);
		$stmt->bindValue(":nickname", mb_strtolower($nickname));
		$stmt->execute();
		$pdo = null;
	} catch (PDOException $e) {
		error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
	}
	$pwdchanged = true;
}

// deepcode ignore PhpSameEvalBinaryExpressionfalse: <please specify a reason of ignoring this>
if (isset($nickname) && !empty($nickname) && isset($key) && !empty($key) && $pwdchanged != true) {
	try {
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
		switch (DB_DRIVER) {
			case "mysql":
				$stmt = $pdo->prepare("SELECT forgottoken FROM users WHERE nickname = :nickname");
				break;
			case "pgsql":
				$stmt = $pdo->prepare("SELECT forgottoken FROM users WHERE lower(nickname) = :nickname");
				break;
		}
		$stmt->bindValue(":nickname", $nickname);
		$stmt->execute();
		$pdo = null;
	} catch (PDOException $e) {
		error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
	}
	$sqlresults = $stmt->fetch(PDO::FETCH_ASSOC);
	$forgottoken = $sqlresults["forgottoken"];

	if (mb_strtolower($key) === mb_strtolower($forgottoken)) {
		require 'includes/header.php';
		print '<form class="srs-container" method="POST" action="' . $_SERVER["PHP_SELF"] . '?nickname=' . $nickname . '&key=' . $forgottoken . '">
<span class="srs-header">' . $langArray['new_password'] . '</span>

<div class="srs-content">
	<a href="#" id="passwordRequirements">' . $langArray['password_requirements'] . '</a><br>
	<div class="bubble-container">
			<div class="bubble" id="bubblePopup">
			' . $langArray['password_requirements_text'] . '
			<button id="closePopup">' . $langArray['close_btn'] . '</button>
			</div>
    <label for="password" class="srs-lb">' . $langArray['password'] . '</label><input name="password" id="password" type="password" class="srs-tb"><br>
    <span id="pwstatus"></span><br>
	</div>
    <label for="password2" class="srs-lb">' . $langArray['repeat_password'] . '</label><input name="password2" id="password2" type="password" class="srs-tb"><br>
</div>
<div class="srs-footer">
	<div class="srs-button-container">
<input type="submit" value="' . $langArray['change_password_button'] . '" class="srs-btn">
</div>
<div class="srs-slope"></div>
</div>
</form>
<br><br>
<script src="./js/pwdreq.js"></script>
<script src="./js/pwdcheck.js"></script>';
		require 'includes/footer.php';
	} else {
		require 'includes/header.php';
		print '<span class="srs-header">' . $langArray['forgot_password_heading'] . ' - ' . $langArray['error'] . '</span>
<div class="srs-content">
' . $langArray['wrong_nickname_or_verification_key'] . '
</div><br><br><br>';
		require 'includes/footer.php';
		exit();
	}
	;
} elseif (!empty($email)) {
	require 'includes/header.php';
	print '<span class="srs-header">' . $langArray['new_password'] . ' - ' . $langArray['email'] . '</span>
<div class="srs-content">
' . $langArray['email_sent_instruction_page_text'] . '
</div><br><br><br>';
	require 'includes/footer.php';
	try {
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
		$stmt = $pdo->prepare("SELECT nickname FROM users WHERE email=:email");
		$stmt->bindValue(":email", $email);
		$stmt->execute();
		$sqlresults = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($stmt->rowCount() === 1) {
			$nickname = mb_strtolower($sqlresults['nickname']);
			$randomkey = genRandomKey();
			$pdo = null;

			$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
			switch (DB_DRIVER) {
				case "mysql":
					$stmt = $pdo->prepare("UPDATE users SET forgottoken=:randomkey WHERE nickname=:nickname");
					break;
				case "pgsql":
					$stmt = $pdo->prepare("UPDATE users SET forgottoken=:randomkey WHERE lower(nickname) = :nickname");
					break;
				default:
					throw new Exception("unsupported_database_driver");
			}
			$stmt->bindValue(":randomkey", $randomkey);
			$stmt->bindValue(":nickname", $nickname);
			$stmt->execute();
			$pdo = null;
			$from_name = str_replace(["\r", "\n"], '', filter_var($from_name, FILTER_SANITIZE_STRING));
			$from_mail = str_replace(["\r", "\n"], '', filter_var($from_mail, FILTER_VALIDATE_EMAIL));
			if (!$from_mail) {
				exit('Invalid sender email address');
			}
			$mailheaders = "From: {$from_name} <{$from_mail}>\r\n";
			$mailheaders .= "X-Mailer: Seat Reservation/2.0";
			$linkPath = '/forgot.php';
			$baseUrl = 'https://' . $_SERVER['SERVER_NAME'] . $linkPath;
			$resetLink = $baseUrl . '?nickname=' . urlencode($nickname) . '&key=' . urlencode($randomkey);
			$mailmsg = $langArray['email_change_password_body_hi'] . " " . htmlspecialchars($nickname) . "\n\n" .
				$langArray['email_change_password_body_link'] . "\n\n" .
				$resetLink;
			mail($email, $mail_subject, $mailmsg, $mailheaders);
		}
	} catch (PDOException $e) {
		error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
	}
} else {
	if ($pwdchanged != true) {
		require 'includes/header.php';
		print '<form class="srs-container" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]); . '">
<span class="srs-header">' . $langArray['forgot_password_heading'] . '</span>
<div class="srs-content">
	<label for="email" class="srs-lb">' . $langArray['email'] . '</label><input name="email" value="" id="email" class="srs-tb"><br>
</div>
<div class="srs-footer">
	<div class="srs-button-container">
		<input type="submit" class="submit" name="regsubmit" value="' . $langArray['continue'] . '">
	</div>
	<div class="srs-slope"></div>
</div>
</form><br>';
		require 'includes/footer.php';
	}
	;
}
?>
