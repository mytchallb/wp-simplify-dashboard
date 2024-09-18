<?php
/*
Plugin Name: Simplify Dashboard
Plugin URI: https://mytchall.dev/projects/wordpress-simplify-dashboard/
Description: Adds a toggle button to the admin bar to simplify the dashboard by hiding specific menu items.
Version: 1.0
Requires PHP: 7.4
Author: Mytchall Bransgrove
Author URI: https://mytchall.dev/about/
License: GPLv2 or later
*/

// SVG Icon Variables
$toggle_on_svg = '<svg width="20" height="20" style="width: 20px; height: 20px; fill: #47b050; margin-right: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192s-86-192-192-192L192 64zm192 96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg>';
$toggle_off_svg = '<svg width="20" height="20" style="width: 20px; height: 20px; fill: #a8aaad; margin-right: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M384 128c70.7 0 128 57.3 128 128s-57.3 128-128 128l-192 0c-70.7 0-128-57.3-128-128s57.3-128 128-128l192 0zM576 256c0-106-86-192-192-192L192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192zM192 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/></svg>';


// Hide specific menu items
function simplify_dashboard_hide_menu_items()
{
  $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
  if (!$is_on) {
    return;
  }

  $items_to_hide = explode("\n", get_option('simplify_dashboard_menu_items', ''));
  $items_to_hide = array_map('strtolower', array_map('trim', $items_to_hide));

  global $menu;

  // Hide sidebar menu items
  foreach ($menu as $key => $item) {
    $menu_name_raw = $item[0] ?? '';
    $menu_name = strtolower(trim(wp_strip_all_tags($menu_name_raw)));

    foreach ($items_to_hide as $hide_item) {
      if ($hide_item == "wp-admin-bar-simplify-dashboard-toggle") continue;

      if (strpos($menu_name, $hide_item) !== false) {
        // Instead of  remove_menu_page($item[2]); add a hidden class
        $menu[$key][4] .= ' hidden';
        break;
      }
    }
  }

  // Hide elements by ID
  $elements_to_hide_by_id = explode("\n", get_option('simplify_dashboard_topbar_items', ''));
  $elements_to_hide_by_id = array_map('trim', $elements_to_hide_by_id);

  add_action('admin_head', function () use ($elements_to_hide_by_id) {
    echo '<style>';
    foreach ($elements_to_hide_by_id as $id) {
      if (!empty($id) && $id != "wp-admin-bar-simplify-dashboard-toggle") {
        echo '#' . esc_attr($id) . ' { display: none !important; }';
      }
    }
    echo '</style>';
  });
}
// Call the function to hide menu items
add_action('admin_menu', 'simplify_dashboard_hide_menu_items', 100);


// Inject custom CSS to hide separators
function simplify_dashboard_custom_css()
{
  $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
  if (!$is_on) {
    return;
  }
?>
  <style>
    /* Hide separators in the admin menu */
    .wp-menu-separator {
      display: none !important;
    }
  </style>
<?php
}
add_action('admin_head', 'simplify_dashboard_custom_css');




// Add the toggle button to the admin bar
function simplify_dashboard_admin_bar($wp_admin_bar)
{
  global $toggle_on_svg, $toggle_off_svg;

  $is_on = isset($_COOKIE['simplifyDashboard']) && $_COOKIE['simplifyDashboard'] === 'on';
  $icon = $is_on ? $toggle_on_svg : $toggle_off_svg;

  $wp_admin_bar->add_node(array(
    'id'    => 'simplify-dashboard-toggle',
    'title' => '<div style="display: flex; align-items: center; cursor: pointer; width: 150px;">' . $icon . 'Simplify Dashboard</div>',
    'meta'  => array('class' => 'simplify-dashboard-toggle')
  ));
}
add_action('admin_bar_menu', 'simplify_dashboard_admin_bar', 100);



