<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();

$dbSettings = loadSettings($pdo);

// Handle save (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlash('error', $langArray['invalid_csrf_token']);
    } else {
        $newSettings = [
            'site_description'             => trim($_POST['site_description'] ?? ''),
            'site_keywords'                => trim($_POST['site_keywords'] ?? ''),
            'site_author'                  => trim($_POST['site_author'] ?? ''),
            'default_language'             => trim($_POST['default_language'] ?? 'en'),
            'smtp_server'                  => trim($_POST['smtp_server'] ?? ''),
            'smtp_port'                    => trim($_POST['smtp_port'] ?? '587'),
            'smtp_username'                => trim($_POST['smtp_username'] ?? ''),
            'from_name'                    => trim($_POST['from_name'] ?? ''),
            'from_mail'                    => trim($_POST['from_mail'] ?? ''),
            'mail_subject'                 => trim($_POST['mail_subject'] ?? ''),
            'pwd_regex'                    => $_POST['pwd_regex'] ?? '',
            'nickname_regex'               => $_POST['nickname_regex'] ?? '',
            'fullname_regex'               => $_POST['fullname_regex'] ?? '',
            'fullname_illegal_chars_regex'  => $_POST['fullname_illegal_chars_regex'] ?? '',
            'argon2id_memory_cost'         => (int)($_POST['argon2id_memory_cost'] ?? 65536),
            'argon2id_time_cost'           => (int)($_POST['argon2id_time_cost'] ?? 3),
            'argon2id_threads'             => (int)($_POST['argon2id_threads'] ?? 1),
        ];

        $smtpPwd = $_POST['smtp_password'] ?? '';
        if ($smtpPwd !== '') {
            $newSettings['smtp_password'] = $smtpPwd;
        } elseif (isset($dbSettings['smtp_password'])) {
            $newSettings['smtp_password'] = $dbSettings['smtp_password'];
        }

        try {
            saveSettings($pdo, $newSettings);
            setFlash('success', $langArray['admin_settings_saved']);
        } catch (PDOException $e) {
            error_log("Admin settings save: " . $e->getMessage());
            setFlash('error', $langArray['error_occurred']);
        }
    }
    header("Location: settings.php");
    exit();
}

// GET
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Reload settings after potential save redirect
$dbSettings = loadSettings($pdo);

$v = [
    'site_description'    => $dbSettings['site_description'] ?? $site_description,
    'site_keywords'       => $dbSettings['site_keywords'] ?? $site_keywords,
    'site_author'         => $dbSettings['site_author'] ?? $site_author,
    'default_language'    => $dbSettings['default_language'] ?? $default_language,
    'smtp_server'         => $dbSettings['smtp_server'] ?? $smtp_server,
    'smtp_port'           => $dbSettings['smtp_port'] ?? $smtp_port,
    'smtp_username'       => $dbSettings['smtp_username'] ?? $smtp_username,
    'from_name'           => $dbSettings['from_name'] ?? $from_name,
    'from_mail'           => $dbSettings['from_mail'] ?? $from_mail,
    'mail_subject'        => $dbSettings['mail_subject'] ?? $mail_subject,
    'pwd_regex'           => $dbSettings['pwd_regex'] ?? $pwd_regex,
    'nickname_regex'      => $dbSettings['nickname_regex'] ?? $nickname_regex,
    'fullname_regex'      => $dbSettings['fullname_regex'] ?? $fullname_regex,
    'fullname_illegal_chars_regex' => $dbSettings['fullname_illegal_chars_regex'] ?? $fullname_illegal_chars_regex,
    'argon2id_memory_cost'=> $dbSettings['argon2id_memory_cost'] ?? $argon2id_options['memory_cost'],
    'argon2id_time_cost'  => $dbSettings['argon2id_time_cost'] ?? $argon2id_options['time_cost'],
    'argon2id_threads'    => $dbSettings['argon2id_threads'] ?? $argon2id_options['threads'],
];

function esc($val) { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }

require '../includes/header.php';
renderAdminNav('settings');
?>

<div class="admin-page-title"><?php echo $langArray['admin_settings']; ?></div>

<?php echo getFlash(); ?>

<form method="POST" action="settings.php">

