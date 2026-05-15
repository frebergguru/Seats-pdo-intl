<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

header('Content-Type: application/json');

// JSON-safe admin guard. We can't use requireAdmin() directly because it
// 302-redirects on failure, which is the wrong contract for a JSON endpoint.
$pdo = getDBConnection();
if (!$pdo || empty($_SESSION['nickname']) || !isAdmin($pdo, $_SESSION['nickname'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => $langArray['you_are_not_logged_in']]);
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

$ok = sendMail($testTo, $mail_subject . ' - Test', getEmailTemplate('test'), [
    'site_name' => htmlspecialchars($from_name, ENT_QUOTES, 'UTF-8'),
]);
if ($ok) {
    echo json_encode(['success' => true, 'message' => $langArray['admin_test_email_sent']]);
} else {
    // Don't leak SMTP error details to the client; sendMail already logs them.
    echo json_encode(['success' => false, 'message' => $langArray['admin_test_email_failed']]);
}
