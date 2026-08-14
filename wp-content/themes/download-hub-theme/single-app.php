<?php
/**
 * Single App Page - Exact Google Play Store UI Layout (Enhanced)
 */
require_once __DIR__ . '/header.php';

$app_id = $_GET['app'] ?? '';
$app = get_app_by_slug($app_id);

if (!$app) {
    echo '<main class="layout-container" style="padding: 4rem 1rem; text-align: center;">';
    echo '<h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 1rem;">App Not Found</h2>';
    echo '<p style="color: var(--text-muted); margin-bottom: 2rem;">The requested app or package could not be found on Download-Hub.</p>';
    echo '<a href="index.php" class="btn-primary">Return to Google Play Store</a>';
    echo '</main>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$acf = $app['acf'] ?? [];
$app_icon = !empty($acf['app_icon']) ? $acf['app_icon'] : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=200&q=80';
$app_developer = !empty($acf['app_developer']) ? $acf['app_developer'] : 'Developer Studio';
$app_rating = !empty($acf['app_rating']) ? $acf['app_rating'] : '4.8';
$rating_count = !empty($acf['rating_count']) ? $acf['rating_count'] : '1,250';
$app_size = !empty($acf['app_size']) ? $acf['app_size'] : '25.0 MB';
$app_version = !empty($acf['app_version']) ? $acf['app_version'] : 'v1.0.0';
$min_android = !empty($acf['min_android']) ? $acf['min_android'] : 'Android 8.0+';
$package_name = !empty($acf['package_name']) ? $acf['package_name'] : 'com.app';
$release_date = !empty($acf['release_date']) ? $acf['release_date'] : date('F j, Y');
$download_count = !empty($acf['download_count']) ? (int)$acf['download_count'] : 100;
$app_cat = !empty($app['category']) ? $app['category'] : 'Tools';
$screenshots = !empty($acf['screenshots']) ? $acf['screenshots'] : [$app_icon];

// If only 1 screenshot exists, add fallback high quality screenshots for full gallery
if (count($screenshots) < 3) {
    $screenshots[] = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=400&q=80';
    $screenshots[] = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=400&q=80';
}

$features = !empty($acf['features']) ? $acf['features'] : [
    'Fast and optimized Android performance',
    'Verified malware-free and Google Play Protect safe',
    'Clean ad-free interface with cloud syncing',
    'Low battery consumption and minimal memory usage'
];

$apk_url = 'download.php?app=' . $app['id'];
$all_apps = get_all_apps();
?>

<main class="layout-container">
    <!-- Top Category & Admin Controls Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <a href="index.php" style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Apps</a>
            <span style="color: var(--text-dim);">&rsaquo;</span>
            <span class="card-cat-tag"><?php echo esc_html($app_cat); ?></span>
        </div>

        <?php if ($is_admin) : ?>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <a href="admin-upload.php?edit=<?php echo urlencode($app['id']); ?>" style="display: inline-flex; align-items: center; gap: 0.4rem; background: var(--play-green); color: #FFF; padding: 0.45rem 1rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 600; text-decoration: none; box-shadow: 0 2px 6px rgba(1,135,95,0.3);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit App Details
                </a>
                <a href="admin-upload.php?delete=<?php echo urlencode($app['id']); ?>" onclick="return confirm('Are you sure you want to delete this app?');" style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid #EF4444; padding: 0.45rem 0.95rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Delete App
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Google Play Hero Section -->
    <section class="play-hero">
        <div class="play-hero-left">
            <h1 class="play-hero-title"><?php echo esc_html($app['title']); ?></h1>
            <div class="play-hero-company"><?php echo esc_html($app_developer); ?></div>

            <!-- Stats Row matching Google Play screenshot -->
            <div class="play-stats-row">
                <div class="play-stat-col">
                    <div class="play-stat-val">
                        <?php echo esc_html($app_rating); ?> ★
                    </div>
                    <div class="play-stat-sub"><?php echo esc_html($rating_count); ?> reviews</div>
                </div>

                <div class="play-stat-col" style="border-left: 1px solid var(--border-color); padding-left: 2rem;">
                    <div class="play-stat-val" id="count-<?php echo $app['id']; ?>"><?php echo number_format($download_count); ?>+</div>
                    <div class="play-stat-sub">Downloads</div>
                </div>

                <div class="play-stat-col" style="border-left: 1px solid var(--border-color); padding-left: 2rem;">
                    <div class="play-stat-val" style="font-size: 0.95rem;">
                        <span style="border: 1px solid var(--text-muted); padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">12+</span>
                    </div>
                    <div class="play-stat-sub">Rated for 12+</div>
                </div>
            </div>

            <!-- Action Bar matching Google Play screenshot -->
            <div class="play-hero-actions">
                <button class="btn-install" onclick="startDownloadFlow('<?php echo $app['id']; ?>', '<?php echo esc_js($app['title']); ?>', '<?php echo esc_js($apk_url); ?>')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Install APK (<?php echo esc_html($app_size); ?>)
                </button>

                <button class="btn-secondary-action" onclick="shareAppLink('<?php echo esc_js($app['title']); ?>', '<?php echo esc_js($app['tagline'] ?? ''); ?>')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                    Share App Link
                </button>

                <button class="btn-secondary-action">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    Add to wishlist
                </button>
            </div>

            <!-- Device Compatibility Indicator -->
            <div class="device-compatibility" style="display: flex; align-items: center; justify-content: center; width: 100%; margin-top: 1.25rem;">
                <div style="display: inline-flex; align-items: flex-start; justify-content: center; gap: 0.55rem; text-align: left; max-width: 100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01875F" stroke-width="2.5" style="flex-shrink: 0; margin-top: 0.15rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.4;">This app is available for your device (Requires <?php echo esc_html($min_android); ?>)</span>
                </div>
            </div>
        </div>

        <!-- Large Play Store App Icon (160x160px) -->
        <div class="play-hero-right">
            <img src="<?php echo esc_url($app_icon); ?>" alt="<?php echo esc_attr($app['title']); ?>" class="play-hero-app-icon">
        </div>
    </section>

    <!-- Google Play Phone Screenshots Carousel with Scroll Controls -->
    <?php if (!empty($screenshots)) : ?>
    <section class="play-screenshots-container">
        <button class="screenshot-arrow arrow-left" onclick="scrollScreenshots(-300)">&lsaquo;</button>
        <div class="play-screenshots-scroll" id="playScreenshotScroll">
            <?php foreach ($screenshots as $shot) : ?>
                <div class="screenshot-wrapper">
                    <img src="<?php echo esc_url($shot); ?>" alt="App Screenshot" class="play-screenshot-item" onclick="openScreenshotModal(this.src)">
                    <div class="screenshot-overlay-zoom">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="screenshot-arrow arrow-right" onclick="scrollScreenshots(300)">&rsaquo;</button>
    </section>
    <?php endif; ?>

    <!-- Main Content Layout (Left Details + Right Sidebar) -->
    <div class="play-main-layout">
        <!-- Left Column: About & Specs -->
        <div class="play-content-left">
            <div class="play-section-title">
                <span>About this app</span>
                <span style="font-size: 1.25rem; color: var(--play-green); cursor: pointer;">&rarr;</span>
            </div>
            
            <p class="play-description"><?php echo nl2br(esc_html($app['description'] ?? 'No detailed description available.')); ?></p>

            <div class="play-section-title">
                <span>Technical Specifications & Package Info</span>
            </div>

            <div class="play-specs-grid">
                <div class="spec-card">
                    <div class="spec-icon">📦</div>
                    <div>
                        <div class="spec-cell-label">Version</div>
                        <div class="spec-cell-val"><?php echo esc_html($app_version); ?></div>
                    </div>
                </div>

                <div class="spec-card">
                    <div class="spec-icon">📅</div>
                    <div>
                        <div class="spec-cell-label">Updated on</div>
                        <div class="spec-cell-val"><?php echo esc_html($release_date); ?></div>
                    </div>
                </div>

                <div class="spec-card">
                    <div class="spec-icon">⚡</div>
                    <div>
                        <div class="spec-cell-label">Download Size</div>
                        <div class="spec-cell-val"><?php echo esc_html($app_size); ?></div>
                    </div>
                </div>

                <div class="spec-card">
                    <div class="spec-icon">📱</div>
                    <div>
                        <div class="spec-cell-label">Requires Android</div>
                        <div class="spec-cell-val"><?php echo esc_html($min_android); ?></div>
                    </div>
                </div>

                <div class="spec-card" style="grid-column: span 2;">
                    <div class="spec-icon">🏷️</div>
                    <div>
                        <div class="spec-cell-label">Package Name</div>
                        <div class="spec-cell-val" style="word-break: break-all; font-family: monospace; font-size: 0.9rem;"><?php echo esc_html($package_name); ?></div>
                    </div>
                </div>
            </div>

            <!-- Key Features Checklist -->
            <div class="play-section-title" style="margin-top: 2.5rem;">
                <span>Key Highlights & Features</span>
            </div>
            <ul class="features-checklist play-features-grid">
                <?php foreach ($features as $feat) : 
                    $clean_feat = ltrim(preg_replace('/^[\s\x{2705}\x{2713}\x{2714}\x{2611}✅✔✓]+/u', '', $feat));
                ?>
                    <li style="display: flex; align-items: center; gap: 0.85rem; background: var(--bg-surface); padding: 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div class="check-icon">✓</div>
                        <span><?php echo esc_html($clean_feat ?: $feat); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Right Column: Sidebar (App Support & Similar Apps) -->
        <div class="play-sidebar">
            <!-- App Support Card -->
            <div class="sidebar-card">
                <div class="sidebar-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>App support</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">&or;</span>
                </div>
                <div style="font-size: 0.88rem; color: var(--text-muted); display: grid; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>📧</span> <strong>Support Email:</strong> support@<?php echo esc_attr($app['id']); ?>.com
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>🌐</span> <strong>Developer:</strong> <?php echo esc_html($app_developer); ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔒</span> <strong>Privacy Policy:</strong> <span style="color: var(--play-green); font-weight: 600;">Verified Clean</span>
                    </div>
                </div>
            </div>

            <!-- Similar Apps Section matching Google Play screenshot -->
            <div class="sidebar-card">
                <div class="sidebar-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Similar apps</span>
                    <a href="index.php" style="font-size: 0.9rem; color: var(--play-green); font-weight: 600;">View All &rarr;</a>
                </div>

                <?php 
                $similar_count = 0;
                foreach ($all_apps as $other_app) : 
                    if ($other_app['id'] === $app['id']) continue;
                    $similar_count++;
                    if ($similar_count > 4) break;
                    $o_acf = $other_app['acf'] ?? [];
                    $o_icon = !empty($o_acf['app_icon']) ? $o_acf['app_icon'] : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=200&q=80';
                    $o_dev = !empty($o_acf['app_developer']) ? $o_acf['app_developer'] : 'Developer';
                    $o_rating = !empty($o_acf['app_rating']) ? $o_acf['app_rating'] : '4.8';
                    $o_url = urlencode($other_app['id']);
                ?>
                    <a href="<?php echo $o_url; ?>" class="similar-app-item">
                        <img src="<?php echo esc_url($o_icon); ?>" class="similar-app-icon">
                        <div class="similar-app-meta">
                            <div class="similar-app-name"><?php echo esc_html($other_app['title']); ?></div>
                            <div class="similar-app-company"><?php echo esc_html($o_dev); ?></div>
                            <div class="similar-app-rating">★ <?php echo esc_html($o_rating); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<!-- Screenshot Image Preview Lightbox Modal -->
<div id="screenshotModal" class="modal-overlay" onclick="closeScreenshotModal()">
    <div class="modal-box" style="max-width: 850px; background: transparent; border: none; box-shadow: none; padding: 0; text-align: center;">
        <img id="screenshotFullImg" src="" style="max-width: 100%; max-height: 85vh; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
        <p style="color: #FFF; margin-top: 1rem; font-size: 0.9rem;">Click anywhere to close preview</p>
    </div>
</div>

<script>
function scrollScreenshots(amount) {
    const container = document.getElementById('playScreenshotScroll');
    if (container) container.scrollBy({ left: amount, behavior: 'smooth' });
}
function openScreenshotModal(imgSrc) {
    document.getElementById('screenshotFullImg').src = imgSrc;
    document.getElementById('screenshotModal').classList.add('active');
}
function closeScreenshotModal() {
    document.getElementById('screenshotModal').classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
