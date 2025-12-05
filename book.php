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

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

session_start();

// Validate session and nickname
if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
    header("Location: login.php");
    exit();
}

$nickname = htmlspecialchars($_SESSION['nickname'], ENT_QUOTES, 'UTF-8');

// Validate and sanitize seat ID
$seat = filter_input(INPUT_GET, 'seatid', FILTER_VALIDATE_INT);
if (!$seat || $seat <= 0) {
    require_once 'includes/header.php';
    print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
        <div class="srs-content">
        ' . $langArray['invalid_seat_selected'] . '
        </div><br><br><br>';
    require_once 'includes/footer.php';
    exit();
}

// Check if the seat exists in the map
$mapData = getMapData();
$maxseats = $mapData['max_seats'];
if ($seat > $maxseats) {
    require_once 'includes/header.php';
    print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
        <div class="srs-content">
        ' . $langArray['the_seat_you_have_selected_does_not_exist'] . '
        </div><br><br><br>';
    require_once 'includes/footer.php';
    exit();
}

try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);

    // Check if the seat is already reserved
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE taken = :seatid");
    $stmt->bindValue(':seatid', $seat, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        require_once 'includes/header.php';
        print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
            <div class="srs-content">
            ' . $langArray['the_seat_you_have_selected_is_already_reserved_by_someone_else'] . '
            </div><br><br><br>';
        require_once 'includes/footer.php';
        exit();
    }

    // Get user ID and check if the user already has a reserved seat
    $stmt = $pdo->prepare("SELECT id, rseat FROM users WHERE lower(nickname) = :nickname");
    $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        require_once 'includes/header.php';
        print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
            <div class="srs-content">
            ' . $langArray['user_not_found'] . '
            </div><br><br><br>';
        require_once 'includes/footer.php';
        exit();
    }

    if (!empty($user['rseat'])) {
        require_once 'includes/header.php';
        print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
            <div class="srs-content">
            ' . $langArray['you_can_only_reserve_one_seat'] . '
            </div><br><br><br>';
        require_once 'includes/footer.php';
        exit();
    }

    // Reserve the seat
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE users SET rseat = :rseat WHERE id = :userid");
    $stmt->bindValue(':rseat', $seat, PDO::PARAM_INT);
    $stmt->bindValue(':userid', $user['id'], PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("INSERT INTO reservations (taken, user_id) VALUES (:seatid, :userid)");
    $stmt->bindValue(':seatid', $seat, PDO::PARAM_INT);
    $stmt->bindValue(':userid', $user['id'], PDO::PARAM_INT);
    $stmt->execute();

    $pdo->commit();

    // Construct the redirect URL
    $redirectUrl = filter_var(dirname($_SERVER['REQUEST_URI']), FILTER_SANITIZE_URL);

    // Ensure the redirect URL is within the same domain
    $redirectUrl = rtrim($redirectUrl, '/') . '/index.php'; // Redirect to a safe default page

    // Perform the redirection
    header("Location: " . $redirectUrl);
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($langArray['could_not_connect_to_db_server'] . ' ' . $e->getMessage(), 0);
    require_once 'includes/header.php';
    print '<span class="srs-header">' . $langArray['an_error_has_occured'] . '</span>
        <div class="srs-content">
        ' . $langArray['database_error'] . '
        </div><br><br><br>';
    require_once 'includes/footer.php';
    exit();
}
?>