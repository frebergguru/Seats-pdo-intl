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

// Generate CSRF token for booking form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

require_once 'includes/header.php';

// Symbol explanation
echo '<div class="seat_registered">';
echo '<p class="heading">' . $langArray['symbol_explanation'] . '</p>';
echo '<div class="legend">';
if ($loggedIn) {
    echo '<div class="legend-item"><img src="./img/yellow.jpg" alt="' . $langArray['selected_seat'] . '" class="seat"><span>' . $langArray['selected_seat'] . '</span></div>';
}
echo '<div class="legend-item"><img src="./img/red.jpg" alt="' . $langArray['occupied_seat'] . '" class="seat"><span>' . $langArray['occupied_seat'] . '</span></div>';
echo '<div class="legend-item"><img src="./img/green.jpg" alt="' . $langArray['vacant_seat'] . '" class="seat"><span>' . $langArray['vacant_seat'] . '</span></div>';
echo '<div class="legend-item"><img src="./img/wall.jpg" alt="' . $langArray['wall'] . '" class="wall"><span>' . $langArray['wall'] . '</span></div>';
echo '<div class="legend-item"><span class="floor legend-swatch"></span><span>' . $langArray['floor'] . '</span></div>';
echo '<div class="legend-item"><span class="door legend-swatch">&#x1F6AA;</span><span>' . $langArray['door'] . '</span></div>';
echo '<div class="legend-item"><img src="./img/exit.jpg" alt="' . $langArray['exit'] . '" class="exit"><span>' . $langArray['exit'] . '</span></div>';
echo '<div class="legend-item"><span class="kitchen legend-swatch">&#x1F37D;</span><span>' . $langArray['kitchen'] . '</span></div>';
echo '<div class="legend-item"><span class="toilet legend-swatch">&#x1F6BD;</span><span>' . $langArray['bathroom'] . '</span></div>';
echo '</div>';
echo '</div><br>';

if (!$loggedIn) {
    echo '<div class="msg">' . $langArray['login_before_reserving'] . '</div><br>';
}

if ($loggedIn && $userSeat) {
    echo '<div class="seat_registered">' . $langArray['you_have_reserved_seat_number'] . ' ' . $userSeat . '</div><br>';
}

// Seat info popup (shown on click)
echo '<div id="seatInfoBox" class="seat-info-box" style="display:none;" role="status" aria-live="polite">';
echo '  <span id="seatInfoText"></span>';
echo '  <button type="button" id="seatInfoClose" class="seat-info-close" aria-label="' . $langArray['close_btn'] . '">' . $langArray['close_btn'] . '</button>';
echo '</div>';

// Seat confirmation popup (shown when clicking a vacant seat)
echo '<div id="seatConfirmBox" class="seat-confirm-box" style="display:none;" role="dialog" aria-live="assertive">';
echo '  <span id="seatConfirmText"></span><br>';
echo '  <form id="seatConfirmForm" method="POST" action="book.php" style="display:inline;">';
echo '    <input type="hidden" name="seatid" id="seatConfirmSeatId" value="">';
echo '    <input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
echo '    <button type="submit" class="submit seat-confirm-btn">' . $langArray['yes'] . '</button>';
echo '  </form>';
echo '  <button type="button" id="seatConfirmNo" class="submit seat-confirm-btn">' . $langArray['cancel'] . '</button>';
echo '</div>';

// Render the seat map
echo '<div id="seatMap" role="grid" aria-label="' . $langArray['symbol_explanation'] . '">';
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
                    if ($loggedIn) {
                        $cls = $userSeat ? 'seat-change' : 'seat-vacant';
                        echo '<img src="./img/green.jpg" alt="' . $langArray['seat_number'] . ' ' . $seatNumber . '" class="seat seat-clickable ' . $cls . '" data-seat="' . $seatNumber . '">';
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
                echo '<span class="kitchen" title="' . $langArray['kitchen'] . '">&#x1F37D;</span>';
                break;
            case 'b':
                echo '<span class="toilet" title="' . $langArray['bathroom'] . '">&#x1F6BD;</span>';
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
    var confirmSeatId = $('#seatConfirmSeatId');

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
        confirmSeatId.val(seat);
        confirmBox.show();
    });

    // Vacant seats for logged-in user who already has a seat - show change confirmation
    $('.seat-change').on('click', function() {
        hideAll();
        var seat = $(this).data('seat');
        confirmText.text(langArray.do_you_want_to_change_to_seat + ' ' + seat + '?');
        confirmSeatId.val(seat);
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
