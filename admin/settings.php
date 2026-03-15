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

<!-- Site Settings -->
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

<!-- Email/SMTP Settings -->
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

<!-- SMTP Test -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_test_email']; ?></div>
    <div class="admin-panel-body">
        <small style="color:#999;"><?php echo $langArray['admin_test_email_hint']; ?></small><br><br>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="test_email_addr"><?php echo $langArray['admin_test_email_address']; ?></label>
                <input type="email" id="test_email_addr" placeholder="test@example.com">
            </div>
            <div class="admin-form-group" style="display:flex; align-items:flex-end;">
                <button type="button" id="sendTestEmail" class="admin-btn"><?php echo $langArray['admin_test_email_send']; ?></button>
            </div>
        </div>
        <div id="testEmailResult" style="margin-top:10px;"></div>
    </div>
</div>

<!-- Security / Password Settings -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_security']; ?></div>
    <div class="admin-panel-body">

        <!-- Password Regex -->
        <div class="admin-form-group">
            <label for="pwd_regex"><?php echo $langArray['admin_pwd_regex']; ?></label>
            <input name="pwd_regex" id="pwd_regex" value="<?php echo esc($v['pwd_regex']); ?>" class="regex-field" style="font-family:monospace;">
            <div class="regex-tester">
                <input type="text" class="regex-test" data-field="pwd_regex" placeholder="<?php echo esc($langArray['admin_regex_test_pwd']); ?>">
                <span class="regex-result"></span>
            </div>
            <button type="button" class="regex-gen-toggle" onclick="$('#genPwd').toggle();"><?php echo $langArray['admin_gen_password']; ?></button>
        </div>
        <div id="genPwd" class="regex-gen-box" style="display:none;">
            <div class="regex-gen-row">
                <label><?php echo $langArray['admin_regex_min_length']; ?></label>
                <input type="number" id="pwdGenMin" value="8" min="1" max="100">
                <label><?php echo $langArray['admin_regex_max_length']; ?></label>
                <input type="number" id="pwdGenMax" value="26" min="1" max="100">
            </div>
            <div class="regex-gen-checks">
                <label><input type="checkbox" class="pwd-opt" data-look="(?=.*[a-z])" checked> <?php echo $langArray['admin_regex_require_lowercase']; ?></label>
                <label><input type="checkbox" class="pwd-opt" data-look="(?=.*[A-Z])" checked> <?php echo $langArray['admin_regex_require_uppercase']; ?></label>
                <label><input type="checkbox" class="pwd-opt" data-look="(?=.*\d)" checked> <?php echo $langArray['admin_regex_require_digit']; ?></label>
                <label><input type="checkbox" class="pwd-opt" data-look="(?=.*[^a-zA-Z\d])" checked> <?php echo $langArray['admin_regex_require_special']; ?></label>
            </div>
            <div class="regex-gen-output" id="pwdGenOut"></div>
            <button type="button" class="admin-btn" onclick="$('#pwd_regex').val($('#pwdGenOut').text()).trigger('input');"><?php echo $langArray['admin_regex_apply']; ?></button>
        </div>

        <!-- Nickname Regex -->
        <div class="admin-form-group">
            <label for="nickname_regex"><?php echo $langArray['admin_nickname_regex']; ?></label>
            <input name="nickname_regex" id="nickname_regex" value="<?php echo esc($v['nickname_regex']); ?>" class="regex-field" style="font-family:monospace;">
            <div class="regex-tester">
                <input type="text" class="regex-test" data-field="nickname_regex" placeholder="<?php echo esc($langArray['admin_regex_test_nick']); ?>">
                <span class="regex-result"></span>
            </div>
            <button type="button" class="regex-gen-toggle" onclick="$('#genNick').toggle();"><?php echo $langArray['admin_gen_nickname']; ?></button>
        </div>
        <div id="genNick" class="regex-gen-box" style="display:none;">
            <div class="regex-gen-row">
                <label><?php echo $langArray['admin_regex_min_length']; ?></label>
                <input type="number" id="nickGenMin" value="4" min="1" max="100">
            </div>
            <div class="regex-gen-checks">
                <label><input type="checkbox" class="nick-opt" data-chars="a-z" checked> a-z</label>
                <label><input type="checkbox" class="nick-opt" data-chars="A-Z" checked> A-Z</label>
                <label><input type="checkbox" class="nick-opt" data-chars="0-9" checked> 0-9</label>
                <label><input type="checkbox" class="nick-opt" data-chars="_" checked> _ (<?php echo $langArray['admin_gen_underscore']; ?>)</label>
                <label><input type="checkbox" class="nick-opt" data-chars="-" checked> - (<?php echo $langArray['admin_gen_hyphen']; ?>)</label>
            </div>
            <div class="regex-gen-output" id="nickGenOut"></div>
            <button type="button" class="admin-btn" onclick="$('#nickname_regex').val($('#nickGenOut').text()).trigger('input');"><?php echo $langArray['admin_regex_apply']; ?></button>
        </div>

        <!-- Fullname Regex -->
        <div class="admin-form-group">
            <label for="fullname_regex"><?php echo $langArray['admin_fullname_regex']; ?></label>
            <input name="fullname_regex" id="fullname_regex" value="<?php echo esc($v['fullname_regex']); ?>" class="regex-field" style="font-family:monospace;">
            <div class="regex-tester">
                <input type="text" class="regex-test" data-field="fullname_regex" placeholder="<?php echo esc($langArray['admin_regex_test_name']); ?>">
                <span class="regex-result"></span>
            </div>
            <button type="button" class="regex-gen-toggle" onclick="$('#genName').toggle();"><?php echo $langArray['admin_gen_fullname']; ?></button>
        </div>
        <div id="genName" class="regex-gen-box" style="display:none;">
            <div class="regex-gen-row">
                <label><?php echo $langArray['admin_gen_min_per_word']; ?></label>
                <input type="number" id="nameGenMin" value="2" min="1" max="50">
            </div>
            <div class="regex-gen-checks">
                <label><input type="checkbox" class="name-opt" data-chars="a-zA-Z" checked> A-Z / a-z</label>
                <label><input type="checkbox" class="name-opt" data-chars="æøåÆØÅÀ-ÖØ-öø-ÿ" checked> <?php echo $langArray['admin_gen_accented']; ?></label>
                <label><input type="checkbox" class="name-opt" data-chars="'" checked> ' (<?php echo $langArray['admin_gen_apostrophe']; ?>)</label>
                <label><input type="checkbox" class="name-opt" data-chars="-" checked> - (<?php echo $langArray['admin_gen_hyphen']; ?>)</label>
            </div>
            <div class="regex-gen-output" id="nameGenOut"></div>
            <button type="button" class="admin-btn" onclick="applyNameRegex();"><?php echo $langArray['admin_regex_apply']; ?></button>
        </div>

        <!-- Fullname Illegal Chars Regex -->
        <div class="admin-form-group">
            <label for="fullname_illegal_chars_regex"><?php echo $langArray['admin_fullname_illegal_regex']; ?></label>
            <input name="fullname_illegal_chars_regex" id="fullname_illegal_chars_regex" value="<?php echo esc($v['fullname_illegal_chars_regex']); ?>" class="regex-field" style="font-family:monospace;">
            <div class="regex-tester">
                <input type="text" class="regex-test" data-field="fullname_illegal_chars_regex" data-invert="1" placeholder="<?php echo esc($langArray['admin_regex_test_illegal']); ?>">
                <span class="regex-result"></span>
            </div>
            <small style="color:#888;"><?php echo $langArray['admin_gen_illegal_auto']; ?></small>
        </div>

    </div>
