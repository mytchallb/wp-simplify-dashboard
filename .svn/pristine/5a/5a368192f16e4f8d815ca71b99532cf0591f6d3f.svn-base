// simplify-dashboard.js
jQuery(document).ready(function($) {
    // Ensure simplifyDashboardData is available
    if (typeof simplifyDashboardData === 'undefined') {
        console.error('Simplify Dashboard: Required data is missing.');
        return;
    }

    function updateToggleIcon() {
        var isOn = localStorage.getItem('simplifyDashboard') === 'on';
        var toggleButton = $('.simplify-dashboard-toggle');

        var svgIconUrl = isOn ? simplifyDashboardData.toggleOnSvgUrl : simplifyDashboardData.toggleOffSvgUrl;
        toggleButton.html('<div style="display: flex; align-items: center; cursor: pointer; width: 150px; padding: 0 10px;">' +
                          '<img src="' + svgIconUrl + '" style="width: 20px; height: 20px; margin-right: 5px;" />' +
                          'Simplify Dashboard</div>');
    }

    function setSimplifyState(isOn) {
        localStorage.setItem('simplifyDashboard', isOn ? 'on' : 'off');
        document.cookie = "simplifyDashboard=" + (isOn ? 'on' : 'off') + "; path=/";
        if (isOn) {
            simplifyDashboardHideMenuItems();
        } else {
            showHiddenElements();
        }
        updateToggleIcon();
    }

    // Initialize state
    setSimplifyState(simplifyDashboardData.isOn);

    $('.simplify-dashboard-toggle').on('click', function(e) {
        e.preventDefault();
        var isCurrentlyOn = localStorage.getItem('simplifyDashboard') === 'on';
        setSimplifyState(!isCurrentlyOn);
    });

    function showHiddenElements() {
        // Remove 'simplify-dashboard-hidden' class from menu items
        $('#adminmenu .simplify-dashboard-hidden').removeClass('simplify-dashboard-hidden').css('display', 'block'); 
    
        // Show the separators again
        $('.wp-menu-separator').css('display', '');
    
        // Show all hidden topbar items
        simplifyDashboardData.topbarItems.forEach(function(id) {
            $('#' + id).css('display', ''); // Remove inline 'display: none' from topbar items
        });

        $('#simplify-dashboard-style-inline-css').remove();
    }
    

    function simplifyDashboardHideMenuItems() {
        var itemsToHide = simplifyDashboardData.menuItems.map(function(item) {
            return item.trim().toLowerCase();
        });

        $('#adminmenu .wp-menu-name').each(function() {
            var menuName = $(this).clone().children().remove().end().text().trim().toLowerCase();

            if (itemsToHide.includes(menuName)) {
                var parent = $(this).closest('li.menu-top');
                parent.addClass('simplify-dashboard-hidden');
                if (parent.next().hasClass('wp-menu-separator')) {
                    parent.next().hide();
                }
            }
        });

        simplifyDashboardData.topbarItems.forEach(function(id) {
            $('#' + id).hide();
        });
    }
});