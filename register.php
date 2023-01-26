<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

$register_page = true;
require 'includes/header.php';
if (!isset($_SESSION['csrf_token'])) {
	$csrf_token = bin2hex(random_bytes(32));
	$_SESSION['csrf_token'] = $csrf_token;
}
try {
	$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$regsubmit = filter_input(INPUT_POST, 'regsubmit', FILTER_SANITIZE_STRING);
	$fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
	$nickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
	$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	$password2 = filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_STRING);
	$form_csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

	if (isset($regsubmit) && !empty($regsubmit)) {
		if (isset($password) && !empty($password) && !preg_match_all($pwd_regex, $password)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_password_contains_illegal_characters'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($password2) && !empty($password2) && $password !== $password2) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_passwords_dosent_match'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($password) && empty($password)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['you_must_enter_a_password'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($password2) && empty($password2)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['you_must_enter_a_confirmation_password'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($fullname) && empty($fullname)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['you_must_enter_a_name'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (!preg_match_all($fullname_regex, $fullname)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['fullname_contains_illegal_characters'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($email) && !empty($email)) {
			$stmt = $pdo->prepare("SELECT id FROM users WHERE email=:email");
			$stmt->bindValue(':email', $email);
			$stmt->execute();
			if ($stmt->rowCount()) {
				echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_email_address_already_exists'].'</div><br><br>';
				$formstatus = 'FEIL';
			}
		}
		if (isset($email) && empty($email)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['you_must_enter_a_valid_email_address'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($nickname) && !empty($nickname) && !preg_match_all($nickname_regex, $nickname)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_nickname_is_invalid'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		if (isset($nickname) && !empty($nickname)) {
			$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname=:nickname");
			$stmt->bindValue(':nickname', $nickname);
			$stmt->execute();
			if ($stmt->rowCount()) {
				echo '<div class="regerror">'.$langArray['error'].': '.$langArray['nickname_already_exists'].'.</div><br><br>';
				$formstatus = 'FEIL';
			}
		}
		if (isset($nickname) && empty($nickname)) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['you_must_enter_a_nickname'].'</div><br><br>';
			$formstatus = 'FEIL';
		}
		// Check if the form submission's CSRF token matches the one in the session
		if ($form_csrf_token !== $_SESSION['csrf_token']) {
			echo '<div class="regerror">'.$langArray['error'].': '.$langArray['invalid_csfr_token'].'</div><br><br>';
			$formstatus = 'FEIL';
		}else if ($formstatus !== 'FEIL') {
			// If the CSRF token is valid, process the form submission
			$options = [
				'memory_cost' => 1<<17,
				'time_cost' => 4,
				'threads' => 3,
			];
			$password = password_hash($password, PASSWORD_ARGON2ID, $options);
			$stmt = $pdo->prepare("INSERT INTO users (fullname, nickname, email, password) VALUES (:fullname, :nickname, :email, :password)");
			$stmt->execute(['fullname' => $fullname, 'nickname' => $nickname, 'email' => $email, 'password' => $password]);
			echo '<span class="srs-header">'.$langArray['user_was_created'].'!</span>
<div class="srs-content">
'.$langArray['you_can_now_login_and_reserve_a_seat'].'
</div><br><br>';
			$formstatus = True;
		}
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
if ($formstatus !== True) {
	print'<form class="srs-container" method="POST" action="'.$_SERVER["PHP_SELF"].'">
        <span class="srs-header">'.$langArray['new_user'].'</span>

        <div class="srs-content">
            <label for="fullname" class="srs-lb">'.$langArray['fullname'].'</label><input name="fullname" value="'.$fullname.'" id="fullname" class="srs-tb"><br>
            <span id="statusfullname"></span><br>
            <label for="nickname" class="srs-lb">'.$langArray['nickname'].'</label><input name="nickname" value="'.$nickname.'" id="nickname" class="srs-tb"><br>
            <span id="status"></span><br>
            <label for="email" class="srs-lb">'.$langArray['email'].'</label><input name="email" value="'.$email.'" id="email" class="srs-tb"><br>
            <span id="statusemail"></span><br>
            <label for="password" class="srs-lb">'.$langArray['password'].'</label><input name="password" id="password" type="password" class="srs-tb"><br>
            <span id="pwstatus"></span><br>
            <label for="password2" class="srs-lb">'.$langArray['repeat_password'].'</label><input name="password2" id="password2" type="password" class="srs-tb"><br>
            <span id="pwstatus2"></span><br>
	    <input type="hidden" name="csrf_token" value="' . $_SESSION["csrf_token"] . '">
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
		<input type="submit" class="submit" name="regsubmit" value="'.$langArray['register_button'].'">
            </div>
            <div class="srs-slope"></div>
        </div>
    </form>
    <script src="./js/formcheck.js"></script>
    <script src="./js/pwdcheck.js"></script>';
};
?>
<br>
<?php
require 'includes/footer.php';
?>
