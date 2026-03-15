<?php
/*
 * This file is part of Seats-pdo-intl.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Generate a secure random key.
 */
function genRandomKey($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Create a PDO database connection.
 * Returns null on failure instead of throwing.
 */
function getDBConnection()
{
    global $dsn, $db_options;
    try {
        return new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
    } catch (PDOException $e) {
        error_log("DB connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Load all settings from the database as key => value array.
 * Returns empty array on failure (table may not exist yet).
 */
function loadSettings($pdo)
{
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Save settings to the database (delete all then insert).
 */
function saveSettings($pdo, $settings)
{
    $pdo->beginTransaction();
    try {
        $pdo->exec("DELETE FROM settings");
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val)");
        foreach ($settings as $key => $val) {
            $stmt->execute([':key' => $key, ':val' => $val]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Reads and parses the seat map from the database.
 */
function getMapData($pdo = null)
{
    if ($pdo === null) {
        $pdo = getDBConnection();
    }

    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT map_data FROM seatmap ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['map_data'])) {
                $data = $row['map_data'];
                $rows = preg_split('/\r\n|\r|\n/', trim($data));
                $maxSeats = substr_count($data, "#");
                return ['grid' => $rows, 'max_seats' => $maxSeats];
            }
        } catch (PDOException $e) {
            error_log("Failed to load map from DB: " . $e->getMessage());
        }
    }

    return ['grid' => [], 'max_seats' => 0];
}

/**
 * Save map data to the database (upsert).
 */
function saveMapData($pdo, $mapData)
{
    $stmt = $pdo->query("SELECT id FROM seatmap ORDER BY id DESC LIMIT 1");
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if (DB_DRIVER === 'pgsql') {
            $stmt = $pdo->prepare("UPDATE seatmap SET map_data = :map_data, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        } else {
            $stmt = $pdo->prepare("UPDATE seatmap SET map_data = :map_data WHERE id = :id");
        }
        $stmt->bindValue(':map_data', $mapData, PDO::PARAM_STR);
        $stmt->bindValue(':id', $existing['id'], PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare("INSERT INTO seatmap (map_data) VALUES (:map_data)");
        $stmt->bindValue(':map_data', $mapData, PDO::PARAM_STR);
    }
    $stmt->execute();
}

/**
 * Check if a user has the admin role.
 */
function isAdmin($pdo, $nickname)
{
    if (empty($nickname)) return false;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE lower(nickname) = :nickname");
    $stmt->bindValue(':nickname', mb_strtolower($nickname), PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row && $row['role'] === 'admin');
}

/**
 * Guard function for admin pages. Redirects non-admins.
 * Returns the PDO connection for reuse.
 */
function requireAdmin()
{
    global $dsn, $db_options;

    if (!isset($_SESSION['nickname']) || empty($_SESSION['nickname'])) {
        header("Location: ../login.php");
        exit();
    }

    try {
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $db_options);
    } catch (PDOException $e) {
        error_log("Admin DB connection failed: " . $e->getMessage());
        die("Database error.");
    }

    if (!isAdmin($pdo, $_SESSION['nickname'])) {
        header("Location: ../index.php");
        exit();
    }

    return $pdo;
}

/**
 * Set a flash message to display after redirect.
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message. Returns HTML or empty string.
 */
function getFlash()
{
    if (!isset($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = $f['type'] === 'success' ? 'admin-success' : 'admin-error';
    return '<div class="' . $cls . '">' . htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8') . '</div>';
}

/**
 * Send no-cache headers to prevent stale CSRF tokens on browser back.
 */
function noCacheHeaders()
{
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
}

/**
 * Render the admin navigation bar.
 */
function renderAdminNav($activePage = '')
{
    global $langArray;
    $pages = [
        'dashboard'    => ['url' => 'index.php',        'label' => $langArray['admin_dashboard']],
        'users'        => ['url' => 'users.php',        'label' => $langArray['admin_users']],
        'reservations' => ['url' => 'reservations.php', 'label' => $langArray['admin_reservations']],
        'map'          => ['url' => 'map.php',          'label' => $langArray['admin_map_editor']],
        'settings'     => ['url' => 'settings.php',     'label' => $langArray['admin_settings']],
    ];

    echo '<div class="admin-nav">';
    $links = [];
    foreach ($pages as $key => $page) {
        $cls = ($key === $activePage) ? ' class="active"' : '';
        $links[] = '<a href="' . $page['url'] . '"' . $cls . '>' . htmlspecialchars($page['label'], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    $links[] = '<a href="../index.php">' . htmlspecialchars($langArray['admin_back_to_site'], ENT_QUOTES, 'UTF-8') . '</a>';
    echo implode(' | ', $links);
    echo '</div>';
}

/**
 * Get client IP address.
 */
function getClientIP()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check if an action is rate-limited for the current IP.
 * Returns true if allowed, false if blocked.
 *
 * Limits: login=5/15min, register=3/15min, forgot=3/15min
 */
function checkRateLimit($pdo, $action)
{
    $limits = [
        'login'    => ['max' => 5,  'window' => 900],
        'register' => ['max' => 3,  'window' => 900],
        'forgot'   => ['max' => 3,  'window' => 900],
    ];

    if (!isset($limits[$action])) return true;

    $ip = getClientIP();
    $window = $limits[$action]['window'];
    $max = $limits[$action]['max'];

    // Cleanup old records (older than 1 hour)
    try {
        if (DB_DRIVER === 'pgsql') {
            $pdo->exec("DELETE FROM rate_limits WHERE attempted_at < NOW() - INTERVAL '1 hour'");
        } else {
            $pdo->exec("DELETE FROM rate_limits WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        }
    } catch (PDOException $e) {
        // Table may not exist yet
        return true;
    }

    // Count recent attempts
    try {
        $cutoff = date('Y-m-d H:i:s', time() - $window);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip_address = :ip AND action = :action AND attempted_at > :cutoff");
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() < $max;
    } catch (PDOException $e) {
        return true;
    }
}

/**
 * Record a rate-limited action attempt.
 */
function recordRateAttempt($pdo, $action)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, action) VALUES (:ip, :action)");
        $stmt->execute([':ip' => getClientIP(), ':action' => $action]);
    } catch (PDOException $e) {
        // Table may not exist yet
    }
}

/**
 * Export user data as an associative array (for GDPR data export).
 */
function exportUserData($pdo, $nickname)
{
    $stmt = $pdo->prepare("SELECT id, fullname, nickname, email, role, rseat, language, privacy_consent FROM users WHERE lower(nickname) = :nick");
    $stmt->execute([':nick' => mb_strtolower($nickname)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return null;

    // Remove password hash from export
    unset($user['password']);

    // Get reservation
    if ($user['rseat']) {
        $stmt = $pdo->prepare("SELECT taken, id FROM reservations WHERE user_id = :uid");
        $stmt->execute([':uid' => $user['id']]);
        $user['reservation'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } else {
        $user['reservation'] = null;
    }

    $user['exported_at'] = date('c');
    return $user;
}

/**
 * Get an email template by type (e.g. 'reset', 'test') for the current session language.
 * Falls back to English, then empty string.
 */
function getEmailTemplate($type)
{
    global $email_templates;
    $lang = $_SESSION['langID'] ?? 'en';
    $tpl = $email_templates[$type . '_' . $lang] ?? '';
    if (empty($tpl)) {
        $tpl = $email_templates[$type . '_en'] ?? '';
    }
    return $tpl;
}

/**
 * Wrap email body content in a styled HTML template.
 */
function emailTemplate($bodyHtml)
{
    return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<tr><td style="background-color:#3a3a3a;padding:20px 30px;text-align:center;">
<h1 style="color:#ffffff;margin:0;font-size:22px;">Seats</h1>
</td></tr>
<tr><td style="padding:30px;color:#333333;font-size:15px;line-height:1.6;">
' . $bodyHtml . '
</td></tr>
<tr><td style="background-color:#f4f4f4;padding:15px 30px;text-align:center;font-size:12px;color:#999;">
Seats &copy; ' . date('Y') . '
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

/**
 * Replace {{placeholders}} in a template string with values.
 */
function renderTemplate($template, $vars)
{
    foreach ($vars as $key => $val) {
        $template = str_replace('{{' . $key . '}}', $val, $template);
    }
    return $template;
}

/**
 * Send an HTML email using PHPMailer with the app's HTML template.
 * $bodyTemplate can contain {{placeholders}} that are replaced from $vars.
 */
function sendMail($to, $subject, $bodyTemplate, $vars = [])
{
    global $smtp_server, $smtp_port, $smtp_username, $smtp_password, $from_mail, $from_name;

    require_once __DIR__ . '/../vendor/autoload.php';

    $body = renderTemplate($bodyTemplate, $vars);
    $html = emailTemplate($body);

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_server;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$smtp_port;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($from_mail, $from_name);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

    $mail->send();
}
?>
