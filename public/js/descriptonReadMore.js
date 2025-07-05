// Read More functionality for video and article descriptions
function initializeDescriptionReadMore() {
    const maxHeight = 80; // Maximum height in pixels before showing "Read more"

    document
        .querySelectorAll(".description-text")
        .forEach(function (textElement) {
            const contentWrapper = textElement.closest(
                ".description-content-wrapper"
            );
            const readMoreBtn = contentWrapper.querySelector(".read-more-btn");
            const readLessBtn = contentWrapper.querySelector(".read-less-btn");

            // Remove existing event listeners to prevent duplicates
            if (readMoreBtn) {
                readMoreBtn.replaceWith(readMoreBtn.cloneNode(true));
            }
            if (readLessBtn) {
                readLessBtn.replaceWith(readLessBtn.cloneNode(true));
            }

            // Get fresh references after cloning
            const newReadMoreBtn =
                contentWrapper.querySelector(".read-more-btn");
            const newReadLessBtn =
                contentWrapper.querySelector(".read-less-btn");

            if (textElement.scrollHeight > maxHeight) {
                // Content is longer than max height, show "Read more" button
                textElement.style.maxHeight = maxHeight + "px";
                textElement.style.overflow = "hidden";
                textElement.classList.add("collapsed");
                newReadMoreBtn.style.display = "inline-block";

                // Read More button click
                newReadMoreBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    textElement.style.maxHeight = "none";
                    textElement.style.overflow = "visible";
                    textElement.classList.remove("collapsed");
                    newReadMoreBtn.style.display = "none";
                    newReadLessBtn.style.display = "inline-block";
                });

                // Read Less button click
                newReadLessBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    textElement.style.maxHeight = maxHeight + "px";
                    textElement.style.overflow = "hidden";
                    textElement.classList.add("collapsed");
                    newReadLessBtn.style.display = "none";
                    newReadMoreBtn.style.display = "inline-block";
                });
            } else {
                // Content is short enough, hide both buttons
                newReadMoreBtn.style.display = "none";
                newReadLessBtn.style.display = "none";
            }
        });
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    initializeDescriptionReadMore();
});

// Also initialize when the page is fully loaded (for images and other content)
window.addEventListener("load", function () {
    initializeDescriptionReadMore();
});

// Export function for external use
window.initializeDescriptionReadMore = initializeDescriptionReadMore;
