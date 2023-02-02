<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/i18n.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$nickname = $_SESSION['nickname'];
if (isset($nickname)) {
    $deluser = 'true';
    try {
        $dsn = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    } catch (PDOException $e) {
        error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
        exit();
    }
    if (isset($_POST['delete_user'])) {
        $delete_user = htmlspecialchars($_POST['delete_user']);
    }
    if (isset($delete_user)) {
        $password = (htmlspecialchars($_POST['password']));

        switch (DB_DRIVER) {
            case "mysql":
                $stmt = $pdo->prepare('SELECT password, rseat FROM users WHERE nickname = :nickname');
                break;
            case "pgsql":
                $stmt = $pdo->prepare('SELECT password, rseat FROM users WHERE lower(nickname) LIKE :nickname');
                break;
            default:
                throw new Exception("unsupported_database_driver");
        }
        $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashed_password = $user['password'];
        $reservation_id = $user['rseat'];

        // Check if the provided password matches the hashed password
        if (password_verify($password, $hashed_password)) {
            // If the password is correct, delete the user and (if exists) the reservation
            $pdo->beginTransaction();
            try {
                if ($reservation_id) {
                    $stmt = $pdo->prepare('DELETE FROM reservations WHERE taken = :reservation_id');
                    $stmt->execute(['reservation_id' => $reservation_id]);
                }
                switch (DB_DRIVER) {
                    case "mysql":
                        $stmt = $pdo->prepare('DELETE FROM users WHERE nickname = :nickname');
                        break;
                    case "pgsql":
                        $stmt = $pdo->prepare('DELETE FROM users WHERE lower(nickname) LIKE :nickname');
                        break;
                    default:
                        throw new Exception("unsupported_database_driver");
                }
                $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
                $stmt->execute();

                $pdo->commit();
                if (isset($_SESSION['nickname'])) {
                    session_destroy();
                    $left = true;
                }
                require_once("includes/header.php");
                echo '<div class="userdel">' . $langArray['user_is_now_successfully_deleted'] . '</div><br><br>';
                require_once("includes/footer.php");
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            require_once("includes/header.php");
            echo '<div class="userdel">' . $langArray['invalid_password'] . '</div><br><br>';
            require_once("includes/footer.php");
        }

    } else {
        require_once("includes/header.php");
        echo '<form class="srs-container" method="POST" action="' . $_SERVER["PHP_SELF"] . '">
    <span class="srs-header">' . $langArray['delete_account'] . '</span>
    <div class="srs-content">
        <p><strong>' . $langArray['please_confirm_with_your_password'] . '</strong></p>
        <label for="password" class="srs-lb">' . $langArray['password'] . '</label><input name="password" id="password" type="password" class="srs-tb"><br>
    </div>
    <div class="srs-footer">
        <div class="srs-button-container">
            <input type="submit" class="submit" value="' . $langArray['delete_btn'] . '">
            <input type="hidden" name="delete_user" value="">
        </div>
        <div class="srs-slope"></div>
    </div>
</form>';
        require_once("includes/footer.php");
    }
} else {
    require_once("includes/header.php");
    echo '<div class="userdel">' . $langArray['error'] . ': ' . $langArray['you_are_not_logged_in'] . '</div><br><br>';
    require_once("includes/footer.php");
}
?>