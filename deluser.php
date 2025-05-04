<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/i18n.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validate session and nickname
if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
    require_once("includes/header.php");
    echo '<div class="userdel">' . $langArray['error'] . ': ' . $langArray['you_are_not_logged_in'] . '</div><br><br>';
    require_once("includes/footer.php");
    exit();
}

$nickname = htmlspecialchars($_SESSION['nickname'], ENT_QUOTES, 'UTF-8');

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
} catch (PDOException $e) {
    error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
    require_once("includes/header.php");
    echo '<div class="userdel">' . $langArray['database_error'] . '</div><br><br>';
    require_once("includes/footer.php");
    exit();
}

// CSRF Token Generation and Validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        require_once("includes/header.php");
        echo '<div class="userdel">' . $langArray['error'] . ': ' . $langArray['invalid_csrf_token'] . '</div><br><br>';
        require_once("includes/footer.php");
        exit();
    }
} else {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $password = $_POST['password'] ?? '';

    // Fetch user data
    $stmt = $pdo->prepare('SELECT password, rseat FROM users WHERE lower(nickname) = :nickname');
    $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        require_once("includes/header.php");
        echo '<div class="userdel">' . $langArray['invalid_password'] . '</div><br><br>';
        require_once("includes/footer.php");
        exit();
    }

    // Begin transaction to delete user and reservation
    $pdo->beginTransaction();
    try {
        if (!empty($user['rseat'])) {
            $stmt = $pdo->prepare('DELETE FROM reservations WHERE taken = :reservation_id');
            $stmt->bindValue(':reservation_id', $user['rseat'], PDO::PARAM_INT);
            $stmt->execute();
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE lower(nickname) = :nickname');
        $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
        $stmt->execute();

        $pdo->commit();

        // Destroy session and confirm deletion
        session_destroy();
        require_once("includes/header.php");
        echo '<div class="userdel">' . $langArray['user_is_now_successfully_deleted'] . '</div><br><br>';
        require_once("includes/footer.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Error deleting user: ' . $e->getMessage(), 0);
        require_once("includes/header.php");
        echo '<div class="userdel">' . $langArray['error_occurred'] . '</div><br><br>';
        require_once("includes/footer.php");
        exit();
    }
} else {
    // Display the delete account form
    require_once("includes/header.php");
    echo '<form class="srs-container" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
        <span class="srs-header">' . $langArray['delete_account'] . '</span>
        <div class="srs-content">
            <p><strong>' . $langArray['please_confirm_with_your_password'] . '</strong></p>
            <label for="password" class="srs-lb">' . $langArray['password'] . '</label>
            <input name="password" id="password" type="password" class="srs-tb" required><br>
        </div>
        <div class="srs-footer">
            <div class="srs-button-container">
                <input type="submit" class="submit" value="' . $langArray['delete_btn'] . '">
                <input type="hidden" name="delete_user" value="1">
                <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
            </div>
            <div class="srs-slope"></div>
        </div>
    </form><br><br>';
    require_once("includes/footer.php");
}
?>