<?php
/**
 * Main WordPress Router & Bootstrap - Download-Hub
 */

require_once __DIR__ . '/config.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_path = rtrim(dirname($script_name), '/\\');

// Extract clean relative path from URL (e.g. "category/games", "socialgo", "admin-upload.php")
$relative_path = trim(substr($request_uri, strlen($base_path)), '/');
$known_categories = ['games', 'tools', 'photography', 'health', 'productivity', 'all'];

if (isset($_GET['app']) && !empty($_GET['app'])) {
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/single-app.php';
} elseif (empty($relative_path) || $relative_path === 'index.php') {
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/home.php';
} elseif (strpos($relative_path, 'admin-upload.php') !== false) {
    require_once __DIR__ . '/admin-upload.php';
} elseif (strpos($relative_path, 'download.php') !== false) {
    require_once __DIR__ . '/download.php';
} elseif (strpos($relative_path, 'category/') === 0 || strpos($relative_path, 'cat/') === 0) {
    $cat_slug = strtolower(trim(basename($relative_path)));
    $_GET['cat'] = $cat_slug;
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/home.php';
} elseif (in_array(strtolower($relative_path), $known_categories)) {
    $_GET['cat'] = strtolower($relative_path);
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/home.php';
} else {
    // Treat relative path as App Slug (e.g. "socialgo", "photopro-ai", "woolj")
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]+/', '', $relative_path)));
    $app = get_app_by_slug($slug);
    if ($app) {
        $_GET['app'] = $app['id'];
        require_once __DIR__ . '/wp-content/themes/download-hub-theme/single-app.php';
    } else {
        require_once __DIR__ . '/wp-content/themes/download-hub-theme/home.php';
    }
}
