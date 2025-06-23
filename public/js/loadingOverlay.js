$(document).ready(function () {
    // Hide the loading overlay when the page has fully loaded
    $("#loading-overlay").addClass("hidden");

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
    if (event.originalEvent.persisted) {
        $("#loading-overlay").addClass("hidden");
    }
});
