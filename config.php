<?php
/**
 * Download-Hub Configuration & Data Store Engine
 * Portable File-Based Architecture (JSON + Direct Uploads)
 * Compatible with Localhost, MAMP, Vercel, Netlify, and Shared Hosting
 */

// Writable session path for Serverless / Lambda environments (Vercel)
if (function_exists('session_save_path') && session_status() === PHP_SESSION_NONE) {
    @session_save_path('/tmp');
}
@ini_set('session.save_path', '/tmp');

// Safe ini_set configuration
if (!getenv('VERCEL') && !isset($_ENV['VERCEL']) && !isset($_SERVER['VERCEL'])) {
    @ini_set('upload_max_filesize', '100M');
    @ini_set('post_max_size', '110M');
    @ini_set('memory_limit', '256M');
    @ini_set('max_execution_time', '300');
    @ini_set('max_input_time', '300');
    if (function_exists('set_time_limit')) {
        @set_time_limit(0);
    }
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

define('SITE_NAME', 'Download-Hub');
define('SITE_TAGLINE', 'Premium Android Apps & Games Portal');

// Dynamic base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$raw_dir = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($raw_dir === 'api' || empty($raw_dir) || $raw_dir === '.') {
    $script_dir = '';
} else {
    $script_dir = '/' . $raw_dir;
}
define('SITE_URL', rtrim($protocol . $host . $script_dir, '/'));

define('UPLOADS_DIR', __DIR__ . '/uploads/');
define('APKS_DIR', UPLOADS_DIR . 'apks/');
define('DATA_FILE', __DIR__ . '/data_store.json');

// WordPress Sanitization & Escape Shims
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_js')) {
    function esc_js($text) {
        return addslashes((string)$text);
    }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags(trim((string)$str));
    }
}
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name) {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . md5($action) . '">';
    }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return $nonce === md5($action);
    }
}

/**
 * Read all apps from JSON data store
 */
function get_all_apps() {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $content = @file_get_contents(DATA_FILE);
    if (empty($content)) {
        return [];
    }
    $apps_list = json_decode($content, true) ?: [];
    
    $apps = [];
    foreach ($apps_list as $app) {
        if (!empty($app['id'])) {
            $apps[$app['id']] = $app;
        }
    }
    return $apps;
}

/**
 * Save all apps array back to JSON file
 */
function save_all_apps($apps) {
    $apps_list = array_values($apps);
    $json = json_encode($apps_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return @file_put_contents(DATA_FILE, $json) !== false;
}

/**
 * Get App by Slug ID
 */
function get_app_by_slug($id) {
    $apps = get_all_apps();
    return $apps[$id] ?? null;
}

/**
 * Get Apps by Category Slug
 */
function get_apps_by_category($cat_slug) {
    $apps = get_all_apps();
    $cat_slug = strtolower(trim($cat_slug));
    $filtered = [];

    foreach ($apps as $app) {
        $c_slug = strtolower($app['category_slug'] ?? '');
        $c_name = strtolower($app['category'] ?? '');
        if ($c_slug === $cat_slug || $c_name === $cat_slug) {
            $filtered[] = $app;
        }
    }
    return $filtered;
}

/**
 * Filter Apps by Category Slug (with 'all' fallback)
 */
function filter_apps($cat_slug = 'all') {
    $apps = get_all_apps();
    if (empty($cat_slug) || $cat_slug === 'all') {
        return array_values($apps);
    }
    return get_apps_by_category($cat_slug);
}

/**
 * Search Apps by Keyword Query
 */
function search_apps($query) {
    $apps = get_all_apps();
    $query = strtolower(trim($query));
    if (empty($query)) return array_values($apps);

    $filtered = [];
    foreach ($apps as $app) {
        $title = strtolower($app['title'] ?? '');
        $tagline = strtolower($app['tagline'] ?? '');
        $category = strtolower($app['category'] ?? '');
        $description = strtolower($app['description'] ?? '');

        if (strpos($title, $query) !== false || strpos($tagline, $query) !== false || strpos($category, $query) !== false || strpos($description, $query) !== false) {
            $filtered[] = $app;
        }
    }
    return $filtered;
}

/**
 * Save / Create / Update App Data
 */
function save_app_data($app_data, $apk_binary = null, $apk_filename = null) {
    $apps = get_all_apps();
    $id = $app_data['id'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $app_data['title'])));
    $app_data['id'] = $id;

    if (!isset($app_data['category_slug'])) {
        $app_data['category_slug'] = strtolower($app_data['category'] ?? 'tools');
    }

    $apps[$id] = $app_data;
    save_all_apps($apps);
    return $id;
}

/**
 * Delete App by ID
 */
function delete_app($id) {
    $apps = get_all_apps();
    if (isset($apps[$id])) {
        unset($apps[$id]);
        save_all_apps($apps);
    }
    return true;
}

/**
 * Increment Download Counter for App
 */
function increment_app_download($id) {
    $apps = get_all_apps();
    if (isset($apps[$id])) {
        $current = (int)($apps[$id]['acf']['download_count'] ?? 0);
        $apps[$id]['acf']['download_count'] = $current + 1;
        save_all_apps($apps);
        return $apps[$id]['acf']['download_count'];
    }
    return 0;
}

/**
 * Admin Passcode Verification
 */
function verify_admin_passcode($input_passcode) {
    $pass_file = __DIR__ . '/admin_pass.txt';
    if (file_exists($pass_file)) {
        $hash = trim(file_get_contents($pass_file));
        if (!empty($hash)) {
            return password_verify($input_passcode, $hash);
        }
    }
    return $input_passcode === 'admin123';
}

/**
 * Update Admin Passcode
 */
function update_admin_passcode($new_passcode) {
    $hash = password_hash($new_passcode, PASSWORD_DEFAULT);
    $pass_file = __DIR__ . '/admin_pass.txt';
    return @file_put_contents($pass_file, $hash) !== false;
}
