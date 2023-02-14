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
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/i18n.php';
if (isset($_GET['seatid'])) {
	$useatId = $_GET['seatid'];
} else {
	$useatId = null;
}
$seatId = 1;
try {
	$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
} catch (PDOException $e) {
	error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
	exit();
}
require 'includes/header.php';
?>
<div class="seatmap">
	<p class="heading">
		<?php echo $langArray['symbol_explanation']; ?>:
	</p>
	<p class="seat_symbols">
	<img src="./img/wall.jpg" class="wall" title="<?php echo $langArray['wall'] ?>" alt="<?php echo $langArray['wall'] ?>"> <?php echo $langArray['wall']; ?>
		&#127869; <?php echo $langArray['kitchen']; ?> &#128701;
		<?php echo $langArray['bathroom']; ?> &#128682;
		<?php echo $langArray['door']; ?> <img src="./img/exit.jpg" class="exit"
			title="<?php echo $langArray['exit']; ?>" alt="<?php echo $langArray['exit']; ?>"> <?php echo $langArray['exit']; ?><br><br>
		<img src="./img/yellow.jpg" alt="<?php echo $langArray['selected_seat']; ?>"> <?php echo $langArray['selected_seat']; ?> <img src="./img/red.jpg" alt="<?php echo $langArray['occupied_seat']; ?>"> <?php echo $langArray['occupied_seat']; ?> <img src="./img/green.jpg" alt="<?php echo $langArray['vacant_seat']; ?>">
		<?php echo $langArray['vacant_seat']; ?>
	</p>
	<hr>
	<?php
	echo '<p class="heading">' . $langArray['stage_front'] . '</p><table id="seatMap">';
	$data = file_get_contents("map.txt");
	$maxSeats = substr_count($data, 's');
	$seatmaprows = explode("\n", $data);
	$num_columns = 0;
	foreach ($seatmaprows as $seatmaprow) {
		echo "<tr>";
		$columns = 0;
		for ($i = 0; $i < strlen($seatmaprow); $i++) {
			switch ($seatmaprow[$i]) {
				case "#":
					try {
						$stmt = $pdo->prepare("SELECT * FROM reservations WHERE taken = :seat");
						$stmt->bindValue(":seat", $seatId);
						$stmt->execute();
						$dbrow = $stmt->fetch(PDO::FETCH_ASSOC);
						if (is_array($dbrow) && $dbrow["taken"] == $seatId) {
							echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/red.jpg" title="' . $langArray['occupied_seat'] . ' #' . $seatId . '" alt="' . $langArray['occupied_seat'] . ' #' . $seatId . '"></a></td>';
						} elseif ($useatId == $seatId) {
							echo '<td class="seat"><img src="./img/yellow.jpg" title="' . $langArray['selected_seat'] . ' #' . $seatId . '" alt="' . $langArray['selected_seat'] . ' #' . $seatId . '"></td>';
						} else {
							echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/green.jpg" title="' . $langArray['vacant_seat'] . ' #' . $seatId . '" alt="' . $langArray['vacant_seat'] . ' #' . $seatId . '"></a></td>';
						}
						$seatId++;
					} catch (PDOException $e) {
						echo "could not connect to db server " . $e->getMessage();
						exit();
					}
					$columns++;
					$has_td = true;
					break;
				case "f":
					echo '<td class="floor" title="' . $langArray['floor'] . '"></td>';
					$columns++;
					$has_td = true;
					break;
				case "w":
					echo '<td class="wall" title="' . $langArray['wall'] . '"><img src="./img/wall.jpg" class="wall" title="' . $langArray['wall'] . '" alt="' . $langArray['wall'] . '"></td>';
					$columns++;
					$has_td = true;
					break;
				case "k":
					echo '<td class="kitchen" title="' . $langArray['kitchen'] . '">&#127869;</td>';
					$columns++;
					$has_td = true;
					break;
				case "b":
					echo '<td class="toilet" title="' . $langArray['bathroom'] . '">&#128701;</td>';
					$columns++;
					$has_td = true;
					break;
				case "d":
					echo '<td class="door" title="' . $langArray['door'] . '">&#128682;</td>';
					$columns++;
					$has_td = true;
					break;
				case "e":
					echo '<td class="exit" title="' . $langArray['exit'] . '"><img src="./img/exit.jpg" class="exit" title="' . $langArray['exit'] . '" alt="' . $langArray['exit'] . '"></td>';
					$columns++;
					$has_td = true;
					break;
			}
		}
		if ($num_columns == 0) {
			$num_columns = $columns;
		} else {
			for ($i = $columns; $i < $num_columns; $i++) {
				echo '<td></td>';
			}
		}
		echo "</tr>";
		$has_td = false;
	}
	?>
	</table>
</div>
<?php
$seatid = intval(filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT));
if (isset($seatid)) {
	try {
		$stmt = $pdo->prepare("SELECT * FROM reservations WHERE taken = :seatid");
		$stmt->bindValue(':seatid', $seatid, PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			if ($row["taken"] == $seatid) {
				$occupied = 1;
			}
		}
	} catch (PDOException $e) {
		error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
		exit();
	}
}
$stmt->closeCursor();

if (isset($seatid)) {
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
	$occupied = null;
}
if ($occupied == "1") {
	try {
		$stmt = $pdo->prepare("SELECT * FROM " . USERS_TABLE . " WHERE id = :user");
		$stmt->bindValue(':user', $user, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$nickname = $row["nickname"];
	} catch (PDOException $e) {
		error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
		exit();
	}
	$stmt->closeCursor();
}
try {
	$stmt = $pdo->prepare("SELECT * FROM " . USERS_TABLE . " WHERE nickname = :nickname");

	if (!empty($seatid)) {
		print '<br><div class="seat_registered">';
		if (!empty($occupied)) {
			print $langArray['seat_number'] . ' ' . $seatid . ' ' . $langArray['is_reserved_by'] . ' ' . $nickname . '</div>';
		} else {
			if (!isset($_SESSION['nickname'])) {
				echo $langArray['login_before_reserving'] . '</div>';
			} else {
				$stmt->bindValue(':nickname', $_SESSION['nickname'], PDO::PARAM_STR);
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$rseat = $row[RSEAT_TABLE];
				if (empty($rseat) or $rseat == 0) {
					print $langArray['do_you_want_to_reserve_seat_number'] . ' ' . $seatid . '? <a href="book.php?seatid=' . $seatid . '">' . $langArray['yes'] . '</a></div>';
				} else {
					print '<strong>' . $langArray['error'] . ': ' . $langArray['you_can_only_reserve_one_seat'] . '</strong><br><br>
' . $langArray['you_have_reserved_seat_number'] . ' ' . $rseat . '.</div>';
				}
				;
			}
		}
	}
} catch (PDOException $e) {
	error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . $langArray['whole_query'] . ' ' . $stmt->queryString, 0);
	exit();
}
require 'includes/footer.php';
?>
