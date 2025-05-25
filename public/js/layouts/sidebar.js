$(document).ready(function () {
    // Function to set the initial sidebar state based on screen width
    function setSidebarState() {
        if ($(window).width() <= 768) {
            $(".sidebar").addClass("active");
            $(".main-content").addClass("collapsed");
        } else {
            $(".sidebar").removeClass("active");
            $(".main-content").removeClass("collapsed");
        }
    }

    // Set initial sidebar state and active menu item based on URL
    setSidebarState();

    // Update the sidebar state when the window is resized
    $(window).resize(function () {
        setSidebarState();
    });

    // Highlight the active menu item based on the current URL
    $(".sidebar-menu ul li a").each(function () {
        // Get the current URL path without query parameters
        var currentPath = window.location.pathname;

        // Get the path from the link's href
        var linkPath = new URL(this.href).pathname;

        // Check if the current URL starts with the link path
        // This will match both exact URLs and subpages
        if (
            currentPath === linkPath ||
            currentPath.startsWith(linkPath + "/")
        ) {
            $(this).parent().addClass("active");
        }
    });

    // Toggle sidebar open/close on button click
    $(".sidebar-open-btn").click(function () {
        $(".sidebar").toggleClass("active");
        $(".main-content").toggleClass("collapsed");
    });
});
