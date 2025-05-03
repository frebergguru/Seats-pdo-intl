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
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
	switch (DB_DRIVER) {
		case "mysql":
			$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = :nickname");
			break;
		case "pgsql":
			$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) LIKE :nickname");
			break;
		default:
			throw new Exception("unsupported_database_driver");
	}
	$postnickname = htmlspecialchars($_POST['nickname']);
	if (isset($postnickname)) {
		$stmt->bindValue(':nickname', mb_strtolower($postnickname), PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount()) {
			echo 'NICKEXISTS';
		} elseif (strlen($postnickname) < 4) {
			echo 'LENGTHFAIL';
		} else {
			echo "NICKOK";
		}
	}
} catch (PDOException $e) {
	error_log($langArray['error'] . ' ' . $e->getMessage());
}
?>