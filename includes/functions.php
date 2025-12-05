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
 * Reads the map file and returns the map structure and total seat count.
 *
 * @param string $mapFile The path to the map file. Default is "map.txt".
 * @return array An associative array containing 'rows' (array of strings) and 'max_seats' (int).
 */
function getMapData($mapFile = "map.txt")
{
    if (!file_exists($mapFile)) {
        return ['rows' => [], 'max_seats' => 0];
    }
    $data = file_get_contents($mapFile);
    return [
        'rows' => explode("\n", trim($data)),
        'max_seats' => substr_count($data, "#")
    ];
}