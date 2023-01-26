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
	$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = :nickname");
	$postnickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);
	if (isset($postnickname)) {
		$stmt->execute(['nickname' => $postnickname]);
		if ($stmt->rowCount()) {
			echo 'NICKEXISTS';
		} elseif (strlen($postnickname) < 4) {
			echo 'LENGTHFAIL';
		} else {
			echo "NICKOK";
		}
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}
?>
