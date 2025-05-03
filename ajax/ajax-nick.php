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

include '../includes/config.php';

try {
    // Establish a database connection
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    // Prepare the query based on the database driver
    switch (DB_DRIVER) {
        case "mysql":
            $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = :nickname");
            break;
        case "pgsql":
            $stmt = $pdo->prepare("SELECT id FROM users WHERE lower(nickname) = :nickname");
            break;
        default:
            error_log("Unsupported database driver: " . DB_DRIVER);
            echo 'NICKFAIL';
            exit();
    }

    // Sanitize and validate the nickname
    $postnickname = trim($_POST['nickname'] ?? '');
    if (empty($postnickname)) {
        echo 'NICKFAIL';
        exit();
    }

    if (strlen($postnickname) < 4) {
        echo 'LENGTHFAIL';
        exit();
    }

    // Bind and execute the query
    $stmt->bindValue(':nickname', mb_strtolower($postnickname), PDO::PARAM_STR);
    $stmt->execute();

    // Check if the nickname is in use
    if ($stmt->rowCount() > 0) {
        echo 'NICKEXISTS';
    } else {
        echo 'NICKOK';
    }
} catch (PDOException $e) {
    // Log the error and return a failure response
    error_log("Database error: " . $e->getMessage());
    echo 'NICKFAIL';
}
?>