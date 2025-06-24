// Add this JavaScript to handle input label positioning
document.addEventListener("DOMContentLoaded", function () {
    // Function to check if input has content and add/remove class
    function handleInputContent(input) {
        if (input.value.trim() !== "") {
            input.classList.add("has-content");
        } else {
            input.classList.remove("has-content");
        }
    }

    // Get all form inputs
    const formInputs = document.querySelectorAll(".input-form-inner-login");

    formInputs.forEach(function (input) {
        // Check initial state (for cases with old() values)
        handleInputContent(input);

        // Add event listeners
        input.addEventListener("input", function () {
            handleInputContent(this);
        });

        input.addEventListener("blur", function () {
            handleInputContent(this);
        });
    });
});
