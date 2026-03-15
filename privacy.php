<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/i18n.php';

require 'includes/header.php';
?>
<div class="srs-container" style="max-width:700px;">
    <span class="srs-header"><?php echo $langArray['privacy_policy_title']; ?></span>
    <div class="srs-content" style="padding:15px; line-height:1.8; font-family:Arial,Helvetica,sans-serif; font-size:14px;">

        <h3><?php echo $langArray['privacy_what_we_collect']; ?></h3>
        <ul>
            <li><?php echo $langArray['privacy_collect_name']; ?></li>
            <li><?php echo $langArray['privacy_collect_nickname']; ?></li>
            <li><?php echo $langArray['privacy_collect_email']; ?></li>
            <li><?php echo $langArray['privacy_collect_password']; ?></li>
            <li><?php echo $langArray['privacy_collect_reservation']; ?></li>
            <li><?php echo $langArray['privacy_collect_ip']; ?></li>
        </ul>

        <h3><?php echo $langArray['privacy_why_we_collect']; ?></h3>
        <p><?php echo $langArray['privacy_why_text']; ?></p>

        <h3><?php echo $langArray['privacy_how_long']; ?></h3>
        <p><?php echo $langArray['privacy_how_long_text']; ?></p>

        <h3><?php echo $langArray['privacy_cookies']; ?></h3>
        <p><?php echo $langArray['privacy_cookies_text']; ?></p>

        <h3><?php echo $langArray['privacy_your_rights']; ?></h3>
        <ul>
            <li><?php echo $langArray['privacy_right_access']; ?></li>
            <li><?php echo $langArray['privacy_right_export']; ?></li>
            <li><?php echo $langArray['privacy_right_delete']; ?></li>
        </ul>

        <?php if (!empty($_SESSION['nickname'])): ?>
        <h3><?php echo $langArray['privacy_your_data']; ?></h3>
        <p>
            <a href="export.php" class="submit" style="font-size:14px; padding:6px 15px; height:auto;"><?php echo $langArray['privacy_export_btn']; ?></a>
        </p>
        <?php endif; ?>

    </div>
</div>
<br>
<?php require 'includes/footer.php'; ?>
