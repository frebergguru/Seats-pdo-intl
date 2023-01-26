<?php
require '../includes/config.php';
try {
	$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
	$postpassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	if (isset($postpassword)) {
		if (empty($postpassword)) {
			echo "PWDEMPTY";
		} else {
			if (!preg_match($pwd_regex, $postpassword)) {
				echo "PWDINVALIDCHAR";
			} else {
				echo "PWDSTRONG";
			}
		}
	} else {
		echo "PWDFAIL";
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
?>
