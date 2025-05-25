// toast.js

function showToast(messages, type = "danger") {
    // Create a unique ID for the toast
    const toastId = "toast-" + Date.now();

    // Build the toast HTML
    const toastHtml = `
  <div id="${toastId}" class="toast-container position-fixed bottom-0 end-0 p-4 z-index-11">
      <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
              <div class="toast-body">
                  ${
                      Array.isArray(messages)
                          ? messages
                                .map(
                                    (msg) =>
                                        `<div class="font-size-md">${msg}</div>`
                                )
                                .join("")
                          : `<div class="font-size-md">${messages}</div>`
                  }
              </div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
      </div>
  </div>
  `;

    // Append the toast to the body
    $("body").append(toastHtml);

    // Initialize and show the toast
    var toastEl = document.getElementById(toastId).querySelector(".toast");
    var toast = new bootstrap.Toast(toastEl);
    toast.show();

    // Remove the toast from the DOM after it hides
    toastEl.addEventListener("hidden.bs.toast", function () {
        document.getElementById(toastId).remove();
    });
}
