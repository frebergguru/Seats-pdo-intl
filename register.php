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

require 'includes/config.php';
require 'includes/i18n.php';

$register_page = true;
require 'includes/header.php';

session_start();

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$formstatus = 'PENDING';

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        $form_csrf_token = $_POST['csrf_token'] ?? '';
        if ($form_csrf_token !== $_SESSION['csrf_token']) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['invalid_csfr_token'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        // Sanitize and validate inputs
        $fullname = trim($_POST['fullname'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $email = filter_var(mb_strtolower(trim($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (empty($fullname) || !preg_match($fullname_regex, $fullname)) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['fullname_contains_illegal_characters'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        if (empty($nickname) || !preg_match($nickname_regex, $nickname)) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_nickname_is_invalid'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        if (!$email) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['you_must_enter_a_valid_email_address'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        if (empty($password) || !preg_match($pwd_regex, $password)) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_password_contains_illegal_characters'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        if ($password !== $password2) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_passwords_dosent_match'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['the_email_address_already_exists'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        // Check for duplicate nickname
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = :nickname");
        $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['nickname_already_exists'] . '</div><br><br>';
            $formstatus = 'FAIL';
        }

        // If no errors, insert the user into the database
        if ($formstatus !== 'FAIL') {
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, nickname, email, password) VALUES (:fullname, :nickname, :email, :password)");
            $stmt->bindValue(':fullname', $fullname, PDO::PARAM_STR);
            $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->execute();

            echo '<span class="srs-header">' . $langArray['user_was_created'] . '!</span>
            <div class="srs-content">' . $langArray['you_can_now_login_and_reserve_a_seat'] . '</div><br><br>';
            $formstatus = 'SUCCESS';
        }
    }
} catch (PDOException $e) {
    error_log($langArray['invalid_query'] . ' ' . $e->getMessage(), 0);
    echo '<div class="regerror">' . $langArray['error'] . ': ' . $langArray['database_error'] . '</div><br><br>';
    $formstatus = 'FAIL';
}

// Display the registration form if the form submission failed or hasn't been submitted
if ($formstatus !== 'SUCCESS') {
    ?>
    <form class="srs-container" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <span class="srs-header"><?php echo $langArray['new_user']; ?></span>
        <div class="srs-content">
            <label for="fullname" class="srs-lb"><?php echo $langArray['fullname']; ?></label>
            <input name="fullname" id="fullname" class="srs-tb" required><br>
            <label for="nickname" class="srs-lb"><?php echo $langArray['nickname']; ?></label>
            <input name="nickname" id="nickname" class="srs-tb" required><br>
            <label for="email" class="srs-lb"><?php echo $langArray['email']; ?></label>
            <input name="email" id="email" class="srs-tb" required><br>
            <label for="password" class="srs-lb"><?php echo $langArray['password']; ?></label>
            <input name="password" id="password" type="password" class="srs-tb" required><br>
            <label for="password2" class="srs-lb"><?php echo $langArray['repeat_password']; ?></label>
            <input name="password2" id="password2" type="password" class="srs-tb" required><br>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
                <input type="submit" class="submit" name="regsubmit" value="<?php echo $langArray['register_button']; ?>">
            </div>
        </div>
    </form>
    <?php
}
require 'includes/footer.php';
?>