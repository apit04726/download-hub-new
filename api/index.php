<?php
/**
 * Vercel Serverless Entrypoint for PHP
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $root = dirname(__DIR__);
    chdir($root);
    require_once $root . '/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo "<div style='padding:20px; font-family:sans-serif;'>";
    echo "<h2>Application Exception</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

