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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'includes/config.php';
require 'includes/i18n.php';

$register_page = true;
require 'includes/header.php';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$fullname = $nickname = $email = '';
$formstatus = null;

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regsubmit'])) {
        // Trim and sanitize input
        $fullname   = trim($_POST['fullname'] ?? '');
        $nickname   = trim($_POST['nickname'] ?? '');
        $email      = mb_strtolower(trim($_POST['email'] ?? ''));
        $password   = $_POST['password'] ?? '';
        $password2  = $_POST['password2'] ?? '';
        $form_token = $_POST['csrf_token'] ?? '';

        // Input validation
        $errors = [];

        if ($form_token !== $_SESSION['csrf_token']) {
            $errors[] = $langArray['invalid_csfr_token'];
        }

        if (empty($fullname)) {
            $errors[] = $langArray['you_must_enter_a_name'];
        } elseif (!preg_match($fullname_regex, $fullname)) {
            $errors[] = $langArray['fullname_contains_illegal_characters'];
        }

        if (empty($nickname)) {
            $errors[] = $langArray['you_must_enter_a_nickname'];
        } elseif (!preg_match($nickname_regex, $nickname)) {
            $errors[] = $langArray['the_nickname_is_invalid'];
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $langArray['you_must_enter_a_valid_email_address'];
        }

        if (empty($password)) {
            $errors[] = $langArray['you_must_enter_a_password'];
        } elseif (!preg_match($pwd_regex, $password)) {
            $errors[] = $langArray['the_password_contains_illegal_characters'];
        }

        if (empty($password2)) {
            $errors[] = $langArray['you_must_enter_a_confirmation_password'];
        } elseif ($password !== $password2) {
            $errors[] = $langArray['the_passwords_dosent_match'];
        }

        // Duplicate email check
        if (empty($errors)) {
            $query = DB_DRIVER === 'pgsql'
                ? "SELECT id FROM users WHERE lower(email) = lower(:email)"
                : "SELECT id FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = $langArray['the_email_address_already_exists'];
            }
        }

        // Duplicate nickname check
        if (empty($errors)) {
            $query = DB_DRIVER === 'pgsql'
                ? "SELECT id FROM users WHERE lower(nickname) = lower(:nickname)"
                : "SELECT id FROM users WHERE nickname = :nickname";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':nickname' => $nickname]);
            if ($stmt->fetch()) {
                $errors[] = $langArray['nickname_already_exists'];
            }
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, nickname, email, password) VALUES (:fullname, :nickname, :email, :password)");
            $stmt->execute([
                ':fullname' => $fullname,
                ':nickname' => $nickname,
                ':email'    => $email,
                ':password' => $hashedPassword
            ]);

            echo '<span class="srs-header">' . $langArray['user_was_created'] . '!</span>
            <div class="srs-content">' . $langArray['you_can_now_login_and_reserve_a_seat'] . '</div><br><br>';
            $formstatus = true;
        } else {
            foreach ($errors as $error) {
                echo '<div class="regerror">' . $langArray['error'] . ': ' . htmlspecialchars($error) . '</div><br>';
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database error: {$e->getMessage()}");
    echo '<div class="regerror">' . $langArray['internal_error'] . '</div><br>';
}

if ($formstatus !== true) {
    ?>
    <form class="srs-container" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <span class="srs-header"><?= $langArray['new_user'] ?></span>
        <div class="srs-content">
            <label for="fullname" class="srs-lb"><?= $langArray['fullname'] ?></label>
            <input name="fullname" value="<?= htmlspecialchars($fullname) ?>" id="fullname" class="srs-tb"><br>
            <label for="nickname" class="srs-lb"><?= $langArray['nickname'] ?></label>
            <input name="nickname" value="<?= htmlspecialchars($nickname) ?>" id="nickname" class="srs-tb"><br>
            <label for="email" class="srs-lb"><?= $langArray['email'] ?></label>
            <input name="email" value="<?= htmlspecialchars($email) ?>" id="email" class="srs-tb"><br>
            <a href="#" id="passwordRequirements"><?= $langArray['password_requirements'] ?></a><br>
            <div class="bubble-container">
                <div class="bubble" id="bubblePopup">
                    <?= $langArray['password_requirements_text'] ?>
                    <button id="closePopup"><?= $langArray['close_btn'] ?></button>
                </div>
            </div>
            <label for="password" class="srs-lb"><?= $langArray['password'] ?></label>
            <input name="password" id="password" type="password" class="srs-tb"><br>
            <label for="password2" class="srs-lb"><?= $langArray['repeat_password'] ?></label>
            <input name="password2" id="password2" type="password" class="srs-tb"><br>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
                <input type="submit" class="submit" name="regsubmit" value="<?= $langArray['register_button'] ?>">
            </div>
            <div class="srs-slope"></div>
        </div>
    </form>
    <br><br>
    <script src="./js/pwdreq.js"></script>
    <script src="./js/formcheck.js"></script>
    <script src="./js/pwdcheck.js"></script>
<?php
}
require 'includes/footer.php';

