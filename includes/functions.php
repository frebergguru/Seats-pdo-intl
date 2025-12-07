<?php
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