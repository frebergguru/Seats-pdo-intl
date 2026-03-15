<?php
/*
 * This file is part of Seats-pdo-intl.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

$home = true;

$loggedIn = isset($_SESSION['nickname']) && !empty($_SESSION['nickname']);
$nickname = $loggedIn ? htmlspecialchars($_SESSION['nickname'], ENT_QUOTES, 'UTF-8') : '';

// Defaults for map data
$grid = [];
$maxSeats = 0;

// Fetch reservations and user data from the database
$reservations = [];
$userSeat = null;

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    // Read the seat map (DB first, file fallback)
    $mapData = getMapData($pdo);
    $grid = $mapData['grid'];
    $maxSeats = $mapData['max_seats'];

    // Get all reservations with user nicknames
    $stmt = $pdo->query("SELECT r.taken, u.nickname FROM reservations r JOIN users u ON r.user_id = u.id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $reservations[(int)$row['taken']] = $row['nickname'];
    }

    // Get current user's reserved seat
    if ($loggedIn) {
        $stmt = $pdo->prepare("SELECT rseat FROM users WHERE lower(nickname) = :nickname");
        $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData && !empty($userData['rseat'])) {
            $userSeat = (int)$userData['rseat'];
        }
    }
} catch (PDOException $e) {
    error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
}

require_once 'includes/header.php';

// Symbol explanation
echo '<div class="seat_registered">';
echo '<p class="heading">' . $langArray['symbol_explanation'] . '</p>';
echo '<p class="seat_symbols">';
if ($loggedIn) {
    echo '<img src="./img/yellow.jpg" alt="' . $langArray['selected_seat'] . '" class="seat"> = ' . $langArray['selected_seat'] . '&nbsp;&nbsp;';
}
echo '<img src="./img/red.jpg" alt="' . $langArray['occupied_seat'] . '" class="seat"> = ' . $langArray['occupied_seat'] . '&nbsp;&nbsp;';
echo '<img src="./img/green.jpg" alt="' . $langArray['vacant_seat'] . '" class="seat"> = ' . $langArray['vacant_seat'] . '&nbsp;&nbsp;';
echo '<img src="./img/wall.jpg" alt="' . $langArray['wall'] . '" class="wall"> = ' . $langArray['wall'] . '&nbsp;&nbsp;';
echo '&#x1F6AA; = ' . $langArray['door'] . '&nbsp;&nbsp;';
echo '<img src="./img/exit.jpg" alt="' . $langArray['exit'] . '" class="exit"> = ' . $langArray['exit'] . '&nbsp;&nbsp;';
echo '</p>';
echo '</div><br>';

if (!$loggedIn) {
    echo '<div class="msg">' . $langArray['login_before_reserving'] . '</div><br>';
}

if ($loggedIn && $userSeat) {
    echo '<div class="seat_registered">' . $langArray['you_have_reserved_seat_number'] . ' ' . $userSeat . '</div><br>';
}

// Seat info popup (shown on click)
echo '<div id="seatInfoBox" class="seat-info-box" style="display:none;">';
echo '  <span id="seatInfoText"></span>';
echo '  <button id="seatInfoClose" class="seat-info-close">' . $langArray['close_btn'] . '</button>';
echo '</div>';

// Seat confirmation popup (shown when clicking a vacant seat)
echo '<div id="seatConfirmBox" class="seat-confirm-box" style="display:none;">';
echo '  <span id="seatConfirmText"></span><br>';
echo '  <a id="seatConfirmYes" href="#" class="submit seat-confirm-btn">' . $langArray['yes'] . '</a>';
echo '  <button id="seatConfirmNo" class="submit seat-confirm-btn">' . $langArray['cancel'] . '</button>';
echo '</div>';

// Render the seat map
echo '<div id="seatMap">';
$cols = isset($grid[0]) ? strlen($grid[0]) : 1;
echo '<div class="seatmap" style="grid-template-columns: repeat(' . $cols . ', var(--cell-size, 20px));">';

$seatNumber = 0;

foreach ($grid as $row) {
    $chars = str_split($row);
    foreach ($chars as $char) {
        switch ($char) {
            case '#':
                $seatNumber++;
                if (isset($reservations[$seatNumber])) {
                    $reservedBy = htmlspecialchars($reservations[$seatNumber], ENT_QUOTES, 'UTF-8');
                    if ($seatNumber === $userSeat) {
                        echo '<img src="./img/yellow.jpg" alt="' . $langArray['seat_number'] . ' ' . $seatNumber . '" class="seat seat-clickable" data-seat="' . $seatNumber . '" data-owner="' . $reservedBy . '">';
                    } else {
                        echo '<img src="./img/red.jpg" alt="' . $langArray['seat_number'] . ' ' . $seatNumber . '" class="seat seat-clickable" data-seat="' . $seatNumber . '" data-owner="' . $reservedBy . '">';
                    }
                } else {
                    if ($loggedIn && !$userSeat) {
                        echo '<img src="./img/green.jpg" alt="' . $langArray['seat_number'] . ' ' . $seatNumber . '" class="seat seat-clickable seat-vacant" data-seat="' . $seatNumber . '">';
                    } else {
                        echo '<img src="./img/green.jpg" alt="' . $langArray['seat_number'] . ' ' . $seatNumber . '" class="seat seat-clickable seat-vacant-info" data-seat="' . $seatNumber . '">';
                    }
                }
                break;
            case 'f':
                echo '<span class="floor"></span>';
                break;
            case 'w':
                echo '<img src="./img/wall.jpg" alt="' . $langArray['wall'] . '" class="wall">';
                break;
            case 'k':
                echo '<img src="./img/wall.jpg" alt="' . $langArray['kitchen'] . '" title="' . $langArray['kitchen'] . '" class="wall">';
                break;
            case 'b':
                echo '<img src="./img/wall.jpg" alt="' . $langArray['bathroom'] . '" title="' . $langArray['bathroom'] . '" class="wall">';
                break;
            case 'd':
                echo '<span class="door" title="' . $langArray['door'] . '">&#x1F6AA;</span>';
                break;
            case 'e':
                echo '<img src="./img/exit.jpg" alt="' . $langArray['exit'] . '" title="' . $langArray['exit'] . '" class="exit">';
                break;
            default:
                echo '<span class="floor"></span>';
                break;
        }
    }
}

echo '</div>';
echo '</div>';
echo '<br>';
echo '<p class="heading">' . $langArray['stage_front'] . '</p>';

?>
<script>
$(function() {
    var infoBox = $('#seatInfoBox');
    var infoText = $('#seatInfoText');
    var confirmBox = $('#seatConfirmBox');
    var confirmText = $('#seatConfirmText');
    var confirmYes = $('#seatConfirmYes');

    function hideAll() {
        infoBox.hide();
        confirmBox.hide();
    }

    // Occupied seats (red/yellow) - show owner info
    $('.seat-clickable[data-owner]').on('click', function() {
        hideAll();
        var seat = $(this).data('seat');
        var owner = $('<span>').text($(this).data('owner')).html();
        infoText.html(langArray.seat_number + ' ' + seat + ' ' + langArray.is_reserved_by + ' <strong>' + owner + '</strong>');
        infoBox.show();
    });

    // Vacant seats for logged-in user who can book - show confirmation
    $('.seat-vacant').on('click', function() {
        hideAll();
        var seat = $(this).data('seat');
        confirmText.text(langArray.do_you_want_to_reserve_seat_number + ' ' + seat + '?');
        confirmYes.attr('href', 'book.php?seatid=' + seat);
        confirmBox.show();
    });

    // Vacant seats for non-bookable state - show vacancy info
    $('.seat-vacant-info').on('click', function() {
        hideAll();
        var seat = $(this).data('seat');
        infoText.text(langArray.seat_number + ' ' + seat + ' - ' + langArray.vacant_seat);
        infoBox.show();
    });

    $('#seatInfoClose').on('click', hideAll);
    $('#seatConfirmNo').on('click', function() { hideAll(); });

    // Close on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') hideAll();
    });
});
</script>
<?php
require_once 'includes/footer.php';
?>
