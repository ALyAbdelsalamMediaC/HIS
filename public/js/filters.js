// filters.js

// Debounce function to delay execution
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize filters with dynamic configuration
function initializeFilters(config = {}) {
    const {
        containerSelector = ".filters-container",
        searchSelector = "#search_input",
        filterSelector = "select.filter-select",
        dateFilterSelector = "input.filter-date", // Added date filter selector
        resetBtnSelector = "#reset-filters",
        debounceDelay = 1000,
    } = config;

    // Get DOM elements from the container
    const container = document.querySelector(containerSelector);
    if (!container) {
        console.error(
            `Container with selector "${containerSelector}" not found.`
        );
        return;
    }

    const baseUrl = container.dataset.url;
    const searchInput = document.querySelector(searchSelector);
    const resetBtn = document.querySelector(resetBtnSelector);

    // Function to update URL and reload page with query parameters
    function updateFilters() {
        const search = searchInput ? searchInput.value : "";

        // Get all filter selects (including Select2 enhanced ones)
        const filterSelects = document.querySelectorAll(filterSelector);
        // Get all date inputs
        const dateInputs = document.querySelectorAll(dateFilterSelector);

        const queryParams = {};

        // Process regular and Select2 filters
        filterSelects.forEach((filter) => {
            // For Select2 multi-select, we need to handle differently
            if (
                $(filter).hasClass("select2-hidden-accessible") &&
                filter.multiple
            ) {
                const values = $(filter).val();
                if (values && values.length) {
                    queryParams[filter.name] = values
                        .map((v) => encodeURIComponent(v))
                        .join(",");
                }
            } else if (filter.value) {
                queryParams[filter.name] = encodeURIComponent(filter.value);
            }
        });

        // Process date inputs
        dateInputs.forEach((dateInput) => {
            if (dateInput.value) {
                queryParams[dateInput.name] = encodeURIComponent(
                    dateInput.value
                );
            }
        });

        if (search) {
            queryParams.search = encodeURIComponent(search);
        }

        let query = "?";
        for (const [key, value] of Object.entries(queryParams)) {
            query += `${key}=${value}&`;
        }
        query = query.endsWith("&") ? query.slice(0, -1) : query;

        window.location.href = baseUrl + query;
    }

    // Function to reset all filters
    function resetFilters() {
        // Clear search input
        if (searchInput) {
            searchInput.value = "";
        }

        // Reset all select filters
        const filterSelects = document.querySelectorAll(filterSelector);
        filterSelects.forEach((filter) => {
            // For Select2 elements
            if ($(filter).data("select2")) {
                $(filter).val(null).trigger("change");
            } else {
                // Regular select elements
                filter.selectedIndex = 0;
            }
        });

        // Reset all date inputs
        const dateInputs = document.querySelectorAll(dateFilterSelector);
        dateInputs.forEach((dateInput) => {
            dateInput.value = "";
        });

        // Redirect to base URL without query parameters
        window.location.href = baseUrl;
    }

    // Add debounced event listener for search input
    if (searchInput) {
        searchInput.addEventListener(
            "input",
            debounce(updateFilters, debounceDelay)
        );

        // Add event listener for Enter key press
        searchInput.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Prevent form submission if within a form
                updateFilters(); // Immediately update filters without debounce
            }
        });
    }

    // Add event listeners for filter selects (both regular and Select2)
    const filterSelects = document.querySelectorAll(filterSelector);
    filterSelects.forEach((filter) => {
        // For Select2 elements, we need to listen to the select2:select and select2:unselect events
        if ($(filter).data("select2")) {
            $(filter).on("select2:select select2:unselect", function () {
                updateFilters();
            });
        } else {
            // Regular select elements
            filter.addEventListener("change", updateFilters);
        }
    });

    // Add event listeners for date inputs
    const dateInputs = document.querySelectorAll(dateFilterSelector);
    dateInputs.forEach((dateInput) => {
        dateInput.addEventListener("change", updateFilters);
    });

    // Add event listener for reset button
    if (resetBtn) {
        resetBtn.addEventListener("click", resetFilters);
    }
}

// Initialize with default config when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Wait a bit to ensure Select2 is initialized first
    setTimeout(() => {
        initializeFilters();
    }, 100);
});
