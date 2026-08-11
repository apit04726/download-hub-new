<?php
/**
 * ACF App Upload & Admin Management Dashboard
 * Portable File-Based Admin Panel
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$message = '';
$error = '';

// Handle Admin Passcode Login
if (isset($_POST['admin_action']) && $_POST['admin_action'] === 'login') {
    $passcode = $_POST['admin_passcode'] ?? '';
    if (verify_admin_passcode($passcode)) {
        $_SESSION['is_admin_logged_in'] = true;
    } else {
        $error = 'Invalid Admin Passcode! Access Denied.';
    }
}

// Handle Admin Passcode Change
if (isset($_POST['admin_action']) && $_POST['admin_action'] === 'change_passcode') {
    $new_pass = trim($_POST['new_passcode'] ?? '');
    if (strlen($new_pass) < 4) {
        $error = 'New passcode must be at least 4 characters long.';
    } else {
        if (update_admin_passcode($new_pass)) {
            $message = 'Admin Passcode updated successfully!';
        } else {
            $error = 'Failed to update passcode.';
        }
    }
}

// Handle Admin Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['is_admin_logged_in']);
    header("Location: admin-upload.php");
    exit;
}

$is_admin = !empty($_SESSION['is_admin_logged_in']);

// Handle Delete App Action
if ($is_admin && isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    delete_app($del_id);
    $message = 'App "' . esc_html($del_id) . '" deleted successfully!';
}

// Handle Edit Fetch Action
$editing_id = $_GET['edit'] ?? '';
$editing_app = null;
if ($is_admin && !empty($editing_id)) {
    $editing_app = get_app_by_slug($editing_id);
}

// Handle Form Submission (Create or Update)
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($is_admin && ($request_method === 'POST') && !isset($_POST['admin_action'])) {
    $existing_id = trim($_POST['existing_app_id'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $category = trim($_POST['category'] ?? 'Tools');
    $category_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $category));
    $developer = trim($_POST['developer'] ?? 'Independent Developer');
    $version = trim($_POST['version'] ?? 'v1.0.0');
    $size = trim($_POST['size'] ?? '25.0 MB');
    $rating = trim($_POST['rating'] ?? '4.8');
    $package_name = trim($_POST['package_name'] ?? 'com.example.app');
    $min_android = trim($_POST['min_android'] ?? 'Android 8.0+');
    $description = trim($_POST['description'] ?? '');
    $features_raw = trim($_POST['features'] ?? '');
    $features = array_filter(array_map('trim', explode("\n", $features_raw)));

    if (empty($title)) {
        $error = 'App Title is required.';
    } else {
        // Ensure Upload Directories exist
        $apks_dir = __DIR__ . '/uploads/apks/';
        $imgs_dir = __DIR__ . '/uploads/images/';
        if (!file_exists($apks_dir)) @mkdir($apks_dir, 0777, true);
        if (!file_exists($imgs_dir)) @mkdir($imgs_dir, 0777, true);

        // Fetch existing app details if editing
        $old_app = !empty($existing_id) ? get_app_by_slug($existing_id) : null;
        $old_acf = $old_app['acf'] ?? [];

        // Handle APK File Upload or Direct Link
        $apk_url_input = trim($_POST['apk_url'] ?? '');
        $apk_filename = $old_acf['apk_file'] ?? '';

        if (!empty($apk_url_input)) {
            $apk_filename = $apk_url_input;
        }

        if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['apk_file']['tmp_name'];
            $apk_real_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['apk_file']['name']);
            $file_name = time() . '_' . $apk_real_name;
            $target_file = $apks_dir . $file_name;
            if (@move_uploaded_file($file_tmp, $target_file)) {
                @chmod($target_file, 0755);
                $apk_filename = 'uploads/apks/' . $file_name;
            }
        }

        if (empty($apk_filename)) {
            $apk_filename = 'uploads/apks/sample.apk';
        }

        // Handle App Icon Upload
        $icon_url = trim($_POST['icon_url'] ?? '');
        if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] === UPLOAD_ERR_OK) {
            $icon_tmp = $_FILES['icon_file']['tmp_name'];
            $icon_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['icon_file']['name']);
            $target_icon = $imgs_dir . $icon_name;
            if (@move_uploaded_file($icon_tmp, $target_icon)) {
                $icon_url = 'uploads/images/' . $icon_name;
            }
        }
        if (empty($icon_url)) {
            $icon_url = $old_acf['app_icon'] ?? 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=160&auto=format&fit=crop&q=80';
        }

        // Handle Screenshots Upload
        $screenshots = $old_acf['screenshots'] ?? [];
        if (isset($_FILES['screenshot_files']) && !empty($_FILES['screenshot_files']['name'][0])) {
            $new_shots = [];
            foreach ($_FILES['screenshot_files']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['screenshot_files']['error'][$key] === UPLOAD_ERR_OK) {
                    $s_name = time() . '_' . $key . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['screenshot_files']['name'][$key]);
                    $s_target = $imgs_dir . $s_name;
                    if (@move_uploaded_file($tmp_name, $s_target)) {
                        $new_shots[] = 'uploads/images/' . $s_name;
                    }
                }
            }
            if (!empty($new_shots)) {
                $screenshots = $new_shots;
            }
        }
        if (empty($screenshots)) {
            $screenshots = [$icon_url];
        }

        $slug_id = !empty($existing_id) ? $existing_id : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        $app_payload = [
            'id' => $slug_id,
            'title' => $title,
            'tagline' => $tagline,
            'category' => $category,
            'category_slug' => $category_slug,
            'acf' => [
                'apk_file' => $apk_filename,
                'app_version' => $version,
                'app_size' => $size,
                'app_rating' => $rating,
                'rating_count' => $old_acf['rating_count'] ?? '1,200',
                'app_developer' => $developer,
                'package_name' => $package_name,
                'min_android' => $min_android,
                'release_date' => date('F j, Y'),
                'download_count' => (int)($old_acf['download_count'] ?? 100),
                'app_icon' => $icon_url,
                'app_banner' => $old_acf['app_banner'] ?? $icon_url,
                'screenshots' => $screenshots,
                'features' => !empty($features) ? array_values($features) : ["Fast & intuitive mobile experience", "Regular feature updates"]
            ],
            'description' => $description
        ];

        save_app_data($app_payload);

        $message = !empty($existing_id) ? 'App updated successfully!' : 'App published successfully!';
        $editing_app = null;
    }
}

$all_published_apps = get_all_apps();

require_once __DIR__ . '/wp-content/themes/download-hub-theme/header.php';
?>

<main class="layout-container" style="padding-top: 2rem; padding-bottom: 4rem;">
    <div style="max-width: 900px; margin: 0 auto; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
        
        <?php if (!$is_admin) : ?>
            <!-- Login Form -->
            <div style="text-align: center; max-width: 400px; margin: 2rem auto;">
                <div style="width: 64px; height: 64px; background: var(--play-green-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--play-green)" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 700; margin-bottom: 0.5rem;">Admin Access Portal</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Enter Admin Passcode to manage apps (Default: <code>admin123</code>)</p>

                <?php if ($error) : ?>
                    <div style="background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid #EF4444; padding: 0.85rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-weight: 600; font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" style="display: grid; gap: 1.25rem;">
                    <input type="hidden" name="admin_action" value="login">
                    <div>
                        <input type="password" name="admin_passcode" required class="search-input" style="border-radius: var(--radius-md); text-align: center; font-size: 1.1rem; letter-spacing: 0.2em;" placeholder="Enter Admin Passcode">
                    </div>

                    <button type="submit" class="btn-primary" style="padding: 0.85rem; justify-content: center; font-size: 1rem;">
                        Login to Admin Portal
                    </button>
                </form>
            </div>
        <?php else : ?>
            <!-- Admin Dashboard Header -->
            <div class="admin-header-flex">
                <div>
                    <span class="security-badge-box" style="margin-bottom: 0.35rem;">✓ Admin Authenticated (Portable Data Store Engine)</span>
                    <h2 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 700;">ACF App Management Dashboard</h2>
                </div>
                <a href="admin-upload.php?action=logout" style="font-size: 0.85rem; color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.4rem 0.95rem; border-radius: var(--radius-full); font-weight: 600; flex-shrink: 0; align-self: flex-start;">Logout Admin</a>
            </div>

            <?php if ($message) : ?>
                <div style="background: var(--play-green-light); color: var(--play-green); border: 1px solid var(--play-green); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-weight: 600;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error) : ?>
                <div style="background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid #EF4444; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-weight: 600;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($editing_app) : ?>
                <div style="background: rgba(1, 135, 95, 0.1); border: 1px solid var(--play-green); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-weight: 600; color: var(--play-green);">✏️ Editing App: <strong><?php echo esc_html($editing_app['title']); ?></strong></span>
                    <a href="admin-upload.php" style="font-size: 0.8rem; color: var(--text-muted); text-decoration: underline;">Cancel Editing</a>
                </div>
            <?php endif; ?>

            <!-- App Upload / Edit Form -->
            <form action="admin-upload.php" method="POST" enctype="multipart/form-data" style="display: grid; gap: 1.25rem;">
                <input type="hidden" name="existing_app_id" value="<?php echo esc_attr($editing_app['id'] ?? ''); ?>">

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">App Name / Title *</label>
                    <input type="text" name="title" required class="search-input" style="border-radius: var(--radius-md);" placeholder="e.g. Pixel Editor Pro" value="<?php echo esc_attr($editing_app['title'] ?? ''); ?>">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Short Tagline</label>
                    <input type="text" name="tagline" class="search-input" style="border-radius: var(--radius-md);" placeholder="e.g. Next-Gen Mobile Photo Editing" value="<?php echo esc_attr($editing_app['tagline'] ?? ''); ?>">
                </div>

                <div class="admin-grid-2">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Category</label>
                        <?php $selected_cat = $editing_app['category'] ?? 'Tools'; ?>
                        <select name="category" class="search-input" style="border-radius: var(--radius-md); background: var(--bg-main);">
                            <option value="Tools" <?php echo ($selected_cat === 'Tools') ? 'selected' : ''; ?>>Tools & Utilities</option>
                            <option value="Photography" <?php echo ($selected_cat === 'Photography') ? 'selected' : ''; ?>>Photography & AI</option>
                            <option value="Health" <?php echo ($selected_cat === 'Health') ? 'selected' : ''; ?>>Health & Fitness</option>
                            <option value="Gaming" <?php echo ($selected_cat === 'Gaming') ? 'selected' : ''; ?>>Gaming</option>
                            <option value="Productivity" <?php echo ($selected_cat === 'Productivity') ? 'selected' : ''; ?>>Productivity</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Developer Name</label>
                        <input type="text" name="developer" class="search-input" style="border-radius: var(--radius-md);" placeholder="e.g. Apex Software" value="<?php echo esc_attr($editing_app['acf']['app_developer'] ?? ''); ?>">
                    </div>
                </div>

                <!-- ACF Upload Fields -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 0.5rem;">
                    <h4 style="font-family: var(--font-heading); color: var(--play-green); margin-bottom: 1.25rem;">ACF Custom Upload Fields</h4>
                    
                    <div style="margin-bottom: 1.25rem; background: rgba(1, 135, 95, 0.04); padding: 1.25rem; border-radius: var(--radius-md); border: 1.5px solid var(--play-green);">
                        <label style="display: block; font-weight: 700; color: var(--play-green); margin-bottom: 0.4rem; font-size: 1rem;">Direct APK File Upload (.apk file)</label>
                        <input type="file" name="apk_file" id="apkFileInput" accept=".apk,.zip" style="color: var(--text-muted); width: 100%; margin-bottom: 0.75rem;">
                        
                        <label style="display: block; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; font-size: 0.85rem;">Or Paste Direct Download URL Link</label>
                        <input type="url" name="apk_url" id="apkUrlInput" class="search-input" style="border-radius: var(--radius-md); background: var(--bg-surface);" placeholder="e.g. https://.../app.apk or GitHub/Drive link" value="<?php echo esc_attr(strpos($editing_app['acf']['apk_file'] ?? '', 'http') === 0 ? $editing_app['acf']['apk_file'] : ''); ?>">
                        
                        <?php if (!empty($editing_app['acf']['apk_file'])) : ?>
                            <span style="font-size: 0.8rem; color: var(--play-green); display: block; margin-top: 0.5rem; font-weight: 600;">Current Active File: <?php echo esc_html($editing_app['acf']['apk_file']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- App Icon Upload -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">App Icon Image Upload (.png, .jpg, .webp)</label>
                        <input type="file" name="icon_file" accept="image/*" style="color: var(--text-muted); margin-bottom: 0.5rem; width: 100%;">
                        <input type="url" name="icon_url" class="search-input" style="border-radius: var(--radius-md);" placeholder="Or paste App Icon Image URL..." value="<?php echo esc_attr($editing_app['acf']['app_icon'] ?? ''); ?>">
                    </div>

                    <!-- App Screenshots Upload -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">App Screenshots Upload (Select Multiple Images)</label>
                        <input type="file" name="screenshot_files[]" accept="image/*" multiple style="color: var(--text-muted); width: 100%;">
                        <span style="display: block; font-size: 0.8rem; color: var(--text-dim); margin-top: 0.25rem;">Select new screenshots to replace current gallery.</span>
                    </div>

                    <div class="admin-grid-3" style="margin-bottom: 1.25rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">App Version</label>
                            <input type="text" name="version" class="search-input" style="border-radius: var(--radius-md);" value="<?php echo esc_attr($editing_app['acf']['app_version'] ?? 'v1.0.0'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">File Size</label>
                            <input type="text" name="size" class="search-input" style="border-radius: var(--radius-md);" value="<?php echo esc_attr($editing_app['acf']['app_size'] ?? '32.5 MB'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Rating (1.0 to 5.0)</label>
                            <input type="text" name="rating" class="search-input" style="border-radius: var(--radius-md);" value="<?php echo esc_attr($editing_app['acf']['app_rating'] ?? '4.8'); ?>">
                        </div>
                    </div>

                    <div class="admin-grid-2" style="margin-bottom: 1.25rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Package Name</label>
                            <input type="text" name="package_name" class="search-input" style="border-radius: var(--radius-md);" value="<?php echo esc_attr($editing_app['acf']['package_name'] ?? 'com.example.app'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Min Android Version</label>
                            <input type="text" name="min_android" class="search-input" style="border-radius: var(--radius-md);" value="<?php echo esc_attr($editing_app['acf']['min_android'] ?? 'Android 8.0+'); ?>">
                        </div>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Key Features (One per line)</label>
                        <textarea name="features" rows="4" class="search-input" style="border-radius: var(--radius-md); font-family: inherit; resize: vertical;"><?php 
                            $feats = $editing_app['acf']['features'] ?? ["Fast & intuitive mobile experience", "Regular feature updates"];
                            echo esc_textarea(implode("\n", $feats)); 
                        ?></textarea>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Full App Description</label>
                        <textarea name="description" rows="5" class="search-input" style="border-radius: var(--radius-md); font-family: inherit; resize: vertical;" placeholder="Detailed overview of the app..."><?php echo esc_textarea($editing_app['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="padding: 0.95rem; font-size: 1.05rem; justify-content: center; border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(1, 135, 95, 0.25);">
                    <?php echo $editing_app ? 'Update App Details' : 'Publish App to Portal'; ?>
                </button>
            </form>

            <!-- Admin Passcode Change Form -->
            <div style="border-top: 1px solid var(--border-color); margin-top: 3rem; padding-top: 1.5rem;">
                <h4 style="font-family: var(--font-heading); margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-main);">Security: Change Admin Passcode</h4>
                <form action="" method="POST" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <input type="hidden" name="admin_action" value="change_passcode">
                    <input type="password" name="new_passcode" required class="search-input" style="border-radius: var(--radius-md); max-width: 250px;" placeholder="New Passcode">
                    <button type="submit" class="btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.85rem;">Update Passcode</button>
                </form>
            </div>

            <!-- Manage Published Apps List -->
            <?php if (!empty($all_published_apps)) : ?>
                <div style="border-top: 2px solid var(--border-color); margin-top: 3rem; padding-top: 2rem;">
                    <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 700; margin-bottom: 1.25rem;">Manage Published Apps (<?php echo count($all_published_apps); ?>)</h3>
                    
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($all_published_apps as $p_app) : ?>
                            <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <img src="<?php echo esc_url($p_app['acf']['app_icon'] ?? ''); ?>" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 1rem;"><?php echo esc_html($p_app['title']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo esc_html($p_app['category']); ?> &bull; <?php echo esc_html($p_app['acf']['app_version'] ?? 'v1.0.0'); ?></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="<?php echo esc_attr(urlencode($p_app['id'])); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; padding: 0.35rem 0.85rem; border-radius: var(--radius-full); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; font-weight: 500;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        View Page
                                    </a>
                                    <a href="admin-upload.php?edit=<?php echo urlencode($p_app['id']); ?>" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; padding: 0.35rem 0.85rem; border-radius: var(--radius-full); background: var(--play-green); color: #FFF; text-decoration: none; font-weight: 600;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        Edit
                                    </a>
                                    <a href="admin-upload.php?delete=<?php echo urlencode($p_app['id']); ?>" onclick="return confirm('Delete this app?');" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; padding: 0.35rem 0.85rem; border-radius: var(--radius-full); background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid #EF4444; text-decoration: none; font-weight: 600;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/wp-content/themes/download-hub-theme/footer.php'; ?>