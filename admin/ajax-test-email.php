<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

$pdo = getDBConnection();
if (!$pdo || !isAdmin($pdo, $_SESSION['nickname'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

// CSRF check
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => $langArray['invalid_csrf_token']]);
    exit();
}

$testTo = trim($_POST['test_email'] ?? '');
if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => $langArray['you_must_enter_a_valid_email_address']]);
    exit();
}

// Use current saved settings (already loaded by config.php)
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $smtp_server;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$smtp_port;
    $mail->Timeout = 10;

    $mail->setFrom($from_mail, $from_name);
    $mail->addAddress($testTo);

    $mail->isHTML(true);
    $mail->Subject = $mail_subject . ' - Test';
    $mail->Body = '<p>' . htmlspecialchars($langArray['admin_test_email_body'], ENT_QUOTES, 'UTF-8') . '</p>';

    $mail->send();

    echo json_encode(['success' => true, 'message' => $langArray['admin_test_email_sent']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $langArray['admin_test_email_failed'] . ': ' . $mail->ErrorInfo]);
}
