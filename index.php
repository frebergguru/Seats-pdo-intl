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
$home = true;
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';
try {
	$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
	error_log("Kunne ikke koble til database serveren: " . $e->getMessage(), 0);
	exit();
}
require 'includes/header.php';
?>
    <!-- SEATMAP START -->
    <div class="seatmap">
    <p class="heading"><?php echo $langArray['symbol_explanation']; ?>:</p>
    <p class="seat_symbols"><img src="./img/yellow.jpg" height="15" alt="<?php echo $langArray['selected_seat']; ?>"> <?php echo $langArray['selected_seat']; ?> <img src="./img/red.jpg" height="15" alt="<?php echo $langArray['occupied_seat']; ?>"> <?php echo $langArray['occupied_seat']; ?> <img src="./img/green.jpg" height="15" alt="<?php echo $langArray['vacant_seat']; ?>"> <?php echo $langArray['vacant_seat']; ?></p>
    <hr>
<?php
try {
	$stmt = $pdo->prepare("SELECT * FROM `" . CONFIG_TABLE . "`");
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$maxseats = $row["maxseats"];
	$seat_width = $row["seat_width"];
	$seat_height = $row["seat_height"];
	$width = $row["width"];
	$seatmapwidth = $width * $seat_width + 40;
	echo '<p class="heading">'.$langArray['stage_front'].'</p>';
	seats($maxseats, $seat_width, $seat_height, $width);
	echo '</div>';
}catch (PDOException $e) {
	error_log("Invalid query: " . $e->getMessage() . "\nWhole query: " . $stmt->queryString, 0);
	exit();
}
$stmt->closeCursor();
?>

    <!-- SEATMAP END -->
    <!-- SEAT TAKEN? START -->
<?php
$seatid = intval(filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT));
if (isset($seatid)) {
	try {
		$stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE taken = :seatid");
		$stmt->bindParam(':seatid', $seatid, PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		checkOccupiedSeat($rows);
	} catch (PDOException $e) {
		error_log("Invalid query: " . $e->getMessage() . "\nWhole query: " . $stmt->queryString, 0);
		exit();
	}
};

$stmt->closeCursor();

if(isset($seatid)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE taken = ?");
        $stmt->execute([$seatid]);
        $result = $stmt->fetchAll();
        foreach ($result as $row) {
            if ($row["taken"] == $seatid) {
                $occupied = 1;
                $user = $row["user_id"];
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (!isset($occupied)) {
	$occupied = '';
}
if ($occupied == "1") {
	try {
		$stmt = $pdo->prepare("SELECT * FROM " . USERS_TABLE . " WHERE id = :user");
		$stmt->bindParam(':user', $user, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$nickname = $row["nickname"];
	} catch (PDOException $e) {
		error_log("Invalid query: " . $e->getMessage() . "\nWhole query: " . $stmt->queryString, 0);
		exit();
	}
	$stmt->closeCursor();
}
try {
	// Use a single prepared statement for multiple queries
	$stmt = $pdo->prepare("SELECT * FROM " . USERS_TABLE . " WHERE nickname = :nickname");

	if (!empty($seatid)) {
		print '<br><div class="seat_registered">';
		if (!empty($occupied)) {
			print $langArray['seat_number'].' '.$seatid.' '.$langArray['is_reserved_by'].' '.$nickname.'</div>';
		} else {
			if (!isset($_SESSION['nickname'])) {
				echo $langArray['login_before_reserving'].'</div>';
			} else {
				$stmt->bindParam(':nickname', $_SESSION['nickname']);
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$rseat = $row[RSEAT_TABLE];
				if (empty($rseat) or $rseat == 0) {
					print $langArray['do_you_want_to_reserve_seat_number'].' '.$seatid.'? <a href="book.php?seatid='.$seatid.'">'.$langArray['yes'].'</a></div>';
				}else {
					print '<strong>'.$langArray['error'].': '.$langArray['you_can_only_reserve_one_seat'].'</strong><br><br>
'.$langArray['you_have_reserved_seat_number'].' '.$rseat.'.</div>';
				};
			}
		}
	}
} catch (PDOException $e) {
	// Use error reporting functions
	error_log("Invalid query: " . $e->getMessage() . "\nWhole query: " . $stmt->queryString, 0);
	exit();
}
?>
    <!-- SEAT TAKEN? END -->
<?php
require 'includes/footer.php';
?>
