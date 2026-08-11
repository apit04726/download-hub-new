<?php
/**
 * Download-Hub APK File Download Handler
 * Bulletproof Case-Insensitive Multi-Directory Streaming Engine
 */

require_once __DIR__ . '/config.php';

$app_id = $_GET['app'] ?? '';
$action = $_GET['action'] ?? 'download';

if (empty($app_id)) {
    header("HTTP/1.0 404 Not Found");
    echo "App parameter missing.";
    exit;
}

$app = get_app_by_slug($app_id);

if (!$app) {
    header("HTTP/1.0 404 Not Found");
    echo "App not found.";
    exit;
}

// Increment download counter
$new_count = increment_app_download($app_id);

if ($action === 'count_only') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'download_count' => number_format($new_count)]);
    exit;
}

$rel_path = $app['acf']['apk_file'] ?? '';

// 1. Direct External Link Redirect
if (strpos($rel_path, 'http://') === 0 || strpos($rel_path, 'https://') === 0) {
    header('Location: ' . $rel_path);
    exit;
}

// 2. Smart Case-Insensitive Multi-Path Physical Search
function find_physical_apk_file($rel_path, $app_id) {
    $candidates = [
        __DIR__ . '/' . ltrim($rel_path, '/'),
        __DIR__ . '/uploads/apks/' . basename($rel_path),
        __DIR__ . '/apk/' . basename($rel_path),
        __DIR__ . '/uploads/apks/' . $app_id . '.apk',
        __DIR__ . '/apk/' . $app_id . '.apk',
    ];

    foreach ($candidates as $path) {
        if (!empty($path) && file_exists($path) && filesize($path) > 100) {
            return $path;
        }
    }

    // Case-insensitive directory scan in uploads/apks and apk
    $target_names = [strtolower(basename($rel_path)), strtolower($app_id . '.apk')];
    $dirs = [__DIR__ . '/uploads/apks', __DIR__ . '/apk'];

    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $f) {
                if (in_array(strtolower($f), $target_names)) {
                    $full = $dir . '/' . $f;
                    if (filesize($full) > 100) {
                        return $full;
                    }
                }
            }
        }
    }

    return null;
}

$file_path = find_physical_apk_file($rel_path, $app_id);

if (!$file_path) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/header.php';
    echo '<main class="layout-container" style="padding: 4rem 1rem; text-align: center;">';
    echo '<h2 style="font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 1rem; color: #EF4444;">APK File Not Uploaded Yet</h2>';
    echo '<p style="color: var(--text-muted); margin-bottom: 2rem;">No physical APK file found for <strong>' . esc_html($app['title']) . '</strong>. Please place the .apk file inside <code>uploads/apks/</code> or set a download link in Admin.</p>';
    echo '<a href="index.php" class="btn-primary">Return to Homepage</a>';
    echo '</main>';
    require_once __DIR__ . '/wp-content/themes/download-hub-theme/footer.php';
    exit;
}

// Clear output buffer to guarantee uncorrupted binary file transfer
while (ob_get_level()) {
    ob_end_clean();
}

$filename = basename($file_path);

header('Content-Description: File Transfer');
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
