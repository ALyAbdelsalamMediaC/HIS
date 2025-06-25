$(document).ready(function () {
    // Always hide the overlay on page load
    $("#loading-overlay").addClass("hidden");

    // Exclude overlay logic for all /content/videos* pages
    var isVideoPage = /^\/content\/videos(\/.*)?$/.test(
        window.location.pathname
    );
    if (isVideoPage) return;

    // Show the loading overlay when navigating to a new page
    $(document).on(
        "click",
        "a:not([target='_blank']):not([href^='#'])",
        function () {
            $("#loading-overlay").removeClass("hidden");
        }
    );
});

// Handle page show events (e.g., when navigating back)
$(window).on("pageshow", function (event) {
    var isVideoPage = /^\/content\/videos(\/.*)?$/.test(
        window.location.pathname
    );
    if (isVideoPage) return;
    if (event.originalEvent.persisted) {
        $("#loading-overlay").addClass("hidden");
    }
});