</div>

<!-- Argon2id Settings -->
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

<script>
$(function() {
    // =====================
    // SMTP Test Email
    // =====================
    $('#sendTestEmail').on('click', function() {
        var email = $('#test_email_addr').val().trim();
        if (!email) return;
        var $btn = $(this);
        var $result = $('#testEmailResult');
        $btn.prop('disabled', true).text(langArray.admin_test_email_sending);
        $result.html('');

        $.ajax({
            type: 'POST',
            url: 'ajax-test-email.php',
            data: {
                test_email: email,
                csrf_token: <?php echo json_encode($_SESSION['csrf_token']); ?>
            },
            dataType: 'json',
            timeout: 30000,
            success: function(res) {
                var cls = res.success ? 'admin-success' : 'admin-error';
                $result.html('<div class="' + cls + '" style="margin:0;">' + $('<span>').text(res.message).html() + '</div>');
            },
            error: function(xhr, status) {
                $result.html('<div class="admin-error" style="margin:0;">Request failed: ' + $('<span>').text(status).html() + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text(langArray.admin_test_email_send);
            }
        });
    });

    // =====================
    // Regex Tester (inline on each field)
    // =====================
    function runTest($input) {
        var fieldId = $input.data('field');
        var regexStr = $('#' + fieldId).val();
        var testVal = $input.val();
        var invert = $input.data('invert');
        var $result = $input.closest('.regex-tester').find('.regex-result');
        if (!testVal) { $result.html(''); return; }
        try {
            var m = regexStr.match(/^\/(.+)\/([gimusy]*)$/);
            var re = m ? new RegExp(m[1], m[2]) : new RegExp(regexStr);
            var ok = re.test(testVal);
            if (invert) ok = !ok;
            $result.html(ok ? '<span style="color:#4CAF50;">\u2714</span>' : '<span style="color:#d9534f;">\u2718</span>');
        } catch(e) {
            $result.html('<span style="color:#d9534f;">\u2718</span>');
        }
    }
    $('.regex-test').on('input', function(){ runTest($(this)); });
    $('.regex-field').on('input', function(){
        var $t = $('.regex-test[data-field="'+$(this).attr('id')+'"]');
        if ($t.val()) runTest($t);
    });

    // =====================
    // Password Regex Generator
    // =====================
    function buildPwdRegex() {
        var min = parseInt($('#pwdGenMin').val())||1, max = parseInt($('#pwdGenMax').val())||100;
        if (max<min) max=min;
        var p = [];
        $('.pwd-opt:checked').each(function(){ p.push($(this).data('look')); });
        $('#pwdGenOut').text('/^'+p.join('')+'.{'+min+','+max+'}$/');
    }
    $('#pwdGenMin,#pwdGenMax').on('input', buildPwdRegex);
    $('.pwd-opt').on('change', buildPwdRegex);
    buildPwdRegex();

    // =====================
    // Nickname Regex Generator
    // =====================
    function buildNickRegex() {
        var min = parseInt($('#nickGenMin').val())||1;
        var chars = [];
        $('.nick-opt:checked').each(function(){ chars.push($(this).data('chars')); });
        var set = chars.join('');
        $('#nickGenOut').text('/^['+set+']{'+min+',}$/');
    }
    $('#nickGenMin').on('input', buildNickRegex);
    $('.nick-opt').on('change', buildNickRegex);
    buildNickRegex();

    // =====================
    // Fullname Regex Generator
    // =====================
    function buildNameRegex() {
        var min = parseInt($('#nameGenMin').val())||1;
        var chars = [];
        $('.name-opt:checked').each(function(){ chars.push($(this).data('chars')); });
        var set = chars.join("\\");
        // escape the apostrophe for the char class
        set = set.replace(/'/g, "\\'");
        var charClass = '['+chars.join('')+']';
        var regex = '/^'+charClass+'{'+min+',}(\\s'+charClass+'{'+min+',})*$/u';
        var illegal = '/[^'+chars.join('')+'\\s]/u';
        $('#nameGenOut').text(regex);
        // Store the illegal version for auto-apply
        $('#nameGenOut').data('illegal', illegal);
    }
    $('#nameGenMin').on('input', buildNameRegex);
    $('.name-opt').on('change', buildNameRegex);
    buildNameRegex();

    // Apply name regex also updates the illegal chars regex automatically
    window.applyNameRegex = function() {
        var regex = $('#nameGenOut').text();
        var illegal = $('#nameGenOut').data('illegal');
        if (regex) {
            $('#fullname_regex').val(regex).trigger('input');
        }
        if (illegal) {
            $('#fullname_illegal_chars_regex').val(illegal).trigger('input');
        }
    };
});
</script>

<?php require '../includes/footer.php'; ?>
