<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();
$message = '';
$isEdit = false;
$userData = ['fullname' => '', 'nickname' => '', 'email' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['id'])) {
    $isEdit = true;
    $userId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT id, fullname, nickname, email, role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userData) {
        header("Location: users.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $message = '<div class="admin-error">' . $langArray['invalid_csrf_token'] . '</div>';
    } else {
        $isEdit = isset($_POST['user_id']) && $_POST['user_id'] !== '';
        $userId = $isEdit ? (int)$_POST['user_id'] : 0;

        $fullname = trim($_POST['fullname'] ?? '');
        $nick = trim($_POST['nickname'] ?? '');
        $emailInput = mb_strtolower(trim($_POST['email'] ?? ''));
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        $password = $_POST['password'] ?? '';

        $userData = ['fullname' => $fullname, 'nickname' => $nick, 'email' => $emailInput, 'role' => $role];
        if ($isEdit) $userData['id'] = $userId;

        $errors = [];

        if (empty($fullname) || !preg_match($fullname_regex, $fullname))
            $errors[] = $langArray['you_must_enter_a_name'];
        if (empty($nick) || !preg_match($nickname_regex, $nick))
            $errors[] = $langArray['you_must_enter_a_nickname'];
        if (empty($emailInput) || !filter_var($emailInput, FILTER_VALIDATE_EMAIL))
            $errors[] = $langArray['you_must_enter_a_valid_email_address'];
        if (!$isEdit && empty($password))
            $errors[] = $langArray['you_must_enter_a_password'];
        if (!empty($password) && !preg_match($pwd_regex, $password))
            $errors[] = $langArray['the_password_contains_illegal_characters'];

        if (empty($errors)) {
            $eq = "SELECT id FROM users WHERE lower(email) = lower(:email)";
            if ($isEdit) $eq .= " AND id != :eid";
            $stmt = $pdo->prepare($eq);
            $stmt->bindValue(':email', $emailInput);
            if ($isEdit) $stmt->bindValue(':eid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch()) $errors[] = $langArray['the_email_address_already_exists'];
        }
        if (empty($errors)) {
            $nq = "SELECT id FROM users WHERE lower(nickname) = lower(:nickname)";
            if ($isEdit) $nq .= " AND id != :nid";
            $stmt = $pdo->prepare($nq);
            $stmt->bindValue(':nickname', $nick);
            if ($isEdit) $stmt->bindValue(':nid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch()) $errors[] = $langArray['nickname_already_exists'];
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    if (!empty($password)) {
                        $hp = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
                        $stmt = $pdo->prepare("UPDATE users SET fullname=:fn, nickname=:nn, email=:em, role=:ro, password=:pw WHERE id=:id");
                        $stmt->bindValue(':pw', $hp);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET fullname=:fn, nickname=:nn, email=:em, role=:ro WHERE id=:id");
                    }
                    $stmt->bindValue(':fn', $fullname);
                    $stmt->bindValue(':nn', $nick);
                    $stmt->bindValue(':em', $emailInput);
                    $stmt->bindValue(':ro', $role);
                    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
                    $stmt->execute();
                    if (mb_strtolower($nick) === mb_strtolower($_SESSION['nickname'])) {
                        $_SESSION['role'] = $role;
                        $_SESSION['nickname'] = $nick;
                    }
                    setFlash('success', $langArray['admin_user_updated']);
                    header("Location: users.php");
                    exit();
                } else {
                    $hp = password_hash($password, PASSWORD_ARGON2ID, $argon2id_options);
                    $stmt = $pdo->prepare("INSERT INTO users (fullname,nickname,email,password,role) VALUES (:fn,:nn,:em,:pw,:ro)");
                    $stmt->execute([':fn'=>$fullname, ':nn'=>$nick, ':em'=>$emailInput, ':pw'=>$hp, ':ro'=>$role]);
                    setFlash('success', $langArray['admin_user_created']);
                    header("Location: users.php");
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Admin user save: " . $e->getMessage());
                $message = '<div class="admin-error">' . $langArray['error_occurred'] . '</div>';
            }
        } else {
            $message = '<div class="admin-error">' . implode('<br>', array_map(function($e) { return htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); }, $errors)) . '</div>';
        }
    }
}

// Always generate a fresh token for the form display
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

require '../includes/header.php';
renderAdminNav('users');
?>

<div class="admin-page-title"><?php echo $isEdit ? $langArray['admin_edit_user'] : $langArray['admin_add_user']; ?></div>

<?php echo $message; ?>

<div class="admin-panel" style="max-width:600px;">
    <div class="admin-panel-body">
        <form method="POST" action="user_edit.php">
            <?php if ($isEdit): ?>
                <input type="hidden" name="user_id" value="<?php echo (int)$userData['id']; ?>">
            <?php endif; ?>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="fullname"><?php echo $langArray['fullname']; ?></label>
                    <input name="fullname" id="fullname" value="<?php echo esc($userData['fullname']); ?>">
                </div>
                <div class="admin-form-group">
                    <label for="nickname"><?php echo $langArray['nickname']; ?></label>
                    <input name="nickname" id="nickname" value="<?php echo esc($userData['nickname']); ?>">
                </div>
            </div>

            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="email"><?php echo $langArray['email']; ?></label>
                    <input name="email" id="email" value="<?php echo esc($userData['email']); ?>">
                </div>
                <div class="admin-form-group">
                    <label for="role"><?php echo $langArray['admin_role']; ?></label>
                    <select name="role" id="role">
                        <option value="user" <?php echo ($userData['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>><?php echo $langArray['admin_role_user']; ?></option>
                        <option value="admin" <?php echo ($userData['role'] ?? 'user') === 'admin' ? 'selected' : ''; ?>><?php echo $langArray['admin_role_admin']; ?></option>
                    </select>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="password"><?php echo $langArray['password']; ?></label>
                <input name="password" id="password" type="password">
                <?php if ($isEdit): ?>
                    <small><?php echo $langArray['admin_leave_blank_keep']; ?></small>
                <?php endif; ?>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo esc($_SESSION['csrf_token']); ?>">

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-lg"><?php echo $langArray['admin_save']; ?></button>
                <a href="users.php" class="admin-btn admin-btn-secondary admin-btn-lg"><?php echo $langArray['admin_back']; ?></a>
            </div>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
