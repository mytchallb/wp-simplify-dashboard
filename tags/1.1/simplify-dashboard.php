<?php
/*
Plugin Name: Simplify Dashboard
Plugin URI: https://mytchall.dev/projects/wordpress-simplify-dashboard/
Description: Adds a toggle button to the admin bar to simplify the dashboard by hiding specific menu items.
Version: 1.1
Requires PHP: 7.4
Author: Mytchall Bransgrove
Author URI: https://mytchall.dev/about/
License: GPLv2 or later
*/

// Exit the script if accessed directly
if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function simplify_dashboard_enqueue_scripts() {
    $plugin_url = plugin_dir_url(__FILE__);
    
    // Enqueue Scripts/CSS
    wp_enqueue_style('simplify-dashboard-style', $plugin_url . 'css/simplify-dashboard.css', array(), '1.0.0');
    wp_enqueue_script('simplify-dashboard-script', $plugin_url . 'js/simplify-dashboard.js', array('jquery'), '1.0.0', true);
    
    $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
    
    // Pass PHP variables to JavaScript using wp_add_inline_script
    $script_data = array(
        'toggleOnSvgUrl' => esc_url($plugin_url . 'assets/toggle-on.svg'),
        'toggleOffSvgUrl' => esc_url($plugin_url . 'assets/toggle-off.svg'),
        'menuItems' => explode("\n", get_option('simplify_dashboard_menu_items', '')),
        'topbarItems' => array_values(array_filter(explode("\n", get_option('simplify_dashboard_topbar_items', '')), function($item) {
            return trim($item) !== "wp-admin-bar-simplify-dashboard-toggle";
        })),
        'isOn' => $is_on
    );
    wp_add_inline_script('simplify-dashboard-script', 'var simplifyDashboardData = ' . wp_json_encode($script_data) . ';', 'before');

    // Add inline CSS to hide elements immediately only if the toggle is on
    if ($is_on) {
        $inline_css = '';
        foreach ($script_data['topbarItems'] as $id) {
            $inline_css .= "#$id { display: none !important; }";
        }
        wp_add_inline_style('simplify-dashboard-style', $inline_css);
    }
}
add_action('admin_enqueue_scripts', 'simplify_dashboard_enqueue_scripts');

// Hide specific menu items
function simplify_dashboard_hide_menu_items() {
    $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
    if (!$is_on) {
        return;
    }

    $items_to_hide = explode("\n", get_option('simplify_dashboard_menu_items', ''));
    $items_to_hide = array_map('strtolower', array_map('trim', $items_to_hide));

    global $menu;

    foreach ($menu as $key => $item) {
        $menu_name_raw = $item[0] ?? '';
        $menu_name = strtolower(trim(wp_strip_all_tags($menu_name_raw)));

        foreach ($items_to_hide as $hide_item) {
            if (strpos($menu_name, $hide_item) !== false) {
                $menu[$key][4] .= ' simplify-dashboard-hidden';
                break;
            }
        }
    }
}
add_action('admin_menu', 'simplify_dashboard_hide_menu_items', 100);

// Add the toggle button to the admin bar
function simplify_dashboard_admin_bar($wp_admin_bar) {
    // Determine the initial state based on the `simplifyDashboard` cookie
    $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
    $plugin_url = plugin_dir_url(__FILE__);

    // Use the correct SVG URL based on the state
    $toggle_svg_url = $is_on ? $plugin_url . 'assets/toggle-on.svg' : $plugin_url . 'assets/toggle-off.svg';

    // Construct the HTML with the SVG
    $icon_html = '<div style="display: flex; align-items: center; cursor: pointer; width: 150px;">';
    $icon_html .= '<img src="' . esc_url($toggle_svg_url) . '" style="width: 20px; height: 20px; margin-right: 5px;" />';
    $icon_html .= 'Simplify Dashboard</div>';

    $wp_admin_bar->add_node(array(
        'id'    => 'simplify-dashboard-toggle',
        'title' => $icon_html, // Add the icon HTML here
        'meta'  => array('class' => 'simplify-dashboard-toggle')
    ));
}
add_action('admin_bar_menu', 'simplify_dashboard_admin_bar', 100);


// Add preload links to the head
add_action('wp_head', function () use ($plugin_url) {
    echo '<link rel="preload" href="' . esc_url($plugin_url . 'assets/toggle-on.svg') . '" as="image">';
    echo '<link rel="preload" href="' . esc_url($plugin_url . 'assets/toggle-off.svg') . '" as="image">';
});

// Include the settings page code
require_once plugin_dir_path(__FILE__) . 'settings-page.php';