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

    // Handle the export button click
    $(document).on("click", ".export-btn", function (e) {
        e.preventDefault(); // Prevent default link behavior

        var patientId = $(this).data("patient-id");
        var url = "/patients/export/" + patientId; // Adjusted to match your route

        $("#loading-overlay").removeClass("hidden");

        fetch(url, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    // Handle HTTP errors
                    throw new Error(
                        "Network response was not ok. Status: " +
                            response.status
                    );
                }
                // Get the filename from the Content-Disposition header
                var disposition = response.headers.get("Content-Disposition");
                var fileName = "export.csv"; // Default filename
                if (disposition && disposition.indexOf("attachment") !== -1) {
                    var filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    var matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        fileName = matches[1].replace(/['"]/g, "");
                    }
                }

                return response
                    .blob()
                    .then((blob) => ({ blob: blob, fileName: fileName }));
            })
            .then((obj) => {
                var blob = obj.blob;
                var fileName = obj.fileName;

                // Create a temporary URL for the Blob
                var downloadUrl = window.URL.createObjectURL(blob);

                // Create a link and trigger the download
                var a = document.createElement("a");
                a.href = downloadUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();

                // Revoke the temporary URL
                window.URL.revokeObjectURL(downloadUrl);

                $("#loading-overlay").addClass("hidden");
            })
            .catch((error) => {
                console.error("Export error:", error);
                $("#loading-overlay").addClass("hidden");
                // Use the showToast function
                showToast("Failed to export data. Please try again.", "danger");
            });
    });

     // Handle the export button click for admin
     $(document).on("click", ".export-btn-admin", function (e) {
        e.preventDefault(); // Prevent default link behavior

        var patientId = $(this).data("patient-id");
        var url = "/userpatients/export/" + patientId; // Adjusted to match your route

        $("#loading-overlay").removeClass("hidden");

        fetch(url, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    // Handle HTTP errors
                    throw new Error(
                        "Network response was not ok. Status: " +
                            response.status
                    );
                }
                // Get the filename from the Content-Disposition header
                var disposition = response.headers.get("Content-Disposition");
                var fileName = "export.csv"; // Default filename
                if (disposition && disposition.indexOf("attachment") !== -1) {
                    var filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    var matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        fileName = matches[1].replace(/['"]/g, "");
                    }
                }

                return response
                    .blob()
                    .then((blob) => ({ blob: blob, fileName: fileName }));
            })
            .then((obj) => {
                var blob = obj.blob;
                var fileName = obj.fileName;

                // Create a temporary URL for the Blob
                var downloadUrl = window.URL.createObjectURL(blob);

                // Create a link and trigger the download
                var a = document.createElement("a");
                a.href = downloadUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();

                // Revoke the temporary URL
                window.URL.revokeObjectURL(downloadUrl);

                $("#loading-overlay").addClass("hidden");
            })
            .catch((error) => {
                console.error("Export error:", error);
                $("#loading-overlay").addClass("hidden");
                // Use the showToast function
                showToast("Failed to export data. Please try again.", "danger");
            });
    });

    // Handle the “Export All Patients” click for admin
$(document).on("click", ".export-all-btn-admin", function (e) {
    e.preventDefault(); // Prevent default link behavior
    
    // Show the loading overlay
    $("#loading-overlay").removeClass("hidden");

    // We can either read the href from the element, or hardcode the route
    // Here we’ll just get the href attribute from the clicked link:
    let url = $(this).attr("href");

    fetch(url, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
    .then((response) => {
        if (!response.ok) {
            // Handle HTTP errors
            throw new Error(
                "Network response was not ok. Status: " + response.status
            );
        }
        // Get the filename from the Content-Disposition header
        let disposition = response.headers.get("Content-Disposition");
        let fileName = "export.csv"; // Default filename
        if (disposition && disposition.indexOf("attachment") !== -1) {
            let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            let matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
                fileName = matches[1].replace(/['"]/g, "");
            }
        }
        
        // Return the blob + filename
        return response
            .blob()
            .then((blob) => ({ blob: blob, fileName: fileName }));
    })
    .then((obj) => {
        let blob = obj.blob;
        let fileName = obj.fileName;

        // Create a temporary URL for the Blob
        let downloadUrl = window.URL.createObjectURL(blob);

        // Create a link and trigger the download
        let a = document.createElement("a");
        a.href = downloadUrl;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();

        // Revoke the temporary URL
        window.URL.revokeObjectURL(downloadUrl);

        // Hide the spinner
        $("#loading-overlay").addClass("hidden");
    })
    .catch((error) => {
        console.error("Export All error:", error);
        $("#loading-overlay").addClass("hidden");
        // Use your showToast function
        showToast("Failed to export data. Please try again.", "danger");
    });
});
});

// Hide the loading overlay when the page is fully loaded
$(window).on("load", function () {
    $("#loading-overlay").addClass("hidden");
});

// Handle page show events (e.g., when navigating back)
$(window).on("pageshow", function (event) {
    if (event.originalEvent.persisted) {
        $("#loading-overlay").addClass("hidden");
    }
});
