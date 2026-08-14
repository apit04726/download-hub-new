<?php
/**
 * Header Template - Download-Hub Ultra Professional Google Play Theme
 */
if (session_status() === PHP_SESSION_NONE) {
    @session_save_path('/tmp');
    @session_start();
}
require_once __DIR__ . '/../../../config.php';
$is_admin = !empty($_SESSION['is_admin_logged_in']);
$current_cat = $_GET['cat'] ?? 'all';
if ($current_cat === 'all') {
    $request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path_parts = explode('/', trim($request_uri, '/'));
    $last_part = strtolower(end($path_parts));
    if (in_array($last_part, ['games', 'tools', 'photography', 'health', 'productivity', 'all'])) {
        $current_cat = $last_part;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo esc_url(SITE_URL . '/'); ?>">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="shortcut icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <title><?php echo esc_html(SITE_NAME . ' | ' . SITE_TAGLINE); ?></title>
    
    <!-- Open Graph / WhatsApp / Facebook Preview Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo esc_attr(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo esc_attr(SITE_NAME . ' | ' . SITE_TAGLINE); ?>">
    <meta property="og:description" content="Discover, explore, and safely download verified Android apps & games with Play Protect security and high-speed APK packages.">
    <meta property="og:image" content="<?php echo esc_url(SITE_URL . '/favicon.svg'); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(SITE_NAME . ' | ' . SITE_TAGLINE); ?>">
    <meta name="twitter:description" content="Discover, explore, and safely download verified Android apps & games with Play Protect security and high-speed APK packages.">
    <meta name="twitter:image" content="<?php echo esc_url(SITE_URL . '/favicon.svg'); ?>">
    
    <!-- Relative & Absolute Fallback Stylesheet Links -->
    <link rel="stylesheet" href="wp-content/themes/download-hub-theme/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/wp-content/themes/download-hub-theme/style.css">

    <!-- Anti-Explode CSS for SVGs and Header Layout -->
    <style>
        svg { display: inline-block !important; vertical-align: middle; }
        .brand-logo-icon {
            width: 32px;
            height: 32px;
            background: #01875F;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(1, 135, 95, 0.3);
        }
        .brand-logo-icon svg {
            width: 18px !important;
            height: 18px !important;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="nav-container">
        <!-- Ultra Professional Logo -->
        <a href="<?php echo esc_url(SITE_URL); ?>" class="brand-logo" style="display: flex; align-items: center; gap: 0.65rem; text-decoration: none;">
            <div class="brand-logo-icon" style="width: 38px; height: 38px; background: linear-gradient(135deg, #01875F 0%, #34A853 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(1, 135, 95, 0.3); flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
            </div>
            <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; display: flex; align-items: center;">
                Download<span style="color: var(--play-green);">Hub</span><span style="width: 6px; height: 6px; background: var(--play-green); border-radius: 50%; display: inline-block; margin-left: 2px;"></span>
            </div>
        </a>

        <!-- Search Bar with Kbd shortcut badge -->
        <div class="search-bar-wrap">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="liveSearchInput" class="search-input" placeholder="Search Android apps, games, APKs..." onkeyup="handleLiveSearch(this.value)">
            <span class="search-kbd">⌘K</span>
        </div>

        <!-- Action Buttons -->
        <div class="nav-actions">
            <?php if ($is_admin) : ?>
                <a href="admin-upload.php" class="btn-primary" style="font-size: 0.85rem; padding: 0.45rem 1.15rem; border-radius: 8px;">
                    Upload App
                </a>
            <?php else : ?>
                <a href="admin-upload.php" class="admin-badge-pill" title="Admin Access Portal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Admin Portal
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
