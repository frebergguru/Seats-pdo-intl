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

session_start();

// Validate and sanitize seat ID
$seatid = filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT);
if ($seatid === false || $seatid <= 0) {
    $seatid = null;
}

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
} catch (PDOException $e) {
    error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
    exit('Database connection error');
}

require 'includes/header.php';
?>
<div class="seatmap">
    <p class="heading">
        <?php echo $langArray['symbol_explanation']; ?>:
    </p>
    <p class="seat_symbols">
        <img src="./img/wall.jpg" class="wall" title="<?php echo $langArray['wall']; ?>" alt="<?php echo $langArray['wall']; ?>"> <?php echo $langArray['wall']; ?> &#127869; <?php echo $langArray['kitchen']; ?> &#128701; <?php echo $langArray['bathroom']; ?> &#128682; <?php echo $langArray['door']; ?> <img src="./img/exit.jpg" class="exit" title="<?php echo $langArray['exit']; ?>" alt="<?php echo $langArray['exit']; ?>"> <?php echo $langArray['exit']; ?><br><br>
        <img src="./img/yellow.jpg" alt="<?php echo $langArray['selected_seat']; ?>"> <?php echo $langArray['selected_seat']; ?> <img src="./img/red.jpg" alt="<?php echo $langArray['occupied_seat']; ?>"> <?php echo $langArray['occupied_seat']; ?> <img src="./img/green.jpg" alt="<?php echo $langArray['vacant_seat']; ?>">
        <?php echo $langArray['vacant_seat']; ?>
    </p>
    <hr>
    <?php
    echo '<p class="heading">' . $langArray['stage_front'] . '</p><table id="seatMap">';
    $data = file_get_contents("map.txt");
    $seatmaprows = explode("\n", $data);
    $seatId = 1;

    foreach ($seatmaprows as $seatmaprow) {
        echo "<tr>";
        for ($i = 0; $i < strlen($seatmaprow); $i++) {
            switch ($seatmaprow[$i]) {
                case "#":
                    try {
                        $stmt = $pdo->prepare("SELECT user_id FROM reservations WHERE taken = :seat");
                        $stmt->bindValue(":seat", $seatId, PDO::PARAM_INT);
                        $stmt->execute();
                        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($reservation) {
                            echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/red.jpg" title="' . $langArray['occupied_seat'] . ' #' . $seatId . '" alt="' . $langArray['occupied_seat'] . ' #' . $seatId . '"></a></td>';
                        } elseif ($seatid === $seatId) {
                            echo '<td class="seat"><img src="./img/yellow.jpg" title="' . $langArray['selected_seat'] . ' #' . $seatId . '" alt="' . $langArray['selected_seat'] . ' #' . $seatId . '"></td>';
                        } else {
                            echo '<td class="seat"><a href="?seatid=' . $seatId . '"><img src="./img/green.jpg" title="' . $langArray['vacant_seat'] . ' #' . $seatId . '" alt="' . $langArray['vacant_seat'] . ' #' . $seatId . '"></a></td>';
                        }
                        $seatId++;
                    } catch (PDOException $e) {
                        error_log('Database error: ' . $e->getMessage());
                        exit('An error occurred while loading the seat map.');
                    }
                    break;
                case "f":
                    echo '<td class="floor" title="' . $langArray['floor'] . '"></td>';
                    break;
                case "w":
                    echo '<td class="wall" title="' . $langArray['wall'] . '"><img src="./img/wall.jpg" class="wall" title="' . $langArray['wall'] . '" alt="' . $langArray['wall'] . '"></td>';
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
                    echo '<td class="exit" title="' . $langArray['exit'] . '"><img src="./img/exit.jpg" class="exit" title="' . $langArray['exit'] . '" alt="' . $langArray['exit'] . '"></td>';
                    break;
            }
        }
        echo "</tr>";
    }
    ?>
    </table>
</div>
<?php
if ($seatid) {
    try {
        $stmt = $pdo->prepare("SELECT r.nickname FROM reservations AS r INNER JOIN users AS u ON r.user_id = u.id WHERE r.taken = :seatid");
        $stmt->bindValue(':seatid', $seatid, PDO::PARAM_INT);
        $stmt->execute();
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reservation) {
            echo '<div class="seat_registered">' . $langArray['seat_number'] . ' ' . $seatid . ' ' . $langArray['is_reserved_by'] . ' ' . htmlspecialchars($reservation['nickname']) . '</div>';
        } else {
            echo '<div class="seat_registered">' . $langArray['seat_number'] . ' ' . $seatid . ' ' . $langArray['is_vacant'] . '</div>';
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        exit('An error occurred while retrieving seat information.');
    }
}
require 'includes/footer.php';
?>