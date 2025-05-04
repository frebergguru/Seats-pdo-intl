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

header('Content-Type: application/json');
require '../includes/config.php';

$response = ['status' => 'UNKNOWN_ERROR'];

// Sanitize and validate the password input
$postpassword = trim($_POST['password'] ?? '');

if ($postpassword === '') {
    $response['status'] = 'EMPTY';
} elseif (!preg_match($pwd_regex, $postpassword)) {
    $response['status'] = 'INVALID_CHARACTERS';
} else {
    $response['status'] = 'STRONG';
}

echo json_encode($response);
?>
