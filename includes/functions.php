<?php
function checkOccupiedSeat($rows)
{
    if (!isset($seatid)) {
        $seatid = filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT);
    }
    foreach ($rows as $row) {
        if ($row["taken"] == $seatid) {
            $occupied = 1;
            $user = $row["user_id"];
        }
    }
}
?>