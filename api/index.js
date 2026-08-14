const fs = require('fs');
const path = require('path');

module.exports = (req, res) => {
    try {
        // Extract pathname e.g. "/socialgo"
        const reqUrl = req.url || '/';
        const urlPath = reqUrl.split('?')[0].replace(/^\/|\/$/g, '').trim();

        // Protocol and host resolution for absolute Open Graph asset links
        const protocol = req.headers['x-forwarded-proto'] || 'https';
        const host = req.headers.host || 'download-hub-new.vercel.app';
        const baseUrl = `${protocol}://${host}`;

        // Read static index.html template
        const indexPath = path.join(process.cwd(), 'index.html');
        let html = fs.readFileSync(indexPath, 'utf8');

        // Read data_store.json
        const dataPath = path.join(process.cwd(), 'data_store.json');
        let apps = [];
        if (fs.existsSync(dataPath)) {
            apps = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
        }

        // Match app details if opening an app route (e.g. /socialgo)
        if (urlPath && urlPath !== 'index.html' && apps.length > 0) {
            const foundApp = apps.find(a => a.id.toLowerCase() === urlPath.toLowerCase());
            if (foundApp) {
                const title = `${foundApp.title} APK Download (v${foundApp.acf?.app_version || '1.0'}) | DownloadHub`;
                const desc = foundApp.tagline || (foundApp.description ? foundApp.description.slice(0, 150) : `Download ${foundApp.title} APK package safely on DownloadHub.`);
                
                let iconUrl = foundApp.acf?.app_icon || '/og-image.png';
                if (!iconUrl.startsWith('http://') && !iconUrl.startsWith('https://')) {
                    iconUrl = `${baseUrl}/${iconUrl.replace(/^\//, '')}`;
                }
                const appUrl = `${baseUrl}/${foundApp.id}`;

                // Inject dynamic Open Graph, Twitter Cards, and Title tags into HTML head
                html = html.replace(/<title>.*?<\/title>/gi, `<title>${escapeHtml(title)}</title>`);
                html = html.replace(/<meta property="og:title" content=".*?"\/?>/gi, `<meta property="og:title" content="${escapeHtml(title)}">`);
                html = html.replace(/<meta property="og:description" content=".*?"\/?>/gi, `<meta property="og:description" content="${escapeHtml(desc)}">`);
                html = html.replace(/<meta property="og:image" content=".*?"\/?>/gi, `<meta property="og:image" content="${escapeHtml(iconUrl)}">`);
                html = html.replace(/<meta property="og:url" content=".*?"\/?>/gi, `<meta property="og:url" content="${escapeHtml(appUrl)}">`);
                html = html.replace(/<meta name="twitter:title" content=".*?"\/?>/gi, `<meta name="twitter:title" content="${escapeHtml(title)}">`);
                html = html.replace(/<meta name="twitter:description" content=".*?"\/?>/gi, `<meta name="twitter:description" content="${escapeHtml(desc)}">`);
                html = html.replace(/<meta name="twitter:image" content=".*?"\/?>/gi, `<meta name="twitter:image" content="${escapeHtml(iconUrl)}">`);

                // Zero-Flicker HTML Pre-rendering for instant Frame 1 rendering
                const relativeIcon = foundApp.acf?.app_icon ? ('/' + foundApp.acf.app_icon.replace(/^\//, '')) : '/og-image.png';
                const preRenderedHtml = `<div id="root">
                    <header class="site-header">
                        <div class="nav-container">
                            <a href="/" class="brand-logo" style="display: flex; align-items: center; gap: 0.65rem; text-decoration: none;">
                                <div class="brand-logo-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </div>
                                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; display: flex; align-items: center;">
                                    Download<span style="color: var(--play-green);">Hub</span>
                                    <span style="width: 6px; height: 6px; background: var(--play-green); border-radius: 50%; display: inline-block; margin-left: 2px;"></span>
                                </div>
                            </a>
                        </div>
                    </header>
                    <main class="layout-container">
                        <div style="padding: 2.5rem 0;">
                            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2.5rem; flex-wrap: wrap;">
                                <img src="${escapeHtml(relativeIcon)}" alt="${escapeHtml(foundApp.title)}" style="width: 110px; height: 110px; border-radius: 24px; object-fit: cover; border: 1px solid var(--border-color);" />
                                <div style="flex: 1; min-width: 240px;">
                                    <h1 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-main);">${escapeHtml(foundApp.title)}</h1>
                                    <div style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.25rem;">${escapeHtml(foundApp.acf?.app_developer || '')}</div>
                                    <div className="skeleton-box" style="width: 160px; height: 46px; border-radius: 24px;"></div>
                                </div>
                            </div>
                            <div class="skeleton-box" style="width: 100%; height: 280px; border-radius: 16px;"></div>
                        </div>
                    </main>
                </div>`;

                html = html.replace(/<div id="root">[\s\S]*?<\/main>\s*<\/div>/gi, preRenderedHtml);
            }
        }

        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.setHeader('Cache-Control', 's-maxage=1, stale-while-revalidate=59');
        return res.status(200).send(html);
    } catch (err) {
        console.error('API Error:', err);
        const indexPath = path.join(process.cwd(), 'index.html');
        const html = fs.readFileSync(indexPath, 'utf8');
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.status(200).send(html);
    }
};

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
