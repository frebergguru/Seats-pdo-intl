<?php
/*
 * This file is part of Seats-pdl-intl.
 *
 * Copyright (C) 2023-2025 Morten Freberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

session_start(); // Start the session
require 'includes/config.php';
require 'includes/i18n.php';

$pwdwrong = false; // Flag to track incorrect password

// Sanitize and validate inputs
$nickname = trim($_POST['nickname'] ?? '');
$password = trim($_POST['password'] ?? '');

try {
    if (!empty($nickname) && !empty($password)) {
        // Establish database connection
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

        // Prepare the query based on the database driver
        switch (DB_DRIVER) {
            case "mysql":
                $stmt = $pdo->prepare("SELECT password FROM users WHERE nickname = :nickname");
                break;
            case "pgsql":
                $stmt = $pdo->prepare("SELECT password FROM users WHERE lower(nickname) = :nickname");
                break;
            default:
                throw new Exception("Unsupported database driver");
        }

        // Bind and execute the query
        $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($results && password_verify($password, $results["password"])) {
            $_SESSION['nickname'] = $nickname;

            // Construct the redirect URL
            $baseUrl = 'https://' . $_SERVER['HTTP_HOST']; // Use a trusted base URL
            $redirectPath = dirname($_SERVER['PHP_SELF']);
            $redirectUrl = filter_var($baseUrl . $redirectPath, FILTER_SANITIZE_URL);

            // Validate the redirect URL to ensure it stays within the same domain
            if (strpos($redirectUrl, $baseUrl) === 0) {
                header("Location: $redirectUrl");
                exit;
            } else {
                // Log the invalid redirect attempt and redirect to a safe default page
                error_log("Invalid redirect attempt to: $redirectUrl");
                header("Location: $baseUrl/index.php");
                exit;
            }
        } else {
            // Incorrect username or password
            $pwdwrong = true;
        }
    }
} catch (PDOException $e) {
    // Log database errors
    error_log($langArray['invalid_query'] . ' ' . $e->getMessage() . '\n' . ($stmt ? $langArray['whole_query'] . ' ' . $stmt->queryString : ''), 0);
} catch (Exception $e) {
    // Log other exceptions
    error_log("Error: " . $e->getMessage());
}

// Include the header
include 'includes/header.php';

// Display error message if login failed
if ($pwdwrong) {
    echo '<span class="srs-header">' . $langArray['wrong_username_or_password'] . '</span><br><br><br>';
}

// Display the login form
?>
<form class="srs-container" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <span class="srs-header"><?php echo $langArray['login']; ?></span>

    <div class="srs-content">
        <label for="nickname" class="srs-lb"><?php echo $langArray['nickname']; ?></label>
        <input name="nickname" value="<?php echo htmlspecialchars($nickname); ?>" id="nickname" class="srs-tb"><br><br>

        <label for="password" class="srs-lb"><?php echo $langArray['password']; ?></label>
        <input name="password" id="password" type="password" class="srs-tb"><br>
    </div>

    <div class="srs-footer">
        <div class="srs-button-container">
            <input type="submit" class="submit" value="<?php echo $langArray['login']; ?>">
        </div>
        <div class="srs-slope"></div>
    </div>
</form>

<?php
// Include the footer
include 'includes/footer.php';
?>
