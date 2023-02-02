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

include '../includes/config.php';
$email = mb_strtolower(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
if (!$email) {
	echo 'EMAILFAIL';
	exit();
}
try {
	$dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
	switch (DB_DRIVER) {
		case "mysql":
			$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
			break;
		case "pgsql":
			$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(email) = :email");
			break;
		default:
			throw new Exception("unsupported_database_driver");
	}
	$stmt->bindValue(':email', $email, PDO::PARAM_STR);
	$stmt->execute();
	if ($stmt->rowCount()) {
		echo 'EMAILINUSE';
	} else {
		echo "EMAILOK";
	}
} catch (PDOException $e) {
	error_log($langArray['error'] . ' ' . $e->getMessage());
}
?>