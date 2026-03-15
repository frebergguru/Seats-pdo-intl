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
            'email_tpl_reset_en'           => $_POST['email_tpl_reset_en'] ?? '',
            'email_tpl_reset_no'           => $_POST['email_tpl_reset_no'] ?? '',
            'email_tpl_test_en'            => $_POST['email_tpl_test_en'] ?? '',
            'email_tpl_test_no'            => $_POST['email_tpl_test_no'] ?? '',
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
    'email_tpl_reset_en'  => $dbSettings['email_tpl_reset_en'] ?? '',
    'email_tpl_reset_no'  => !empty($dbSettings['email_tpl_reset_no']) ? $dbSettings['email_tpl_reset_no'] : ($dbSettings['email_tpl_reset_en'] ?? ''),
    'email_tpl_test_en'   => $dbSettings['email_tpl_test_en']  ?? '',
    'email_tpl_test_no'   => !empty($dbSettings['email_tpl_test_no'])  ? $dbSettings['email_tpl_test_no']  : ($dbSettings['email_tpl_test_en'] ?? ''),
];

function esc($val) { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }

require '../includes/header.php';
renderAdminNav('settings');
?>

<div class="admin-page-title"><?php echo $langArray['admin_settings']; ?></div>
<?php echo getFlash(); ?>

<form method="POST" action="settings.php">

<!-- ==================== SITE ==================== -->
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

<!-- ==================== EMAIL ==================== -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_email']; ?></div>
    <div class="admin-panel-body">

        <!-- SMTP connection -->
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

        <!-- Sender info -->
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label for="from_name"><?php echo $langArray['admin_from_name']; ?></label>
                <input name="from_name" id="from_name" value="<?php echo esc($v['from_name']); ?>">
            </div>
            <div class="admin-form-group">
                <label for="from_mail"><?php echo $langArray['admin_from_email']; ?></label>
                <input name="from_mail" id="from_mail" value="<?php echo esc($v['from_mail']); ?>">
            </div>
        </div>
        <div class="admin-form-group">
            <label for="mail_subject"><?php echo $langArray['admin_mail_subject']; ?></label>
            <input name="mail_subject" id="mail_subject" value="<?php echo esc($v['mail_subject']); ?>">
        </div>

        <!-- SMTP test (inline) -->
        <div style="border-top:1px solid #444; margin-top:15px; padding-top:15px;">
            <small style="color:#999;"><?php echo $langArray['admin_test_email_hint']; ?></small>
            <div style="display:flex; gap:10px; align-items:flex-end; margin-top:8px; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <label for="test_email_addr" style="font-size:11px;color:#999;text-transform:uppercase;font-weight:bold;letter-spacing:.3px;display:block;margin-bottom:3px;"><?php echo $langArray['admin_test_email_address']; ?></label>
                    <input type="email" id="test_email_addr" placeholder="test@example.com">
                </div>
                <button type="button" id="sendTestEmail" class="admin-btn"><?php echo $langArray['admin_test_email_send']; ?></button>
            </div>
            <div id="testEmailResult" style="margin-top:8px;"></div>
        </div>
    </div>
</div>

