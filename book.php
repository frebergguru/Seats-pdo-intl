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
require 'includes/functions.php';
require 'includes/i18n.php';

$nickname = htmlspecialchars($_SESSION['nickname']);
$seat = intval(filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT));
$text = file_get_contents("map.txt");
$maxseats = substr_count($text, "#");

$dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];

if (isset($nickname) && !empty($nickname) && isset($seat) && !empty($seat)) {

	if ($seat <= $maxseats) {
		try {
			$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE taken = :seatid");
			$stmt->bindValue(':seatid', $seat, PDO::PARAM_INT);
			$stmt->execute();
			$checkSeat = $stmt->fetchColumn();
			
			if($checkSeat > "0") {
				require_once 'includes/header.php';
				print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
					<div class="srs-content">
			' . $langArray['the_seat_you_have_selected_is_already_reserved_by_someone_else'] . '
			</div><br><br><br>';
				require_once 'includes/footer.php';
				exit();
			}
			$pdo = null;
		} catch (PDOException $e) {
			error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
			exit();
		}

		try {
			$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
			switch (DB_DRIVER) {
				case "mysql":
					$stmt = $pdo->prepare("SELECT id FROM users WHERE nickname=:nickname");
					break;
				case "pgsql":
					$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) LIKE :nickname");
					break;
				default:
					throw new Exception("unsupported_database_driver");
			}
			$stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$userid = $result["id"];
			switch (DB_DRIVER) {
				case "mysql":
					$stmt = $pdo->prepare("SELECT rseat FROM users WHERE nickname=:nickname");
					break;
				case "pgsql":
					$stmt = $pdo->prepare("SELECT rseat FROM users WHERE lower(nickname) LIKE :nickname");
					break;
				default:
					throw new Exception("unsupported_database_driver");
			}
			$stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
			$stmt->execute();
			$pdo = null;

		} catch (PDOException $e) {
			error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
			exit();
		}
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if (empty($result["rseat"])) {
			try {
				$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
				$stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) LIKE :nickname");
				$stmt = $pdo->prepare("UPDATE users SET rseat=:rseat WHERE nickname=:nickname");
				$stmt->bindValue(":rseat", $seat, PDO::PARAM_STR);
				$stmt->bindValue(":nickname", $_SESSION['nickname'], PDO::PARAM_STR);
				$stmt->execute();
				$stmt = $pdo->prepare("INSERT INTO reservations (taken, user_id) VALUES(:staken, :suserid)");
				$stmt->bindValue(":staken", $seat, PDO::PARAM_STR);
				$stmt->bindValue(":suserid", $userid, PDO::PARAM_STR);
				$stmt->execute();
				$pdo = null;
			} catch (PDOException $e) {
				error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
				exit();
			}
			header('Location: ' . dirname($_SERVER['REQUEST_URI']));
			exit;
		} else {
			require_once 'includes/header.php';
			print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
                <div class="srs-content">
		' . $langArray['you_can_only_reserve_one_seat'] . '
		</div><br><br><br>';
			require_once 'includes/footer.php';
		}
	} else {
		require_once 'includes/header.php';
		print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
            <div class="srs-content">
	    ' . $langArray['the_seat_you_have_selected_does_not_exist'] . '
            </div><br><br><br>';
		require_once 'includes/footer.php';
	}
} else {
	header("Location: " . dirname($_SERVER['REQUEST_URI']));
}
?>