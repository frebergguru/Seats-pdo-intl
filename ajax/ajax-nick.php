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
 *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

header('Content-Type: application/json');
include '../includes/config.php';
include '../includes/functions.php';

$response = ['status' => 'ERROR'];

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    if (!checkRateLimit($pdo, 'enumerate')) {
        echo json_encode(['status' => 'RATE_LIMITED']);
        exit();
    }
    recordRateAttempt($pdo, 'enumerate');

    $nickname = isset($_POST['nickname']) ? mb_strtolower(trim($_POST['nickname'])) : '';

    if (!$nickname || strlen($nickname) < 4) {
        $response['status'] = 'TOO_SHORT';
    } elseif (safePregMatch($nickname_regex, $nickname) !== 1) {
        $response['status'] = 'INVALID_CHARS';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) = :nickname");

        $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'EXISTS';
        } else {
            $response['status'] = 'OK';
        }
    }
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    $response['status'] = 'DB_ERROR';
}

echo json_encode($response);

