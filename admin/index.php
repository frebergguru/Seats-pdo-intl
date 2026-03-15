<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();

$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$reservationCount = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$mapData = getMapData($pdo);
$totalSeats = $mapData['max_seats'];

require '../includes/header.php';
renderAdminNav('dashboard');
?>

<div class="admin-page-title"><?php echo $langArray['admin_dashboard']; ?></div>

<div class="admin-stats">
    <div class="admin-stat-box">
        <span class="stat-number"><?php echo (int)$userCount; ?></span>
        <span class="stat-label"><?php echo $langArray['admin_total_users']; ?></span>
    </div>
    <div class="admin-stat-box">
        <span class="stat-number"><?php echo (int)$reservationCount; ?></span>
        <span class="stat-label"><?php echo $langArray['admin_total_reservations']; ?></span>
    </div>
    <div class="admin-stat-box">
        <span class="stat-number"><?php echo (int)$totalSeats; ?></span>
        <span class="stat-label"><?php echo $langArray['admin_total_seats']; ?></span>
    </div>
</div>

<div class="admin-links">
    <a href="users.php" class="admin-link-card"><?php echo $langArray['admin_users']; ?></a>
    <a href="reservations.php" class="admin-link-card"><?php echo $langArray['admin_reservations']; ?></a>
    <a href="map.php" class="admin-link-card"><?php echo $langArray['admin_map_editor']; ?></a>
    <a href="settings.php" class="admin-link-card"><?php echo $langArray['admin_settings']; ?></a>
</div>

<?php require '../includes/footer.php'; ?>
