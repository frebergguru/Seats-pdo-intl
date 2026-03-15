<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();

// Handle deletion (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlash('error', $langArray['invalid_csrf_token']);
    } else {
        $deleteId = (int)$_POST['delete_user_id'];
        $stmt = $pdo->prepare("SELECT nickname FROM users WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser && mb_strtolower($targetUser['nickname']) === mb_strtolower($_SESSION['nickname'])) {
            setFlash('error', $langArray['admin_cannot_delete_self']);
        } elseif ($targetUser) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("DELETE FROM reservations WHERE user_id = :id")->execute([':id' => $deleteId]);
                $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $deleteId]);
                $pdo->commit();
                setFlash('success', $langArray['admin_user_deleted']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                setFlash('error', $langArray['error_occurred']);
            }
        }
    }
    header("Location: users.php");
    exit();
}

// GET
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$stmt = $pdo->query("SELECT id, fullname, nickname, email, role, rseat FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
renderAdminNav('users');
?>

<div class="admin-page-title"><?php echo $langArray['admin_users']; ?></div>

<?php echo getFlash(); ?>

<div style="text-align:center; margin-bottom:15px;">
    <a href="user_edit.php" class="admin-btn admin-btn-lg"><?php echo $langArray['admin_add_user']; ?></a>
</div>

<?php if (empty($users)): ?>
    <div class="admin-panel"><div class="admin-panel-body" style="text-align:center;color:#999;"><?php echo $langArray['admin_no_users']; ?></div></div>
<?php else: ?>
<div class="admin-panel">
    <div class="admin-panel-body" style="padding:0; overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?php echo $langArray['admin_id']; ?></th>
                    <th><?php echo $langArray['fullname']; ?></th>
                    <th><?php echo $langArray['nickname']; ?></th>
                    <th><?php echo $langArray['email']; ?></th>
                    <th><?php echo $langArray['admin_role']; ?></th>
                    <th><?php echo $langArray['admin_seat']; ?></th>
                    <th><?php echo $langArray['admin_actions']; ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo (int)$user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo htmlspecialchars($user['nickname'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="admin-badge <?php echo $user['role'] === 'admin' ? 'admin-badge-admin' : 'admin-badge-user'; ?>"><?php echo $user['role'] === 'admin' ? $langArray['admin_role_admin'] : $langArray['admin_role_user']; ?></span></td>
                    <td><?php echo $user['rseat'] ? (int)$user['rseat'] : '-'; ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="user_edit.php?id=<?php echo (int)$user['id']; ?>" class="admin-btn"><?php echo $langArray['admin_edit']; ?></a>
                            <form method="POST" action="users.php" onsubmit="return confirm('<?php echo htmlspecialchars($langArray['admin_confirm_delete_user'], ENT_QUOTES, 'UTF-8'); ?>');">
                                <input type="hidden" name="delete_user_id" value="<?php echo (int)$user['id']; ?>">
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
