<?php
//Generate seat map
function seats($maxseats, $seat_width, $seat_height, $width) {
	$seatid  = filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT);
	require 'config.php';
	try {
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE taken = :seat");
		for ($i = 1; $i <= $maxseats; $i++) {
			$stmt->execute(['seat' => $i]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if (is_array($row) && $row["taken"] == $i) {
				echo '<a href="?seatid='.$i.'"><img src="./img/red.jpg" width="'.$seat_width.'" height="'.$seat_height.'" alt="Sete nummer: '.$i.' - Opptatt sete"></a> ';
			}elseif ($seatid == $i) {
				echo '<img src="./img/yellow.jpg" width="'.$seat_width.'" height="'.$seat_height.'" alt="Sete nummer: '.$i.' - Valgt sete"> ';
			} else {
				echo '<a href="?seatid='.$i.'"><img src="./img/green.jpg" width="'.$seat_width.'" height="'.$seat_height.'" alt="Sete nummer: '.$i.' - Ledig sete"></a> ';
			};
			if (!isset($width2)) {$width2='';};
			$width2 = intval($width2) + 1;
			if ($width2 == $width) {
				$width2 = 0;
				echo "<br>\n";
			};
		}
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	$pdo = null;
}

//Check if a seat is occupied or not
function checkOccupiedSeat($rows) {
	foreach ($rows as $row) {
		if ($row["taken"] == $seatid) {
			$occupied = 1;
			$user = $row["user_id"];
		}
	}
}
