<?php
// settings-page.php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


// Create the settings page
function simplify_dashboard_settings_page() {
    add_options_page(
        'Simplify Dashboard Settings',
        'Simplify Dashboard',
        'manage_options',
        'simplify-dashboard-settings',
        'simplify_dashboard_settings_page_html'
    );
}
add_action('admin_menu', 'simplify_dashboard_settings_page');

// Render the settings page
function simplify_dashboard_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle saving the settings
    if (isset($_POST['simplify_dashboard_menu_items']) || isset($_POST['simplify_dashboard_topbar_items'])) {
        check_admin_referer('simplify_dashboard_settings');
        
        if (isset($_POST['simplify_dashboard_menu_items'])) {
            $menu_items = sanitize_textarea_field(wp_unslash($_POST['simplify_dashboard_menu_items']));
            update_option('simplify_dashboard_menu_items', $menu_items);
        }

        if (isset($_POST['simplify_dashboard_topbar_items'])) {
            $topbar_items = sanitize_textarea_field(wp_unslash($_POST['simplify_dashboard_topbar_items']));
            update_option('simplify_dashboard_topbar_items', $topbar_items);
        }

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Get the existing options
    $menu_items = get_option('simplify_dashboard_menu_items', '');
    $topbar_items = get_option('simplify_dashboard_topbar_items', '');
    ?>
    <div class="wrap">
        <h1>Simplify Dashboard Settings</h1>
        <p>Simplify the WordPress admin experience by hiding seldom-used menu items.<br />A handy toggle provides quick access to show or hide items.</p>

        <form method="post">
            <?php wp_nonce_field('simplify_dashboard_settings'); ?>

            <h2>Sidebar Menu</h2>
            <p>Enter a list of menu item names (case-insensitive) to hide in the WordPress admin dashboard. One item per line, e.g.:</p>
            <pre>
Plugins
Settings
Yoast SEO
            </pre>
            <div style="max-width: 500px;">
                <textarea name="simplify_dashboard_menu_items" rows="10" cols="20" class="large-text"><?php echo esc_textarea($menu_items); ?></textarea>
            </div>

            <h2>Hide anything by ID</h2>
            <p>Hide an element by specifying it's HTML ID. One ID per line, e.g., <code>wp-admin-bar-wp-logo</code>.</p>
            <div style="max-width: 500px;">
                <textarea name="simplify_dashboard_topbar_items" rows="5" cols="20" class="large-text"><?php echo esc_textarea($topbar_items); ?></textarea>
            </div>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}