<!-- ==================== EMAIL TEMPLATES ==================== -->
<?php
$tplLangs = ['en' => 'English', 'no' => 'Norsk'];
$tplTypes = [
    'reset' => ['label' => $langArray['admin_email_template_reset'], 'placeholders' => ['nickname','reset_link','site_name'], 'rows' => 8],
    'test'  => ['label' => $langArray['admin_email_template_test'],  'placeholders' => ['site_name'], 'rows' => 4],
];
?>
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_email_templates']; ?></div>
    <div class="admin-panel-body">
        <small style="color:#888;"><?php echo $langArray['admin_email_template_help']; ?></small>

        <?php foreach ($tplTypes as $tplKey => $tpl): ?>
        <div style="border-top:1px solid #444; margin-top:15px; padding-top:15px;">
            <label style="display:block; margin-bottom:8px;"><strong><?php echo $tpl['label']; ?></strong></label>

            <!-- Row 1: Language + View mode -->
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:6px;">
                <?php foreach ($tplLangs as $lc => $ln): ?>
                    <button type="button" class="email-tpl-lang-tab <?php echo $lc === 'en' ? 'active' : ''; ?>" data-tpl="<?php echo $tplKey; ?>" data-lang="<?php echo $lc; ?>"><?php echo $ln; ?></button>
                <?php endforeach; ?>
                <span class="email-tpl-sep">|</span>
                <button type="button" class="email-tpl-tab active" data-target="<?php echo $tplKey; ?>" data-mode="code"><?php echo $langArray['admin_tpl_code']; ?></button>
                <button type="button" class="email-tpl-tab" data-target="<?php echo $tplKey; ?>" data-mode="preview"><?php echo $langArray['admin_tpl_preview']; ?></button>
            </div>

            <!-- Row 2: Insert placeholders -->
            <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:6px;">
                <span style="color:#666; font-size:11px; align-self:center; margin-right:2px;">Insert:</span>
                <?php foreach ($tpl['placeholders'] as $ph): ?>
                    <button type="button" class="email-tpl-insert" data-tpl="<?php echo $tplKey; ?>" data-placeholder="{{<?php echo $ph; ?>}}"><?php echo $ph; ?></button>
                <?php endforeach; ?>
            </div>

            <!-- Textareas (one per language) -->
            <?php foreach ($tplLangs as $lc => $ln):
                $fn = 'email_tpl_' . $tplKey . '_' . $lc;
            ?>
                <textarea name="<?php echo $fn; ?>" id="<?php echo $fn; ?>" class="email-tpl-code" data-tpl="<?php echo $tplKey; ?>" data-lang="<?php echo $lc; ?>" rows="<?php echo $tpl['rows']; ?>" <?php echo $lc !== 'en' ? 'style="display:none;"' : ''; ?>><?php echo esc($v[$fn]); ?></textarea>
            <?php endforeach; ?>

            <div class="email-tpl-preview" id="preview_<?php echo $tplKey; ?>" style="display:none;"></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ==================== SECURITY ==================== -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_settings_security']; ?></div>
    <div class="admin-panel-body">

        <?php
        // Regex fields config: id, label, test placeholder, generator id (null = none)
        $regexFields = [
            ['pwd_regex',    $langArray['admin_pwd_regex'],    $langArray['admin_regex_test_pwd'],     'genPwd',  $langArray['admin_gen_password']],
            ['nickname_regex', $langArray['admin_nickname_regex'], $langArray['admin_regex_test_nick'], 'genNick', $langArray['admin_gen_nickname']],
            ['fullname_regex', $langArray['admin_fullname_regex'], $langArray['admin_regex_test_name'], 'genName', $langArray['admin_gen_fullname']],
            ['fullname_illegal_chars_regex', $langArray['admin_fullname_illegal_regex'], $langArray['admin_regex_test_illegal'], null, null],
        ];
        foreach ($regexFields as $rf):
            list($id, $label, $testPh, $genId, $genLabel) = $rf;
            $isIllegal = ($id === 'fullname_illegal_chars_regex');
        ?>
        <div class="admin-form-group">
            <label for="<?php echo $id; ?>"><?php echo $label; ?></label>
            <input name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo esc($v[$id]); ?>" class="regex-field" style="font-family:monospace;">
            <div class="regex-tester">
                <input type="text" class="regex-test" data-field="<?php echo $id; ?>" <?php echo $isIllegal ? 'data-invert="1"' : ''; ?> placeholder="<?php echo esc($testPh); ?>">
                <span class="regex-result"></span>
            </div>
            <?php if ($genId): ?>
                <button type="button" class="regex-gen-toggle" onclick="$('#<?php echo $genId; ?>').toggle();"><?php echo $genLabel; ?></button>
            <?php else: ?>
                <small style="color:#888;"><?php echo $langArray['admin_gen_illegal_auto']; ?></small>
            <?php endif; ?>
        </div>

        <?php if ($id === 'pwd_regex'): ?>
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
        <?php elseif ($id === 'nickname_regex'): ?>
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
        <?php elseif ($id === 'fullname_regex'): ?>
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
        <?php endif; ?>

        <?php endforeach; ?>
    </div>
</div>

<!-- ==================== HASHING ==================== -->
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

<!-- ==================== SAVE ==================== -->
<input type="hidden" name="save_settings" value="1">
<input type="hidden" name="csrf_token" value="<?php echo esc($_SESSION['csrf_token']); ?>">

<div style="text-align:center; margin:15px 0 30px;">
    <button type="submit" class="admin-btn admin-btn-lg"><?php echo $langArray['admin_save']; ?></button>
</div>

</form>

