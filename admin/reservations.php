<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();

// Handle purge all (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purge_all'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlash('error', $langArray['invalid_csrf_token']);
    } else {
        $pdo->beginTransaction();
        try {
            $pdo->exec("DELETE FROM reservations");
            $pdo->exec("UPDATE users SET rseat = NULL");
            $pdo->commit();
            setFlash('success', $langArray['admin_purge_all_done']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('error', $langArray['error_occurred']);
        }
    }
    header("Location: reservations.php");
    exit();
}

// Handle deletion (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_seat'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlash('error', $langArray['invalid_csrf_token']);
    } else {
        $deleteSeat = (int)$_POST['delete_seat'];
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM reservations WHERE taken = :seat")->execute([':seat' => $deleteSeat]);
            $pdo->prepare("UPDATE users SET rseat = NULL WHERE rseat = :seat")->execute([':seat' => $deleteSeat]);
            $pdo->commit();
            setFlash('success', $langArray['admin_reservation_deleted']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('error', $langArray['error_occurred']);
        }
    }
    header("Location: reservations.php");
    exit();
}

// GET
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$stmt = $pdo->query("SELECT r.id, r.taken, u.id as user_id, u.nickname, u.fullname FROM reservations r JOIN users u ON r.user_id = u.id ORDER BY r.taken");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
renderAdminNav('reservations');
?>

<div class="admin-page-title"><?php echo $langArray['admin_reservations']; ?></div>

<?php echo getFlash(); ?>

<?php if (!empty($reservations)): ?>
<div style="text-align:center; margin-bottom:15px;">
    <form method="POST" action="reservations.php" style="display:inline;" onsubmit="return confirm('<?php echo htmlspecialchars($langArray['admin_confirm_purge_all'], ENT_QUOTES, 'UTF-8'); ?>');">
        <input type="hidden" name="purge_all" value="1">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="admin-btn admin-btn-delete"><?php echo $langArray['admin_purge_all']; ?></button>
    </form>
</div>
<?php endif; ?>

<?php if (empty($reservations)): ?>
    <div class="admin-panel"><div class="admin-panel-body" style="text-align:center;color:#999;"><?php echo $langArray['admin_no_reservations']; ?></div></div>
<?php else: ?>
<div class="admin-panel">
    <div class="admin-panel-body" style="padding:0; overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?php echo $langArray['admin_seat']; ?></th>
                    <th><?php echo $langArray['nickname']; ?></th>
                    <th><?php echo $langArray['fullname']; ?></th>
                    <th><?php echo $langArray['admin_actions']; ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $res): ?>
                <tr>
                    <td><strong>#<?php echo (int)$res['taken']; ?></strong></td>
                    <td><?php echo htmlspecialchars($res['nickname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($res['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="admin-actions">
                            <form method="POST" action="reservations.php" onsubmit="return confirm('<?php echo htmlspecialchars($langArray['admin_confirm_delete_reservation'], ENT_QUOTES, 'UTF-8'); ?>');">
                                <input type="hidden" name="delete_seat" value="<?php echo (int)$res['taken']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="admin-btn admin-btn-delete"><?php echo $langArray['admin_delete']; ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
