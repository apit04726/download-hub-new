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