<script>
$(function() {
    // =====================
    // SMTP Test
    // =====================
    $('#sendTestEmail').on('click', function() {
        var email = $('#test_email_addr').val().trim();
        if (!email) return;
        var $btn = $(this), $r = $('#testEmailResult');
        $btn.prop('disabled', true).text(langArray.admin_test_email_sending);
        $r.html('');
        $.ajax({
            type: 'POST', url: 'ajax-test-email.php', dataType: 'json', timeout: 30000,
            data: { test_email: email, csrf_token: <?php echo json_encode($_SESSION['csrf_token']); ?> },
            success: function(res) { $r.html('<div class="'+(res.success?'admin-success':'admin-error')+'" style="margin:0;">'+$('<span>').text(res.message).html()+'</div>'); },
            error: function(x,s) { $r.html('<div class="admin-error" style="margin:0;">'+$('<span>').text(s).html()+'</div>'); },
            complete: function() { $btn.prop('disabled', false).text(langArray.admin_test_email_send); }
        });
    });

    // =====================
    // Regex Tester
    // =====================
    function runTest($i) {
        var f=$i.data('field'), r=$('#'+f).val(), t=$i.val(), inv=$i.data('invert'), $r=$i.closest('.regex-tester').find('.regex-result');
        if(!t){$r.html('');return;}
        try{var m=r.match(/^\/(.+)\/([gimusy]*)$/),re=m?new RegExp(m[1],m[2]):new RegExp(r),ok=re.test(t);if(inv)ok=!ok;
        $r.html(ok?'<span style="color:#4CAF50;">\u2714</span>':'<span style="color:#d9534f;">\u2718</span>');}catch(e){$r.html('<span style="color:#d9534f;">\u2718</span>');}
    }
    $('.regex-test').on('input',function(){runTest($(this));});
    $('.regex-field').on('input',function(){var $t=$('.regex-test[data-field="'+$(this).attr('id')+'"]');if($t.val())runTest($t);});

    // =====================
    // Regex Generators
    // =====================
    function buildPwdRegex(){var n=parseInt($('#pwdGenMin').val())||1,x=parseInt($('#pwdGenMax').val())||100;if(x<n)x=n;var p=[];$('.pwd-opt:checked').each(function(){p.push($(this).data('look'));});$('#pwdGenOut').text('/^'+p.join('')+'.{'+n+','+x+'}$/');}
    $('#pwdGenMin,#pwdGenMax').on('input',buildPwdRegex);$('.pwd-opt').on('change',buildPwdRegex);buildPwdRegex();

    function buildNickRegex(){var n=parseInt($('#nickGenMin').val())||1,c=[];$('.nick-opt:checked').each(function(){c.push($(this).data('chars'));});$('#nickGenOut').text('/^['+c.join('')+']{'+n+',}$/');}
    $('#nickGenMin').on('input',buildNickRegex);$('.nick-opt').on('change',buildNickRegex);buildNickRegex();

    function buildNameRegex(){var n=parseInt($('#nameGenMin').val())||1,c=[];$('.name-opt:checked').each(function(){c.push($(this).data('chars'));});var cc='['+c.join('')+']';$('#nameGenOut').text('/^'+cc+'{'+n+',}(\\s'+cc+'{'+n+',})*$/u');$('#nameGenOut').data('illegal','/[^'+c.join('')+'\\s]/u');}
    $('#nameGenMin').on('input',buildNameRegex);$('.name-opt').on('change',buildNameRegex);buildNameRegex();
    window.applyNameRegex=function(){var r=$('#nameGenOut').text(),i=$('#nameGenOut').data('illegal');if(r)$('#fullname_regex').val(r).trigger('input');if(i)$('#fullname_illegal_chars_regex').val(i).trigger('input');};

    // =====================
    // Email Template Editor
    // =====================
    var sampleVars = { nickname:'JohnDoe', reset_link:'https://example.com/forgot.php?nickname=johndoe&key=abc123', site_name:<?php echo json_encode($from_name); ?> };
    var activeLang = {};
    $('.email-tpl-lang-tab.active').each(function(){ activeLang[$(this).data('tpl')]=$(this).data('lang'); });

    function getActiveTa(t){ return $('#email_tpl_'+t+'_'+(activeLang[t]||'en')); }

    function renderPreview(t){
        var h=getActiveTa(t).val();
        $.each(sampleVars,function(k,v){h=h.split('{{'+k+'}}').join(v);});
        $('#preview_'+t).html('<div class="email-tpl-preview-frame">'+h+'</div>');
    }

    $('.email-tpl-lang-tab').on('click',function(){
        var t=$(this).data('tpl'),l=$(this).data('lang');activeLang[t]=l;
        $('.email-tpl-lang-tab[data-tpl="'+t+'"]').removeClass('active');$(this).addClass('active');
        var ip=$('#preview_'+t).is(':visible');$('textarea[data-tpl="'+t+'"]').hide();
        if(!ip)getActiveTa(t).show(); else renderPreview(t);
    });

    $('.email-tpl-tab').on('click',function(){
        var t=$(this).data('target'),m=$(this).data('mode');
        $('.email-tpl-tab[data-target="'+t+'"]').removeClass('active');$(this).addClass('active');
        if(m==='preview'){renderPreview(t);$('textarea[data-tpl="'+t+'"]').hide();$('#preview_'+t).show();}
        else{$('#preview_'+t).hide();getActiveTa(t).show();}
    });

    $('.email-tpl-insert').on('click',function(){
        var t=$(this).data('tpl'),ph=$(this).data('placeholder'),$ta=getActiveTa(t),ta=$ta[0];
        if(!$ta.is(':visible')){$('textarea[data-tpl="'+t+'"]').hide();$ta.show();$('#preview_'+t).hide();$('.email-tpl-tab[data-target="'+t+'"][data-mode="code"]').addClass('active');$('.email-tpl-tab[data-target="'+t+'"][data-mode="preview"]').removeClass('active');}
        var s=ta.selectionStart,e=ta.selectionEnd,v=$ta.val();$ta.val(v.substring(0,s)+ph+v.substring(e));ta.setSelectionRange(s+ph.length,s+ph.length);$ta.focus();
    });
});
</script>

<?php require '../includes/footer.php'; ?>