// Add JavaScript to handle the toggle functionality
function simplify_dashboard_toggle_script()
{
?>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      // Define SVG icons as JavaScript variables
      var toggleOnSvg = '<svg width="20" height="20" style="width: 20px; height: 20px; fill: #47b050; margin-right: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192s-86-192-192-192L192 64zm192 96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg>';
      var toggleOffSvg = '<svg width="20" height="20" style="width: 20px; height: 20px; fill: #a8aaad; margin-right: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M384 128c70.7 0 128 57.3 128 128s-57.3 128-128 128l-192 0c-70.7 0-128-57.3-128-128s57.3-128 128-128l192 0zM576 256c0-106-86-192-192-192L192 64C86 64 0 150 0 256S86 448 192 448l192 0c106 0 192-86 192-192zM192 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/></svg>';

      function updateToggleIcon() {
        var isOn = localStorage.getItem('simplifyDashboard') === 'on';
        var toggleButton = $('.simplify-dashboard-toggle');
        toggleButton.html('<div style="display: flex; align-items: center; cursor: pointer; width: 150px; padding: 0 10px;">' + (isOn ? toggleOnSvg : toggleOffSvg) + 'Simplify Dashboard</div>');
      }

      if (localStorage.getItem('simplifyDashboard') === 'on') {
        simplifyDashboardHideMenuItems();
      }
      updateToggleIcon();

      $('.simplify-dashboard-toggle').on('click', function(e) {
        e.preventDefault();
        var isCurrentlyOn = localStorage.getItem('simplifyDashboard') === 'on';

        if (isCurrentlyOn) {
          localStorage.setItem('simplifyDashboard', 'off');
          document.cookie = "simplifyDashboard=off; path=/";
          // Instead of reloading, show hidden elements
          showHiddenElements();
        } else {
          localStorage.setItem('simplifyDashboard', 'on');
          document.cookie = "simplifyDashboard=on; path=/";
          simplifyDashboardHideMenuItems();
        }
        updateToggleIcon();
      });

      // Function to show hidden elements
      function showHiddenElements() {
        $('#adminmenu .wp-menu-name').each(function() {
          $(this).closest('li.menu-top').show();
        });
        $('.wp-menu-separator').show();

        var elementsToHideById = <?php echo wp_json_encode(explode("\n", get_option('simplify_dashboard_topbar_items', ''))); ?>;
        elementsToHideById.forEach(function(id) {
          if (id != "wp-admin-bar-simplify-dashboard-toggle") {
            $('#' + id).show();
          }
        });
      }

      // Function to hide specific menu items
      function simplifyDashboardHideMenuItems() {
        var itemsToHide = <?php echo wp_json_encode(explode("\n", get_option('simplify_dashboard_menu_items', ''))); ?>;
        itemsToHide = itemsToHide.map(function(item) {
          return item.trim().toLowerCase();
        });

        $('#adminmenu .wp-menu-name').each(function() {
          var menuName = $(this).clone().children().remove().end().text().trim().toLowerCase();

          if (itemsToHide.includes(menuName)) {
            var parent = $(this).closest('li.menu-top');
            parent.hide();
            if (parent.next().hasClass('wp-menu-separator')) {
              parent.next().hide();
            }
          }
        });

        // Hide elements by ID
        var elementsToHideById = <?php echo wp_json_encode(explode("\n", get_option('simplify_dashboard_topbar_items', ''))); ?>;
        elementsToHideById = elementsToHideById.map(function(item) {
          return item.trim();
        });

        elementsToHideById.forEach(function(id) {
          var element = $('#' + id);
          if (element.length) {
            if (id != "wp-admin-bar-simplify-dashboard-toggle") {
              element.hide();
            }
          }
        });
      }
    });
  </script>
<?php
}
add_action('admin_footer', 'simplify_dashboard_toggle_script');

// Create the settings page
function simplify_dashboard_settings_page()
{
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
function simplify_dashboard_settings_page_html()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  // Handle saving the sidebar menu items
  if (isset($_POST['simplify_dashboard_menu_items'])) {
    check_admin_referer('simplify_dashboard_settings');
    $menu_items = sanitize_textarea_field(wp_unslash($_POST['simplify_dashboard_menu_items']));
    update_option('simplify_dashboard_menu_items', $menu_items);
  }

  // Handle saving the element IDs to hide
  if (isset($_POST['simplify_dashboard_topbar_items'])) {
    check_admin_referer('simplify_dashboard_settings');
    $topbar_items = sanitize_textarea_field(wp_unslash($_POST['simplify_dashboard_topbar_items']));
    update_option('simplify_dashboard_topbar_items', $topbar_items);
    echo '<div class="updated"><p>Settings saved.</p></div>';
  }

  // Get the existing options
  $menu_items = get_option('simplify_dashboard_menu_items', '');
  $topbar_items = get_option('simplify_dashboard_topbar_items', '');
?>
  <div class="wrap">
    <h1>Simplify Dashboard Settings</h1>
    <p>Simplify the WordPress admin experience by hiding seldom-used menu items.<br />A handy toggle provides quick access to show or hide items.</p>

    <!-- Sidebar Menu Section -->
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

      <!-- Elements by ID Section -->
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
