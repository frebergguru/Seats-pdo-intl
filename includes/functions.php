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
?>