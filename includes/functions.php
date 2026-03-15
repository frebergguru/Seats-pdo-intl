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
?>
