<?php
/**
 * Plugin Name: Download-Hub ACF & Custom Fields Engine
 * Description: Advanced Custom Fields & App Management Engine for Download-Hub WordPress Platform.
 * Version: 1.0.0
 * Author: Download-Hub Team
 */

if (!defined('ABSPATH')) {
    exit;
}

class DownloadHubACFEngine {

    public function __construct() {
        add_action('init', [$this, 'register_app_cpt']);
        add_action('init', [$this, 'register_acf_field_group']);
        add_action('add_meta_boxes', [$this, 'add_custom_meta_boxes']);
        add_action('save_post_app', [$this, 'save_custom_meta_data']);
    }

    public function register_app_cpt() {
        $labels = [
            'name'                  => 'Android Apps',
            'singular_name'         => 'App',
            'menu_name'             => 'Apps & Games',
            'add_new'               => 'Add New App',
            'add_new_item'          => 'Add New App',
            'edit_item'             => 'Edit App',
            'new_item'              => 'New App',
            'view_item'             => 'View App',
            'search_items'          => 'Search Apps',
            'not_found'             => 'No Apps Found',
            'not_found_in_trash'    => 'No Apps found in Trash'
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'app'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-smartphone',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields']
        ];

        register_post_type('app', $args);

        // Register Category Taxonomy
        register_taxonomy('app_category', ['app'], [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => 'App Categories',
                'singular_name'     => 'App Category',
                'search_items'      => 'Search Categories',
                'all_items'         => 'All Categories',
                'edit_item'         => 'Edit Category',
                'add_new_item'      => 'Add New Category'
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'app-category']
        ]);
    }

    public function register_acf_field_group() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_download_hub_app_details',
                'title' => 'Download-Hub ACF App Meta Fields',
                'fields' => [
                    [
                        'key' => 'field_apk_file',
                        'label' => 'APK File Upload',
                        'name' => 'apk_file',
                        'type' => 'file',
                        'instructions' => 'Upload the Android APK file for this app.',
                        'mime_types' => 'apk,zip',
                        'return_format' => 'url'
                    ],
                    [
                        'key' => 'field_app_version',
                        'label' => 'App Version',
                        'name' => 'app_version',
                        'type' => 'text',
                        'placeholder' => 'e.g. v3.4.2 Pro'
                    ],
                    [
                        'key' => 'field_app_size',
                        'label' => 'File Size',
                        'name' => 'app_size',
                        'type' => 'text',
                        'placeholder' => 'e.g. 48.5 MB'
                    ],
                    [
                        'key' => 'field_app_rating',
                        'label' => 'App Rating (1.0 to 5.0)',
                        'name' => 'app_rating',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 5,
                        'step' => 0.1,
                        'default_value' => '4.8'
                    ],
                    [
                        'key' => 'field_app_developer',
                        'label' => 'Developer / Author Name',
                        'name' => 'app_developer',
                        'type' => 'text',
                        'placeholder' => 'e.g. Apex Studio Labs'
                    ],
                    [
                        'key' => 'field_package_name',
                        'label' => 'Android Package Name',
                        'name' => 'package_name',
                        'type' => 'text',
                        'placeholder' => 'e.g. com.example.app'
                    ],
                    [
                        'key' => 'field_min_android',
                        'label' => 'Minimum Android Version',
                        'name' => 'min_android',
                        'type' => 'text',
                        'placeholder' => 'e.g. Android 8.0+'
                    ],
                    [
                        'key' => 'field_app_features',
                        'label' => 'App Features (One per line)',
                        'name' => 'app_features',
                        'type' => 'textarea',
                        'placeholder' => 'High Definition Output&#10;No background watermark&#10;Fast rendering'
                    ]
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'app'
                        ]
                    ]
                ]
            ]);
        }
    }

    public function add_custom_meta_boxes() {
        add_meta_box(
            'download_hub_acf_metabox',
            'Download-Hub ACF App Specifications & Uploads',
            [$this, 'render_acf_metabox'],
            'app',
            'normal',
            'high'
        );
    }

    public function render_acf_metabox($post) {
        wp_nonce_field('download_hub_save_meta', 'download_hub_nonce');
        $apk_file = get_post_meta($post->ID, 'apk_file', true);
        $app_version = get_post_meta($post->ID, 'app_version', true);
        $app_size = get_post_meta($post->ID, 'app_size', true);
        $app_rating = get_post_meta($post->ID, 'app_rating', true);
        $app_developer = get_post_meta($post->ID, 'app_developer', true);
        $package_name = get_post_meta($post->ID, 'package_name', true);
        $min_android = get_post_meta($post->ID, 'min_android', true);
        $app_features = get_post_meta($post->ID, 'app_features', true);
        ?>
        <div style="font-family: system-ui; padding: 10px;">
            <p><strong>ACF Integrated App Metadata Fields:</strong></p>
            <table class="form-table">
                <tr>
                    <th><label for="apk_file">APK File Upload Path / URL</label></th>
                    <td><input type="text" id="apk_file" name="apk_file" value="<?php echo esc_attr($apk_file); ?>" class="large-text" placeholder="uploads/apks/myapp.apk"></td>
                </tr>
                <tr>
                    <th><label for="app_version">App Version</label></th>
                    <td><input type="text" id="app_version" name="app_version" value="<?php echo esc_attr($app_version); ?>" class="regular-text" placeholder="v3.4.2"></td>
                </tr>
                <tr>
                    <th><label for="app_size">App File Size</label></th>
                    <td><input type="text" id="app_size" name="app_size" value="<?php echo esc_attr($app_size); ?>" class="regular-text" placeholder="45.2 MB"></td>
                </tr>
                <tr>
                    <th><label for="app_rating">Rating Stars (1.0 - 5.0)</label></th>
                    <td><input type="number" step="0.1" id="app_rating" name="app_rating" value="<?php echo esc_attr($app_rating); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th><label for="app_developer">Developer Name</label></th>
                    <td><input type="text" id="app_developer" name="app_developer" value="<?php echo esc_attr($app_developer); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="package_name">Package Identifier</label></th>
                    <td><input type="text" id="package_name" name="package_name" value="<?php echo esc_attr($package_name); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="min_android">Android Requirement</label></th>
                    <td><input type="text" id="min_android" name="min_android" value="<?php echo esc_attr($min_android); ?>" class="regular-text" placeholder="Android 8.0+"></td>
                </tr>
                <tr>
                    <th><label for="app_features">Key Features (One per line)</label></th>
                    <td><textarea id="app_features" name="app_features" rows="4" class="large-text"><?php echo esc_textarea($app_features); ?></textarea></td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function save_custom_meta_data($post_id) {
        if (!isset($_POST['download_hub_nonce']) || !wp_verify_nonce($_POST['download_hub_nonce'], 'download_hub_save_meta')) {
            return;
        }

        $fields = ['apk_file', 'app_version', 'app_size', 'app_rating', 'app_developer', 'package_name', 'min_android', 'app_features'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}

new DownloadHubACFEngine();
