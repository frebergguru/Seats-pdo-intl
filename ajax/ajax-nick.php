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
