<?php
/*
 * This file is part of Seats-pdl-intl.
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

/**
 * Generate a secure random key.
 *
 * @param int $length The length of the random key in bytes. Default is 32 bytes.
 * @return string A hexadecimal representation of the random key.
 * @throws Exception If an appropriate source of randomness cannot be found.
 */
function genRandomKey($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Reads and parses the map file.
 *
 * @return array An associative array containing 'grid' (array of rows) and 'max_seats' (int).
 */
function getMapData()
{
    $mapFile = __DIR__ . '/../map.txt';
    if (!file_exists($mapFile)) {
        return ['grid' => [], 'max_seats' => 0];
    }

    $data = file_get_contents($mapFile);
    // Split by any newline sequence
    $rows = preg_split('/\r\n|\r|\n/', trim($data));

    // Count max seats
    $maxSeats = substr_count($data, "#");

    return [
        'grid' => $rows,
        'max_seats' => $maxSeats
    ];
}
?>
