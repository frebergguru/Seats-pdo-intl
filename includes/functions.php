<?php
function genRandomKey()
{
    $randomkey = bin2hex(random_bytes(32));
    return $randomkey;
}
?>