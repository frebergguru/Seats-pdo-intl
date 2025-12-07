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

require '../includes/config.php';

header('Content-Type: application/json'); // Tell the browser we're returning JSON

$response = [];

$email = mb_strtolower(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

if (!$email) {
    echo json_encode([
        'status' => 'EMAILFAIL',
        'message' => $langArray['you_must_enter_a_valid_email_address'] ?? 'Invalid email address.',
    ]);
    exit();
}

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    $query = match (DB_DRIVER) {
        "mysql" => "SELECT id FROM users WHERE email = :email",
        "pgsql" => "SELECT id FROM users WHERE lower(email) = :email",
        default => throw new Exception("unsupported_database_driver"),
    };

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $response = [
            'status' => 'EMAILINUSE',
            'message' => $langArray['the_email_address_already_exists'] ?? 'Email is already in use.',
        ];
    } else {
        $response = [
            'status' => 'EMAILOK',
            'message' => '',
        ];
    }
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    $response = [
        'status' => 'EMAILFAIL',
        'message' => 'Database error occurred.',
    ];
}

echo json_encode($response);
?>
