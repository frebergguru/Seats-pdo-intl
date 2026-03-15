<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
    $data = exportUserData($pdo, $_SESSION['nickname']);

    if (!$data) {
        header("Location: index.php");
        exit();
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="my-data.json"');
    header('Content-Length: ' . strlen($json));
    echo $json;
    exit();
} catch (PDOException $e) {
    error_log("Data export error: " . $e->getMessage());
    header("Location: index.php");
    exit();
}
