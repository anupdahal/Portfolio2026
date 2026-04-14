<?php
/**
 * Configuration & Helper Functions
 * Shared by all frontend PHP pages
 */

session_start();

// Database credentials (defaults)
$db_host = 'localhost';
$db_port = '3306';
$db_name = 'portfolio_db';
$db_user = 'root';
$db_pass = '';

// Load from backend-php .env if available
$envFile = __DIR__ . '/../../backend-php/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), '"\'');
            if ($key === 'DB_HOST') $db_host = $value;
            if ($key === 'DB_PORT') $db_port = $value;
            if ($key === 'DB_NAME') $db_name = $value;
            if ($key === 'DB_USER') $db_user = $value;
            if ($key === 'DB_PASSWORD') $db_pass = $value;
        }
    }
}

function getDB() {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// Theme handling via cookie
if (isset($_GET['toggle_theme'])) {
    $current = $_COOKIE['theme'] ?? 'dark';
    $new = $current === 'dark' ? 'light' : 'dark';
    setcookie('theme', $new, time() + 86400 * 365, '/');
    $_COOKIE['theme'] = $new;
    $params = $_GET;
    unset($params['toggle_theme']);
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    if (!empty($params)) $url .= '?' . http_build_query($params);
    header("Location: $url");
    exit;
}
$theme = $_COOKIE['theme'] ?? 'dark';

// Flash messages (stored in session)
function setFlash($message, $type = 'info') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Admin auth check
function isLoggedIn() {
    return !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: admin.php');
        exit;
    }
}

// Escape HTML output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Truncate string
function truncateStr($str, $len) {
    if (!$str) return '';
    return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . '...' : $str;
}

// Format date
function formatDate($dateStr) {
    if (!$dateStr) return '';
    return date('M j, Y', strtotime($dateStr));
}
