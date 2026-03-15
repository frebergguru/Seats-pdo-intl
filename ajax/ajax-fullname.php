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
 *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

header('Content-Type: application/json');
require '../includes/config.php';

$response = ['status' => 'ERROR'];

$fullname = trim($_POST['fullname'] ?? '');

if ($fullname === '') {
    $response['status'] = 'EMPTY';
} elseif (preg_match($fullname_illegal_chars_regex, $fullname)) {
    $response['status'] = 'ILLEGAL_CHARS';
} elseif (!preg_match($fullname_regex, $fullname)) {
    $response['status'] = 'INVALID';
} else {
    $response['status'] = 'OK';
}

echo json_encode($response);
?>