<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_site']; ?></div>
    <div class="admin-panel-body">
        <div class="admin-form-group">
            <label for="site_description"><?php echo $langArray['admin_site_description']; ?></label>
            <input name="site_description" id="site_description" value="<?php echo esc($v['site_description']); ?>">
        </div>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="site_keywords"><?php echo $langArray['admin_site_keywords']; ?></label>
                <input name="site_keywords" id="site_keywords" value="<?php echo esc($v['site_keywords']); ?>">
            </div>
            <div class="admin-form-group">
                <label for="site_author"><?php echo $langArray['admin_site_author']; ?></label>
                <input name="site_author" id="site_author" value="<?php echo esc($v['site_author']); ?>">
            </div>
        </div>
        <div class="admin-form-group">
            <label for="default_language"><?php echo $langArray['admin_default_language']; ?></label>
            <select name="default_language" id="default_language">
                <option value="en" <?php echo $v['default_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="no" <?php echo $v['default_language'] === 'no' ? 'selected' : ''; ?>>Norsk</option>
            </select>
        </div>
    </div>
</div>

<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_email']; ?></div>
    <div class="admin-panel-body">
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="smtp_server"><?php echo $langArray['admin_smtp_server']; ?></label>
                <input name="smtp_server" id="smtp_server" value="<?php echo esc($v['smtp_server']); ?>">
            </div>
            <div class="admin-form-group">
                <label for="smtp_port"><?php echo $langArray['admin_smtp_port']; ?></label>
                <input name="smtp_port" id="smtp_port" value="<?php echo esc($v['smtp_port']); ?>">
            </div>
        </div>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="smtp_username"><?php echo $langArray['admin_smtp_username']; ?></label>
                <input name="smtp_username" id="smtp_username" value="<?php echo esc($v['smtp_username']); ?>">
            </div>
            <div class="admin-form-group">
                <label for="smtp_password"><?php echo $langArray['admin_smtp_password']; ?></label>
                <input name="smtp_password" id="smtp_password" type="password" placeholder="<?php echo esc($langArray['admin_smtp_password_hint']); ?>">
            </div>
        </div>
        <div class="admin-form-group">
            <label for="from_name"><?php echo $langArray['admin_from_name']; ?></label>
            <input name="from_name" id="from_name" value="<?php echo esc($v['from_name']); ?>">
        </div>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="from_mail"><?php echo $langArray['admin_from_email']; ?></label>
                <input name="from_mail" id="from_mail" value="<?php echo esc($v['from_mail']); ?>">
            </div>
            <div class="admin-form-group">
                <label for="mail_subject"><?php echo $langArray['admin_mail_subject']; ?></label>
                <input name="mail_subject" id="mail_subject" value="<?php echo esc($v['mail_subject']); ?>">
            </div>
        </div>
    </div>
</div>

<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_security']; ?></div>
    <div class="admin-panel-body">
        <div class="admin-form-group">
            <label for="pwd_regex"><?php echo $langArray['admin_pwd_regex']; ?></label>
            <input name="pwd_regex" id="pwd_regex" value="<?php echo esc($v['pwd_regex']); ?>">
        </div>
        <div class="admin-form-group">
            <label for="nickname_regex"><?php echo $langArray['admin_nickname_regex']; ?></label>
            <input name="nickname_regex" id="nickname_regex" value="<?php echo esc($v['nickname_regex']); ?>">
        </div>
        <div class="admin-form-group">
            <label for="fullname_regex"><?php echo $langArray['admin_fullname_regex']; ?></label>
            <input name="fullname_regex" id="fullname_regex" value="<?php echo esc($v['fullname_regex']); ?>">
        </div>
        <div class="admin-form-group">
            <label for="fullname_illegal_chars_regex"><?php echo $langArray['admin_fullname_illegal_regex']; ?></label>
            <input name="fullname_illegal_chars_regex" id="fullname_illegal_chars_regex" value="<?php echo esc($v['fullname_illegal_chars_regex']); ?>">
        </div>
    </div>
</div>

<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_argon2id']; ?></div>
    <div class="admin-panel-body">
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="argon2id_memory_cost"><?php echo $langArray['admin_argon2id_memory']; ?></label>
                <input name="argon2id_memory_cost" id="argon2id_memory_cost" type="number" value="<?php echo (int)$v['argon2id_memory_cost']; ?>">
            </div>
            <div class="admin-form-group">
                <label for="argon2id_time_cost"><?php echo $langArray['admin_argon2id_time']; ?></label>
                <input name="argon2id_time_cost" id="argon2id_time_cost" type="number" value="<?php echo (int)$v['argon2id_time_cost']; ?>">
            </div>
        </div>
        <div class="admin-form-group">
            <label for="argon2id_threads"><?php echo $langArray['admin_argon2id_threads']; ?></label>
            <input name="argon2id_threads" id="argon2id_threads" type="number" value="<?php echo (int)$v['argon2id_threads']; ?>">
        </div>
    </div>
</div>

<input type="hidden" name="save_settings" value="1">
<input type="hidden" name="csrf_token" value="<?php echo esc($_SESSION['csrf_token']); ?>">

<div style="text-align:center; margin:15px 0 30px;">
    <button type="submit" class="admin-btn admin-btn-lg"><?php echo $langArray['admin_save']; ?></button>
</div>

</form>

<?php require '../includes/footer.php'; ?>
