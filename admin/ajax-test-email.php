<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

header('Content-Type: application/json');

if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

$pdo = getDBConnection();
if (!$pdo || !isAdmin($pdo, $_SESSION['nickname'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => $langArray['invalid_csrf_token']]);
    exit();
}

$testTo = trim($_POST['test_email'] ?? '');
if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => $langArray['you_must_enter_a_valid_email_address']]);
    exit();
}

try {
    sendMail($testTo, $mail_subject . ' - Test', getEmailTemplate('test'), [
        'site_name' => htmlspecialchars($from_name, ENT_QUOTES, 'UTF-8'),
    ]);
    echo json_encode(['success' => true, 'message' => $langArray['admin_test_email_sent']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $langArray['admin_test_email_failed'] . ': ' . $e->getMessage()]);
}
