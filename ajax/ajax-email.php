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

// Sanitize and validate the email
$email = filter_var(mb_strtolower(trim($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo 'EMAILFAIL';
    exit();
}

try {
    // Use the shared PDO connection
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    // Prepare the query based on the database driver
    switch (DB_DRIVER) {
        case "mysql":
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            break;
        case "pgsql":
            $stmt = $pdo->prepare("SELECT id FROM users WHERE lower(email) = :email");
            break;
        default:
            error_log("Unsupported database driver: " . DB_DRIVER);
            echo 'EMAILFAIL';
            exit();
    }

    // Bind and execute the query
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    // Check if the email is in use
    if ($stmt->rowCount() > 0) {
        echo 'EMAILINUSE';
    } else {
        echo 'EMAILOK';
    }
} catch (PDOException $e) {
    // Log the error and return a failure response
    error_log("Database error: " . $e->getMessage());
    echo 'EMAILFAIL';
}
?>