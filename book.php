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

$nickname = $_SESSION['nickname'];
$seat = intval(filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT));

if (isset($nickname) && !empty($nickname) && isset($seat) && !empty($seat)) {
	try {
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
		$stmt = $pdo->query("SELECT maxseats FROM config");
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$maxseats = $result["maxseats"];
		if ($seat < $maxseats) {
			$stmt = $pdo->query("SELECT id FROM users WHERE nickname='$nickname'");
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$userid = $result["id"];
			$stmt = $pdo->query("SELECT rseat FROM users WHERE nickname='$nickname'");
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if (empty($result["rseat"])) {
				$pdo->query("UPDATE users SET rseat='".$seat."' WHERE nickname='".$_SESSION['nickname']."'");
				$pdo->query("INSERT INTO reservations (taken, user_id) VALUES($seat, $userid)");
				header('Location: '.dirname($_SERVER['REQUEST_URI']));
				exit;
			}else {
				require 'includes/header.php';
				print '<span class="srs-header">'.$langArray['an_error_has_occured'].'</span>
                <div class="srs-content">
		'.$langArray['you_can_only_reserve_one_seat'].'
		</div><br><br><br>';
				require 'includes/footer.php';
			};
		}else {
			require 'includes/header.php';
			print'<span class="srs-header">'.$langArray['an_error_has_occured'].'</span>
            <div class="srs-content">
	    '.$langArray['the_seat_you_have_selected_does_not_exist'].'
            </div><br><br><br>';
			require 'includes/footer.php';
		}
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	$pdo = null;
}else {
	header("Location: ".dirname($_SERVER['REQUEST_URI']));
};
?>
