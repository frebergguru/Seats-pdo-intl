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
    <table id="seatMap" cellspacing="0" cellpadding="0">
        <?php
        $data = file_get_contents("map.txt");
        $rows = explode("\n", trim($data));

        foreach ($rows as $row) {
            echo "<tr>";
            for ($i = 0; $i < strlen($row); $i++) {
                $char = $row[$i];
                switch ($char) {
                    case "#":
                        try {
                            $stmt = $pdo->prepare("SELECT 1 FROM reservations WHERE taken = ?");
                            $stmt->execute([$seatId]);
                            $taken = $stmt->fetchColumn();

                            if ($taken) {
                                echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/red.jpg" alt="' . $langArray['occupied_seat'] . ' #' . $seatId . '"></a></td>';
                            } elseif ($useatId === $seatId) {
                                echo '<td class="seat"><img src="./img/yellow.jpg" alt="' . $langArray['selected_seat'] . ' #' . $seatId . '"></td>';
                            } else {
                                echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/green.jpg" alt="' . $langArray['vacant_seat'] . ' #' . $seatId . '"></a></td>';
                            }
                            $seatId++;
                        } catch (PDOException $e) {
                            echo '<td>Error</td>';
                        }
                        break;
                    case "f":
                        echo '<td class="floor" title="' . $langArray['floor'] . '"></td>';
                        break;
                    case "w":
                        echo '<td class="wall"><img src="./img/wall.jpg" class="wall" alt="' . $langArray['wall'] . '"></td>';
                        break;
                    case "k":
                        echo '<td class="kitchen" title="' . $langArray['kitchen'] . '">&#127869;</td>';
                        break;
                    case "b":
                        echo '<td class="toilet" title="' . $langArray['bathroom'] . '">&#128701;</td>';
                        break;
                    case "d":
                        echo '<td class="door" title="' . $langArray['door'] . '">&#128682;</td>';
                        break;
                    case "e":
                        echo '<td class="exit"><img src="./img/exit.jpg" class="exit" alt="' . $langArray['exit'] . '"></td>';
                        break;
                    default:
                        echo '<td></td>';
                        break;
                }
            }
            echo "</tr>";
        }
        ?>
    </table>
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

