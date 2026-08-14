<?php
/**
 * Footer Template - Download-Hub Ultra Professional Google Play Theme
 */
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Col 1: Brand Info -->
            <div class="footer-col brand-col">
                <a href="index.php" class="brand-logo" style="display: flex; align-items: center; gap: 0.65rem; text-decoration: none; margin-bottom: 1.15rem;">
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
                <p class="footer-desc">
                    Discover, explore, and safely download verified Android apps & games with Play Protect security and high-speed APK packages.
                </p>
                <div class="security-badge-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01875F" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    <span>100% Virus Free & Play Protect Verified</span>
                </div>
            </div>

            <!-- Col 2: Categories -->
            <div class="footer-col">
                <h4 class="footer-col-title">Categories</h4>
                <ul class="footer-links">
                    <li><a href="category/photography">Photography & AI</a></li>
                    <li><a href="category/tools">Tools & Utilities</a></li>
                    <li><a href="category/health">Health & Fitness</a></li>
                    <li><a href="category/games">Gaming & Action</a></li>
                    <li><a href="category/all">All Applications</a></li>
                </ul>
            </div>

            <!-- Col 3: Dynamic Top Apps from MySQL DB -->
            <div class="footer-col">
                <h4 class="footer-col-title">Top Apps</h4>
                <ul class="footer-links">
                    <?php 
                    $footer_apps = function_exists('get_all_apps') ? get_all_apps() : [];
                    $top_footer_apps = array_slice($footer_apps, 0, 5);
                    foreach ($top_footer_apps as $f_app_id => $f_app) : 
                    ?>
                        <li>
                            <a href="<?php echo esc_attr(urlencode($f_app['id'])); ?>">
                                <?php echo esc_html($f_app['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <div class="copyright-text">
                &copy; <?php echo date('Y'); ?> Download-Hub Inc. <a href="https://imvishall.netlify.app/" target="_blank" rel="noopener noreferrer" style="color: var(--play-green); text-decoration: none; font-weight: 600;">Vishal</a>. All Rights Reserved.
            </div>

            <div class="footer-bottom-links">
                <span class="lang-selector">🌐 English (US)</span>
                <span>Privacy</span>
                <span>Terms</span>
                <span>Security</span>
            </div>
        </div>
    </div>
</footer>

<!-- Interactive 5-Second Countdown Modal with Cancel Option -->
<div id="downloadModal" class="modal-overlay">
    <div class="modal-box" style="text-align: center; max-width: 460px;">
        <!-- Countdown Badge -->
        <div id="countdownBadge" style="width: 70px; height: 70px; background: var(--play-green-light); color: var(--play-green); font-size: 2.2rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; border: 2px solid var(--play-green); box-shadow: 0 4px 12px rgba(1,135,95,0.2);">
            <span id="modalTimerText">5</span>
        </div>

        <h3 id="modalAppTitle" style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text-main);">Preparing APK Download...</h3>
        <p id="modalAppStatus" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5;">Your APK file download will start automatically in 5 seconds...</p>
        
        <!-- Smooth Progress Bar -->
        <div style="background: var(--bg-surface); height: 10px; border-radius: 10px; overflow: hidden; margin-bottom: 1.75rem; border: 1px solid var(--border-color);">
            <div id="progressBar" style="width: 0%; height: 100%; background: var(--play-green); transition: width 0.1s linear;"></div>
        </div>

        <!-- Action Buttons: Cancel and Direct Download -->
        <div style="display: flex; gap: 0.85rem; justify-content: center;">
            <button id="modalCancelBtn" class="btn-cancel-download" onclick="cancelDownloadFlow()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Cancel Download
            </button>

            <button id="modalDownloadBtn" class="btn-primary" style="display: none; width: 100%; justify-content: center;" onclick="triggerDirectDownload()">
                Download File Now
            </button>
        </div>
    </div>
</div>

<style>
.btn-cancel-download {
    background: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FCA5A5;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0.65rem 1.5rem;
    border-radius: var(--radius-sm);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-cancel-download:hover {
    background: #FEE2E2;
    border-color: #F87171;
    color: #B91C1C;
}
</style>

<script>
let currentDownloadUrl = '';
let currentAppId = '';
let downloadTimerInterval = null;

function handleLiveSearch(query) {
    const cards = document.querySelectorAll('.play-app-card');
    const q = query.toLowerCase().trim();
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(q)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function startDownloadFlow(appId, appTitle, apkUrl) {
    currentAppId = appId;
    currentDownloadUrl = apkUrl || ('download.php?app=' + appId);

    const modal = document.getElementById('downloadModal');
    const title = document.getElementById('modalAppTitle');
    const status = document.getElementById('modalAppStatus');
    const timerText = document.getElementById('modalTimerText');
    const progress = document.getElementById('progressBar');
    const btnCancel = document.getElementById('modalCancelBtn');
    const btnDirect = document.getElementById('modalDownloadBtn');

    if (downloadTimerInterval) {
        clearInterval(downloadTimerInterval);
        downloadTimerInterval = null;
    }

    title.innerText = 'Downloading ' + appTitle;
    timerText.innerText = '5';
    status.innerText = 'Your APK file download will start automatically in 5 seconds...';
    progress.style.width = '0%';
    btnDirect.style.display = 'none';
    btnCancel.style.display = 'inline-flex';

    modal.classList.add('active');

    const duration = 5000; // 5 seconds
    const startTime = Date.now();

    downloadTimerInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const remainingMs = Math.max(0, duration - elapsed);
        const remainingSec = Math.ceil(remainingMs / 1000);

        timerText.innerText = remainingSec > 0 ? remainingSec : '✓';
        status.innerText = remainingSec > 0 
            ? ('Your APK file download will start automatically in ' + remainingSec + ' seconds...')
            : 'Download starting automatically...';

        const percent = Math.min(100, (elapsed / duration) * 100);
        progress.style.width = percent + '%';

        if (elapsed >= duration) {
            clearInterval(downloadTimerInterval);
            downloadTimerInterval = null;
            btnDirect.style.display = 'inline-flex';
            btnCancel.style.display = 'none';
            triggerDirectDownload();
        }
    }, 100);
}

function cancelDownloadFlow() {
    if (downloadTimerInterval) {
        clearInterval(downloadTimerInterval);
        downloadTimerInterval = null;
    }
    const modal = document.getElementById('downloadModal');
    if (modal) modal.classList.remove('active');
}

function triggerDirectDownload() {
    if (!currentAppId) return;
    
    // Update live counter via AJAX
    fetch('download.php?app=' + currentAppId + '&action=count_only')
        .then(r => r.json())
        .then(data => {
            if (data.download_count) {
                const countElem = document.getElementById('count-' + currentAppId);
                if (countElem) countElem.innerText = data.download_count;
            }
        }).catch(e => console.log(e));

    // Trigger actual download link
    window.location.href = 'download.php?app=' + currentAppId;
// Smart Share App Link (Web Share API for WhatsApp, Telegram, FB, Twitter + Clipboard Fallback)
function shareAppLink(title, tagline) {
    const shareData = {
        title: title + ' APK Download',
        text: 'Download ' + title + ' APK on DownloadHub! ' + (tagline || ''),
        url: window.location.href
    };
    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('📲 Link copied for ' + title + '!\n\nWhen you paste and share this link on WhatsApp, Telegram, or Facebook, the app icon & description will appear automatically in the link preview!');
    }
}

// Scroll to Top Helper Function
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Scroll Listener for Floating FAB Button
window.addEventListener('scroll', function() {
    const fab = document.getElementById('scrollTopBtn');
    if (fab) {
        if (window.scrollY > 250) {
            fab.classList.add('visible');
        } else {
            fab.classList.remove('visible');
        }
    }
}, { passive: true });
</script>

<!-- Floating Scroll-to-Top Action Button (FAB) -->
<button id="scrollTopBtn" class="scroll-top-fab" onclick="scrollToTop()" aria-label="Scroll to top" title="Back to Top">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5"></line>
        <polyline points="5 12 12 5 19 12"></polyline>
    </svg>
</button>

<!-- Play Store Native Mobile Bottom Navigation Bar -->
<nav class="play-mobile-bottom-bar" aria-label="Mobile Navigation">
    <a href="category/all" class="mobile-nav-item <?php echo (($current_cat ?? 'all') === 'all') ? 'active' : ''; ?>" onclick="scrollToTop()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect></svg>
        <span>Apps</span>
    </a>
    <a href="category/games" class="mobile-nav-item <?php echo (($current_cat ?? '') === 'games') ? 'active' : ''; ?>" onclick="scrollToTop()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="6 11 10 7 14 11 18 7 18 17 6 17 6 11"></polygon><line x1="10" y1="11" x2="10" y2="14"></line><line x1="14" y1="11" x2="14" y2="14"></line></svg>
        <span>Games</span>
    </a>
    <button class="mobile-nav-item" onclick="scrollToTop(); setTimeout(() => { const el = document.getElementById('liveSearchInput'); if (el) el.focus(); }, 100);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <span>Search</span>
    </button>
    <button class="mobile-nav-item" onclick="scrollToTop()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
        <span>Top</span>
    </button>
</nav>

</body>
</html>

