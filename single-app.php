<?php
/**
 * Root Single App Handler - Download-Hub
 * Automatically redirects all single-app.php?app=slug URLs to clean permalinks (/slug)
 */

require_once __DIR__ . '/config.php';

$app_id = $_GET['app'] ?? '';
$app = get_app_by_slug($app_id);

if ($app && isset($app['id'])) {
    header('Location: ' . SITE_URL . '/' . urlencode($app['id']), true, 301);
    exit;
} else {
    header('Location: ' . SITE_URL . '/', true, 302);
    exit;
}
