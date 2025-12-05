<?php
/*
Copyright 2023 Morten Freberg
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License...
*/

$home = true;
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/i18n.php';

session_start();

$useatId = isset($_GET['seatid']) ? (int)$_GET['seatid'] : null;
$seatId = 1;

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
} catch (PDOException $e) {
    error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage());
    exit();
}

require 'includes/header.php';
?>

<div class="seatmap">
    <p class="heading"><?php echo $langArray['symbol_explanation']; ?>:</p>
    <p class="seat_symbols">
        <img src="./img/wall.jpg" class="wall" alt="<?php echo $langArray['wall']; ?>"> <?php echo $langArray['wall']; ?>
        &#127869; <?php echo $langArray['kitchen']; ?>
        &#128701; <?php echo $langArray['bathroom']; ?>
        &#128682; <?php echo $langArray['door']; ?>
        <img src="./img/exit.jpg" class="exit" alt="<?php echo $langArray['exit']; ?>"> <?php echo $langArray['exit']; ?><br><br>
        <img src="./img/yellow.jpg" alt="<?php echo $langArray['selected_seat']; ?>"> <?php echo $langArray['selected_seat']; ?>
        <img src="./img/red.jpg" alt="<?php echo $langArray['occupied_seat']; ?>"> <?php echo $langArray['occupied_seat']; ?>
        <img src="./img/green.jpg" alt="<?php echo $langArray['vacant_seat']; ?>"> <?php echo $langArray['vacant_seat']; ?>
    </p>
    <hr>

    <p class="heading"><?php echo $langArray['stage_front']; ?></p>
    <?php
    $mapData = getMapData();
    $rows = $mapData['rows'];
    $takenSeats = [];
    $mapWidth = !empty($rows) ? max(array_map('strlen', $rows)) : 0;

    try {
        $stmt = $pdo->query("SELECT taken FROM reservations");
        $takenSeats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        // Flip for faster lookup: [seatId => true]
        $takenSeats = array_flip($takenSeats);
    } catch (PDOException $e) {
        error_log("Failed to fetch reservations: " . $e->getMessage());
    }
    ?>
    <div class="seat-grid" style="--cols: <?php echo $mapWidth; ?>;">
        <?php
        foreach ($rows as $row) {
            for ($i = 0; $i < strlen($row); $i++) {
                $char = $row[$i];
                switch ($char) {
                    case "#":
                        $taken = isset($takenSeats[$seatId]);

                        if ($taken) {
                            echo '<div class="map-cell seat"><a href="?seatid=' . $seatId . '"><img src="./img/red.jpg" alt="' . $langArray['occupied_seat'] . ' #' . $seatId . '"></a></div>';
                        } elseif ($useatId === $seatId) {
                            echo '<div class="map-cell seat"><img src="./img/yellow.jpg" alt="' . $langArray['selected_seat'] . ' #' . $seatId . '"></div>';
                        } else {
                            echo '<div class="map-cell seat"><a href="?seatid=' . $seatId . '"><img src="./img/green.jpg" alt="' . $langArray['vacant_seat'] . ' #' . $seatId . '"></a></div>';
                        }
                        $seatId++;
                        break;
                    case "f":
                        echo '<div class="map-cell floor" title="' . $langArray['floor'] . '"></div>';
                        break;
                    case "w":
                        echo '<div class="map-cell wall"><img src="./img/wall.jpg" class="wall" alt="' . $langArray['wall'] . '"></div>';
                        break;
                    case "k":
                        echo '<div class="map-cell kitchen" title="' . $langArray['kitchen'] . '">&#127869;</div>';
                        break;
                    case "b":
                        echo '<div class="map-cell toilet" title="' . $langArray['bathroom'] . '">&#128701;</div>';
                        break;
                    case "d":
                        echo '<div class="map-cell door" title="' . $langArray['door'] . '">&#128682;</div>';
                        break;
                    case "e":
                        echo '<div class="map-cell exit"><img src="./img/exit.jpg" class="exit" alt="' . $langArray['exit'] . '"></div>';
                        break;
                    default:
                        echo '<div class="map-cell empty"></div>';
                        break;
                }
            }
        }
        ?>
    </div>
</div>

<?php
$seatid = intval(filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT));
$occupied = null;
$nickname = '';

if ($seatid) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE taken = ?");
        $stmt->execute([$seatid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $occupied = 1;
            $user = $row['user_id'];
        }
    } catch (PDOException $e) {
        error_log("Seat lookup error: " . $e->getMessage());
    }

    if ($occupied) {
        try {
            $stmt = $pdo->prepare("SELECT nickname FROM " . USERS_TABLE . " WHERE id = ?");
            $stmt->execute([$user]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $nickname = $userRow['nickname'] ?? '';
        } catch (PDOException $e) {
            error_log("User lookup error: " . $e->getMessage());
        }
    }

    echo '<br><div class="seat_registered">';
    if ($occupied) {
        echo $langArray['seat_number'] . ' ' . $seatid . ' ' . $langArray['is_reserved_by'] . ' ' . htmlspecialchars($nickname) . '</div>';
    } else {
        if (!isset($_SESSION['nickname'])) {
            echo $langArray['login_before_reserving'] . '</div>';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT " . RSEAT_TABLE . " FROM " . USERS_TABLE . " WHERE nickname = ?");
                $stmt->execute([$_SESSION['nickname']]);
                $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $rseat = $userRow[RSEAT_TABLE] ?? 0;

                if (empty($rseat)) {
                    echo $langArray['do_you_want_to_reserve_seat_number'] . ' ' . $seatid .
                        '? <a href="book.php?seatid=' . $seatid . '">' . $langArray['yes'] . '</a></div>';
                } else {
                    echo '<strong>' . $langArray['error'] . ': ' . $langArray['you_can_only_reserve_one_seat'] . '</strong><br><br>' .
                        $langArray['you_have_reserved_seat_number'] . ' ' . $rseat . '.</div>';
                }
            } catch (PDOException $e) {
                error_log("Reservation check error: " . $e->getMessage());
            }
        }
    }
}
?>

<?php require 'includes/footer.php'; ?>
