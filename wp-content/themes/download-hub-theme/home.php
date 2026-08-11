<?php
/**
 * Home Page Template - Google Play Professional Store UI Style
 */
require_once __DIR__ . '/header.php';

$cat_filter = $_GET['cat'] ?? 'all';
$apps = filter_apps($cat_filter);
?>

<main class="layout-container">
    <!-- Hero Banner for Homepage -->
    <section class="play-hero-banner">
        <div class="play-hero-banner-content">
            <span class="security-badge-box" style="margin-bottom: 0.75rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#01875F" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                100% Malware Free & Play Protect Verified
            </span>
            <h1 class="play-hero-banner-title">Discover & Download Premium Android Apps</h1>
            <p class="play-hero-banner-sub">Your trusted destination for verified, virus-free Android APKs, original app packages, and high-speed direct downloads.</p>
        </div>
    </section>

    <!-- Section Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
                Recommended Applications
            </h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Selected top rated apps & games</p>
        </div>
        <span style="color: var(--play-green); font-weight: 700; font-size: 0.9rem;">
            <?php echo count($apps); ?> Apps Available
        </span>
    </div>

    <?php if (empty($apps)) : ?>
        <div style="padding: 4rem 2rem; text-align: center; background: var(--bg-surface); border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 3rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 0.5rem;">No Apps Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Log in to Admin Portal to publish apps and APK files via ACF.</p>
            <a href="admin-upload.php" class="btn-primary">Go to Admin Portal</a>
        </div>
    <?php else : ?>
        <div class="play-apps-grid" id="appsContainer">
            <?php foreach ($apps as $app) : 
                $acf = $app['acf'] ?? [];
                $app_icon = !empty($acf['app_icon']) ? $acf['app_icon'] : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=200&q=80';
                $app_developer = !empty($acf['app_developer']) ? $acf['app_developer'] : 'Developer Studio';
                $app_rating = !empty($acf['app_rating']) ? $acf['app_rating'] : '4.8';
                $app_size = !empty($acf['app_size']) ? $acf['app_size'] : '25.0 MB';
                $app_version = !empty($acf['app_version']) ? $acf['app_version'] : 'v1.0.0';
                $download_count = !empty($acf['download_count']) ? (int)$acf['download_count'] : 100;
                $app_cat = !empty($app['category']) ? $app['category'] : 'Tools';
                
                $app_url = urlencode($app['id']);
                $apk_url = 'download.php?app=' . urlencode($app['id']);
            ?>
                <article class="play-app-card" data-category="<?php echo esc_attr($app['category_slug'] ?? 'tools'); ?>">
                    <div>
                        <!-- Category Badge in top right -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem;">
                            <a href="<?php echo $app_url; ?>">
                                <img src="<?php echo esc_url($app_icon); ?>" alt="<?php echo esc_attr($app['title']); ?>" class="play-card-icon" loading="lazy">
                            </a>
                            <span class="card-cat-tag"><?php echo esc_html($app_cat); ?></span>
                        </div>

                        <div class="play-card-info" style="margin-bottom: 0.85rem;">
                            <h3 class="play-card-name">
                                <a href="<?php echo $app_url; ?>"><?php echo esc_html($app['title']); ?></a>
                            </h3>
                            <div class="play-card-company"><?php echo esc_html($app_developer); ?></div>
                            
                            <div class="play-card-badges">
                                <span class="rating-badge">★ <?php echo esc_html($app_rating); ?></span>
                                <span class="version-badge"><?php echo esc_html($app_version); ?></span>
                                <span class="size-badge"><?php echo esc_html($app_size); ?></span>
                            </div>
                        </div>

                        <p class="play-card-desc"><?php echo esc_html($app['tagline'] ?? ''); ?></p>
                    </div>

                    <div class="play-card-footer">
                        <span class="download-count">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span id="count-<?php echo $app['id']; ?>"><?php echo number_format($download_count); ?></span> downloads
                        </span>

                        <a href="<?php echo $app_url; ?>" class="btn-install-sm" style="text-decoration: none; padding: 0.5rem 1.25rem;">
                            View App Details
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
