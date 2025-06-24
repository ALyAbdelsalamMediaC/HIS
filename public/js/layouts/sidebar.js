$(document).ready(function () {
    // Function to set the initial sidebar state based on screen width
    function setSidebarState() {
        if ($(window).width() <= 768) {
            // Mobile: Hide sidebar by default
            $(".sidebar").addClass("mobile-hidden").removeClass("active");
            $(".main-content").removeClass("collapsed");
            $(".mobile-backdrop").removeClass("active");
        } else {
            // Desktop: Show sidebar by default
            $(".sidebar").removeClass("mobile-hidden active");
            $(".main-content").removeClass("collapsed");
            $(".mobile-backdrop").removeClass("active");
        }
    }

    // Function to show mobile sidebar
    function showMobileSidebar() {
        $(".sidebar").removeClass("mobile-hidden").addClass("mobile-active");
        $(".mobile-backdrop").addClass("active");
        $("body").addClass("sidebar-mobile-open");
    }

    // Function to hide mobile sidebar
    function hideMobileSidebar() {
        $(".sidebar").addClass("mobile-hidden").removeClass("mobile-active");
        $(".mobile-backdrop").removeClass("active");
        $("body").removeClass("sidebar-mobile-open");
    }

    // Set initial sidebar state and active menu item based on URL
    setSidebarState();

    // Update the sidebar state when the window is resized
    $(window).resize(function () {
        setSidebarState();
    });

    // Highlight the active menu item based on the current URL
    $(".sidebar-menu ul li a").each(function () {
        var currentPath = window.location.pathname;
        var linkPath = new URL(this.href).pathname;

        if (
            currentPath === linkPath ||
            currentPath.startsWith(linkPath + "/")
        ) {
            $(this).parent().addClass("active");
        }
    });

    // Mobile hamburger menu toggle
    $("#mobileMenuToggle").click(function () {
        if ($(window).width() <= 768) {
            showMobileSidebar();
        }
    });

    // Mobile close button
    $("#mobileCloseBtn").click(function () {
        hideMobileSidebar();
    });

    // Mobile backdrop click to close sidebar
    $(".mobile-backdrop").click(function () {
        hideMobileSidebar();
    });

    // Close mobile sidebar when clicking on sidebar links (for better UX)
    $(".sidebar-menu a").click(function () {
        if ($(window).width() <= 768) {
            setTimeout(function () {
                hideMobileSidebar();
            }, 150); // Small delay for better visual feedback
        }
    });

    // Handle escape key to close mobile sidebar
    $(document).keydown(function (e) {
        if (e.key === "Escape" && $(window).width() <= 768) {
            hideMobileSidebar();
        }
    });
});
