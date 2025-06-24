// public/js/validation.js

document.addEventListener("DOMContentLoaded", function () {
    // Get all forms on the page
    const forms = document.querySelectorAll("form");

    forms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            // Find the submit button within the form
            const submitButton = form.querySelector(
                'button[type="submit"], input[type="submit"]'
            );
            if (submitButton) {
                // Check if the button is already disabled to prevent double submissions
                if (submitButton.disabled) {
                    // Prevent the form from submitting again
                    event.preventDefault();
                    return;
                }
                // Disable the submit button
                submitButton.disabled = true;
                // Store the original button content to restore it later if needed
                submitButton.originalInnerHTML = submitButton.innerHTML;
                // Change the button content to show a spinner and 'Loading...' text
                submitButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading...
                `;
            }

            let isValid = true;

            // Clear previous errors
            clearErrors(form);

            // Get all form elements within the form
            const elements = form.querySelectorAll("input, textarea, select");

            elements.forEach(function (element) {
                const value = element.value.trim();
                const dataName =
                    element.getAttribute("data-name") || "This field";

                // Initialize an array to collect error messages for this field
                let messages = [];

                // Check for required fields (without using HTML 'required' attribute)
                if (element.hasAttribute("data-required") && value === "") {
                    messages.push(`${dataName} is required.`);
                } else {
                    // Only perform validations if the field is not empty or is required
                    // This ensures optional fields are validated only when they have content
                    if (value !== "" || element.hasAttribute("data-required")) {
                        // Custom validations based on data-validate attribute
                        const validations =
                            element.getAttribute("data-validate");
                        if (validations) {
                            const rules = validations.split("|");
                            rules.forEach(function (rule) {
                                switch (rule) {
                                    case "email":
                                        if (!validateEmail(value)) {
                                            messages.push(
                                                "Please enter a valid email address example@example.com."
                                            );
                                        }
                                        break;
                                    case "phone":
                                        if (!validatePhone(value)) {
                                            messages.push(
                                                "Please enter a valid phone number."
                                            );
                                        }
                                        break;
                                    case "password":
                                        if (value.length < 8) {
                                            messages.push(
                                                "Password must be at least 8 characters."
                                            );
                                        }
                                        if (!/[a-z]/.test(value)) {
                                            messages.push(
                                                "Password must include lowercase letters."
                                            );
                                        }
                                        if (!/[A-Z]/.test(value)) {
                                            messages.push(
                                                "Password must include uppercase letters."
                                            );
                                        }
                                        if (!/\d/.test(value)) {
                                            messages.push(
                                                "Password must include at least one number."
                                            );
                                        }
                                        break;
                                    case "password_confirmation":
                                        const password = form.querySelector(
                                            'input[name="password"]'
                                        ).value;
                                        if (value !== password) {
                                            messages.push(
                                                "Passwords must match."
                                            );
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            });
                        }
                    }
                }

                if (messages.length > 0) {
                    isValid = false;
                    displayError(element, messages);
                }
            });

            if (!isValid) {
                // Prevent form submission if validation fails
                event.preventDefault();
                // Re-enable the submit button and restore its original content
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.originalInnerHTML;
                }
            } else {
                // Proceed with form submission
                // The submit button remains disabled, and the loading spinner is displayed
            }
        });
    });

    function validateEmail(email) {
        const re =
            /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/;
        return re.test(email);
    }

    function validatePhone(phone) {
        const re =
            /^\+?\d{1,4}?[-.\s]?(\d{1,3})?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/;
        return re.test(phone);
    }

    function displayError(element, messages) {
        // Find the error container based on the field name
        const errorContainerId = element.name + "-error-container";
        const errorContainer = document.getElementById(errorContainerId);

        if (errorContainer) {
            errorContainer.innerHTML = createErrorHtml(messages);
        }
    }

    function createErrorHtml(messages) {
        let html = '<ul class="error-input-login">';
        messages.forEach(function (message) {
            html += "<li>" + message + "</li>";
        });
        html += "</ul>";
        return html;
    }

    function clearErrors(form) {
        const errorContainers = form.querySelectorAll(
            '[id$="-error-container"]'
        );
        errorContainers.forEach(function (container) {
            container.innerHTML = "";
        });
    }
});
