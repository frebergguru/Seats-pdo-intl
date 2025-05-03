<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

session_start();

// CSRF Token Generation
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate CSRF Token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Invalid CSRF token');
    }
}

// Validate and sanitize input
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$email) {
    exit('Invalid email address');
}

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($email)) {
        // Rate limiting
        if (!isset($_SESSION['email_attempts'])) {
            $_SESSION['email_attempts'] = 0;
        }
        if ($_SESSION['email_attempts'] >= 5) {
            exit('Too many email attempts. Please try again later.');
        }
        $_SESSION['email_attempts']++;

        // Check if email exists
        $stmt = $pdo->prepare("SELECT nickname FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            exit('Email address not found');
        }

        $nickname = $user['nickname'];
        $randomkey = bin2hex(random_bytes(16)); // Generate a secure random token
        $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Update the database with the reset token and expiration
        $stmt = $pdo->prepare("UPDATE users SET forgottoken = :randomkey, token_expiration = :expiration WHERE nickname = :nickname");
        $stmt->bindValue(':randomkey', $randomkey, PDO::PARAM_STR);
        $stmt->bindValue(':expiration', $tokenExpiration, PDO::PARAM_STR);
        $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
        $stmt->execute();

        // Send the reset email
        $from_name = htmlspecialchars(trim($from_name), ENT_QUOTES, 'UTF-8');
        $from_mail = filter_var($from_mail, FILTER_VALIDATE_EMAIL);
        if (!$from_mail) {
            error_log('Invalid sender email address: ' . $from_mail);
            exit('Invalid sender email address');
        }

        $mailheaders = "From: {$from_name} <{$from_mail}>\r\n";
        $mailheaders .= "X-Mailer: Seat Reservation/2.0";
        $linkPath = '/forgot.php';
        $baseUrl = 'https://' . $_SERVER['SERVER_NAME'] . $linkPath;
        $resetLink = $baseUrl . '?nickname=' . urlencode($nickname) . '&key=' . urlencode($randomkey);
        $mailmsg = $langArray['email_change_password_body_hi'] . " " . htmlspecialchars($nickname) . "\n\n" .
            $langArray['email_change_password_body_link'] . "\n\n" .
            $resetLink;

        mail($email, $langArray['password_reset_subject'], $mailmsg, $mailheaders);

        echo '<div>' . $langArray['email_sent_instruction_page_text'] . '</div>';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nickname'], $_GET['key'])) {
        $nickname = htmlspecialchars($_GET['nickname']);
        $key = htmlspecialchars($_GET['key']);

        // Validate the token
        $stmt = $pdo->prepare("SELECT forgottoken, token_expiration FROM users WHERE nickname = :nickname");
        $stmt->bindValue(':nickname', $nickname, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['forgottoken'] !== $key || strtotime($user['token_expiration']) < time()) {
            exit('Invalid or expired token');
        }

        // Display the password reset form
        echo '<form method="POST" action="reset_password.php">
            <input type="hidden" name="nickname" value="' . htmlspecialchars($nickname) . '">
            <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
            <label for="password">' . $langArray['password'] . '</label>
            <input type="password" name="password" id="password" required>
            <label for="password2">' . $langArray['repeat_password'] . '</label>
            <input type="password" name="password2" id="password2" required>
            <button type="submit">' . $langArray['change_password_button'] . '</button>
        </form>';
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    exit('An error occurred. Please try again later.');
}
?>