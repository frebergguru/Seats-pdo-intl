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

require 'includes/config.php';
require 'includes/i18n.php';

$register_page = true;
require 'includes/header.php';
if (!isset($_SESSION['csrf_token'])) {
	$csrf_token = bin2hex(random_bytes(32));
	$_SESSION['csrf_token'] = $csrf_token;
}
try {
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
	if (!empty($_POST['regsubmit'])) {
		$regsubmit = htmlspecialchars($_POST['regsubmit']);
	}
	if (!empty($_POST['fullname'])) {
		$fullname = htmlspecialchars($_POST['fullname']);
	}
	if (!empty($_POST['nickname'])) {
		$nickname = htmlspecialchars($_POST['nickname']);
	}
	if (!empty($_POST['email'])) {
		$email = htmlspecialchars(mb_strtolower($_POST['email']));
	}
	if (!empty($_POST['password'])) {
		$password = htmlspecialchars($_POST['password']);
	}
	if (!empty($_POST['password2'])) {
		$password2 = htmlspecialchars($_POST['password2']);
	}
	if (!empty($_POST['csrf_token'])) {
		$form_csrf_token = htmlspecialchars($_POST['csrf_token']);
	}

	if (isset($regsubmit) && !empty($regsubmit)) {
		if (isset($password) && !empty($password) && !preg_match_all($pwd_regex, $password)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_contains_illegal_characters'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($password2) && !empty($password2) && $password !== $password2) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_passwords_dosent_match'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($password) && empty($password)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_password'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($password2) && empty($password2)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_confirmation_password'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($fullname) && empty($fullname)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_name'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (!empty($fullname) && !preg_match_all($fullname_regex, $fullname)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['fullname_contains_illegal_characters'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($email) && !empty($email)) {
			if (DB_DRIVER == "mysql") {
				$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
			} elseif (DB_DRIVER == "pgsql") {
				$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(email) LIKE :email");
			} else {
				throw new Exception("unsupported_database_driver");
			}
			$stmt->bindValue(':email', $email, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount()) {
				echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_email_address_already_exists'] . '</div><br><br>';
				$formstatus = 'FAIL';
			}
		}
		if (isset($email) && empty($email)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_valid_email_address'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($nickname) && !empty($nickname) && !preg_match_all($nickname_regex, $nickname)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_nickname_is_invalid'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		if (isset($nickname) && !empty($nickname)) {
			if (DB_DRIVER == "mysql") {
				$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = :nickname");
			} elseif (DB_DRIVER == "pgsql") {
				$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) LIKE :nickname");
			} else {
				throw new Exception("unsupported_database_driver");
			}
			$stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount()) {
				echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['nickname_already_exists'] . '.</div><br><br>';
				$formstatus = 'FAIL';
			}
		}
		if (isset($nickname) && empty($nickname)) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_nickname'] . '</div><br><br>';
			$formstatus = 'FAIL';
		}
		// Check if the form submission's CSRF token matches the one in the session
		if ($form_csrf_token !== $_SESSION['csrf_token']) {
			echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['invalid_csfr_token'] . '</div><br><br>';
			$formstatus = 'FAIL';
		} else if ($formstatus !== 'FAIL') {
			// If the CSRF token is valid, process the form submission
			$options = [
				'memory_cost' => 1 << 14,
				'time_cost' => 2,
				'threads' => 2,
			];
			$password = password_hash($password, PASSWORD_ARGON2ID, $options);
			$stmt = $pdo->prepare("INSERT INTO users (fullname, nickname, email, password) VALUES (:fullname, :nickname, :email, :password)");
			$stmt->execute(['fullname' => $fullname, 'nickname' => $nickname, 'email' => $email, 'password' => $password]);
			echo '<span class="srs-header">' . $langArray['user_was_created'] . '!</span>
<div class="srs-content">
' . $langArray['you_can_now_login_and_reserve_a_seat'] . '
</div><br><br>';
			$formstatus = True;
		}
	}
} catch (PDOException $e) {
	error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
}
if ($formstatus !== True) {
	print '<form class="srs-container" method="POST" action="' . $_SERVER["PHP_SELF"] . '">
        <span class="srs-header">' . $langArray['new_user'] . '</span>

        <div class="srs-content">
            <label for="fullname" class="srs-lb">' . $langArray['fullname'] . '</label><input name="fullname" value="' . $fullname . '" id="fullname" class="srs-tb"><br>
            <span id="statusfullname"></span><br>
            <label for="nickname" class="srs-lb">' . $langArray['nickname'] . '</label><input name="nickname" value="' . $nickname . '" id="nickname" class="srs-tb"><br>
            <span id="status"></span><br>
            <label for="email" class="srs-lb">' . $langArray['email'] . '</label><input name="email" value="' . $email . '" id="email" class="srs-tb"><br>
            <span id="statusemail"></span><br>
            <label for="password" class="srs-lb">' . $langArray['password'] . '</label><input name="password" id="password" type="password" class="srs-tb"><br>
            <span id="pwstatus"></span><br>
            <label for="password2" class="srs-lb">' . $langArray['repeat_password'] . '</label><input name="password2" id="password2" type="password" class="srs-tb"><br>
            <span id="pwstatus2"></span><br>
	    <input type="hidden" name="csrf_token" value="' . $_SESSION["csrf_token"] . '">
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
		<input type="submit" class="submit" name="regsubmit" value="' . $langArray['register_button'] . '">
            </div>
            <div class="srs-slope"></div>
        </div>
    </form>
    <script src="./js/formcheck.js"></script>
    <script src="./js/pwdcheck.js"></script>';
}
?>
<br>
<?php
require 'includes/footer.php';
?>