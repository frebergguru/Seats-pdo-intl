<?php
include '../includes/config.php';
try {
	$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
	$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
	if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
		echo 'EMAILFAIL';
		exit();
	}
	$postemail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
	if (isset($postemail)) {
		$stmt->execute(['email' => $postemail]);
		if ($stmt->rowCount()) {
			echo 'EMAILINUSE';
		} else {
			echo "EMAILOK";
		}
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
?>
