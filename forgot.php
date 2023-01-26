<?php
declare(strict_types=1);

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);

$nickname = filter_input(INPUT_GET, 'nickname', FILTER_SANITIZE_STRING);
$key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
$password2 = filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_STRING);

if (isset($password) && !empty($password) && !preg_match_all($pwd_regex, $password)) {
	echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_password_contains_illegal_characters'].'</div><br><br>';
	$formstatus = 'FEIL';
}

if (isset($password2) && !empty($password2) && $password !== $password2) {
	echo '<div class="regerror">'.$langArray['error'].': '.$langArray['the_password_dosent_match'].'</div><br><br>';
	$formstatus = 'FEIL';
}

if (isset($password) && !empty($password) && isset($password2) && !empty($password2) && isset($key) && !empty($key) && $formstatus != "FEIL") {
	require 'includes/header.php';
	print '<span class="srs-header">'.$langArray['new_password'].'</span>
<div class="srs-content">
'.$langArray['password_changed_log_in'].'.
</div><br><br><br>';
	require 'includes/footer.php';

	$options = [
		'memory_cost' => 1<<17,
		'time_cost' => 4,
		'threads' => 3,

	];
	$pwdhash = password_hash($password, PASSWORD_ARGON2ID, $options);

	$stmt = $pdo->prepare("UPDATE users SET password = :password, forgottoken = NULL WHERE nickname = :nickname");
	$stmt->execute([':password' => $pwdhash, ':nickname' => $nickname]);

	$pwdchanged = true;
}

if (isset($nickname) && !empty($nickname) && isset($key) && !empty($key) && $pwdchanged != true) {
	$stmt = $pdo->prepare("SELECT forgottoken FROM users WHERE nickname = :nickname");
	$stmt->execute([':nickname' => $nickname]);
	$sqlresults = $stmt->fetch(PDO::FETCH_ASSOC);
	$forgottoken = $sqlresults["forgottoken"];

	if ($key == $forgottoken) {
		require 'includes/header.php';
		print '<form class="srs-container" method="POST" action="' . $_SERVER["PHP_SELF"] . '?nickname=' . $nickname . '&key=' . $forgottoken . '">
<span class="srs-header">'.$langArray['new_password'].'</span>

<div class="srs-content">
    <label for="password" class="srs-lb">'.$langArray['password'].'</label><input name="password" id="password" type="password" class="srs-tb"><br>
    <span id="pwstatus"></span><br>
    <label for="password2" class="srs-lb">'.$langArray['repeat_password'].'</label><input name="password2" id="password2" type="password" class="srs-tb"><br>
</div>
<div class="srs-footer">
	<div class="srs-button-container">
<input type="submit" value="'.$langArray['change_password_button'].'" class="srs-btn">
</div>
<div class="srs-slope"></div>
</div>
</form><br><br>
<script src="./js/pwdcheck.js"></script><br>';
		require 'includes/footer.php';
	}else {
		require 'includes/header.php';
		print '<span class="srs-header">'.$langArray['lost_password'].' - '.$langArray['error'].'</span>
<div class="srs-content">
'.$langArray['wrong_nickname_or_verification_key'].'
</div><br><br><br>';
		require 'includes/footer.php';
		exit();
	};
}elseif (!empty($email)) {
	require 'includes/header.php';
	print '<span class="srs-header">'.$langArray['new_password'].' - '.$langArray['email'].'</span>
<div class="srs-content">
'.$langArray['email_sent_instruction_page_text'].'
</div><br><br><br>';
	require 'includes/footer.php';
	$stmt = $pdo->prepare("SELECT nickname FROM users WHERE email=:email");
	$stmt->execute(['email' => $email]);
	$sqlresults = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($stmt->rowCount() === 1) {
		$nickname = $sqlresults['nickname'];
		$randomkey = bin2hex(random_bytes(32));
		$stmt = $pdo->prepare("UPDATE users SET forgottoken=:randomkey WHERE nickname=:nickname");
		$stmt->execute(['randomkey' => $randomkey, 'nickname' => $nickname]);
		$mailheaders = 'From: '.$from_name.' <'.$from_mail.'>'."\r\n".
			'X-Mailer: Seat Reservation/2.0';
		$mailmsg = $langArray['email_change_password_body']."\n\n https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?nickname=".$nickname."&key=".$randomkey;
		mail($email, $mail_subject, $mailmsg, $mailheaders);
	}
}else {
	if ($pwdchanged != true) {
		require 'includes/header.php';
		print '<form class="srs-container" method="POST" action="'.$_SERVER["PHP_SELF"].'">
<span class="srs-header">'.$langArray['lost_password'].'</span>
<div class="srs-content">
	<label for="email" class="srs-lb">'.$langArray['email'].'</label><input name="email" value="" id="email" class="srs-tb"><br>
</div>
<div class="srs-footer">
	<div class="srs-button-container">
		<input type="submit" class="submit" name="regsubmit" value="'.$langArray['continue'].'">
	</div>
	<div class="srs-slope"></div>
</div>
</form><br>';
		require 'includes/footer.php';
	};
};
?>
