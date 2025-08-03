// AJAX Review System - For review replies (no likes)
class AjaxReviews {
    constructor(config = {}) {
        this.config = {
            addReviewEndpoint: '/reviews/add',
            getReviewHtmlEndpoint: '/reviews',
            addReplyEndpoint: '/reviews/reply',
            deleteReplyEndpoint: '/reviews',
            getReplyHtmlEndpoint: '/reviews/reply',
            ...config
        };
        // Try to read config from DOM if not passed
        const container = document.querySelector('.reviews-list-container[data-ajax-review]');
        if (container) {
            try {
                const domConfig = JSON.parse(container.getAttribute('data-ajax-review'));
                this.config = { ...this.config, ...domConfig };
            } catch (e) {}
        }
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                        document.querySelector('input[name="_token"]')?.value;
        this.init();
    }
    init() {
        this.bindEvents();
        this.setupModalDeleteHandler();
        this.initializeReviewReplyInputs();
    }
    bindEvents() {
        document.addEventListener('click', (e) => {
            // Handle reply submit (div or button)
            if (e.target.closest('.review-reply-submit-btn')) {
                e.preventDefault();
                this.handleReplySubmit(e.target.closest('.review-reply-input-wrapper'));
            }
            // Handle reply button
            if (e.target.closest('.review-reply-btn')) {
                e.preventDefault();
                this.toggleReplyInput(e.target.closest('.review-reply-btn'));
            }
        });
        // Enter key submit for review reply textarea
        document.addEventListener('keydown', (e) => {
            if (
                e.target.classList.contains('review-reply-textarea') &&
                e.key === 'Enter' && !e.shiftKey
            ) {
                e.preventDefault();
                const wrapper = e.target.closest('.review-reply-input-wrapper');
                if (wrapper && e.target.value.trim().length > 0) {
                    this.handleReplySubmit(wrapper);
                }
            }
        });
    }
    initializeReviewReplyInputs() {
        document.querySelectorAll('.review-reply-textarea').forEach(textarea => {
            const submitBtn = textarea?.closest('.review-reply-input-wrapper')?.querySelector('.review-reply-submit-btn');
            if (textarea && submitBtn) {
                // Auto-resize functionality
                const autoResize = () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
                    // Enable/disable submit button based on content
                    const hasContent = textarea.value.trim().length > 0;
                    submitBtn.style.opacity = hasContent ? '1' : '0.5';
                    submitBtn.style.pointerEvents = hasContent ? 'auto' : 'none';
                };
                // Handle Enter key (handled globally)
                // Auto-resize on input
                textarea.addEventListener('input', autoResize);
                // Initial resize
                autoResize();
            }
        });
    }
    async addReply(parentId, content, mediaId) {
        try {
            const response = await fetch(`${this.config.addReplyEndpoint}/${mediaId}/${parentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content })
            });
            const data = await response.json();
            if (data.success) {
                // Clear and reset the textarea
                const textarea = document.querySelector(`#review-reply-comment-${parentId}`);
                if (textarea) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    textarea.focus(); // Keep focus for better UX
                }
                
                // Update the reply count in the DOM
                const replyCountSpan = document.getElementById(`reply-count-${parentId}`);
                if (replyCountSpan) {
                    let current = parseInt(replyCountSpan.textContent) || 0;
                    replyCountSpan.textContent = (current + 1) + ' Replies';
                }

                // Ensure the replies container exists or create it
                let repliesContainer = document.querySelector(`#review-replies-${parentId}`);
                if (!repliesContainer) {
                    repliesContainer = document.createElement('div');
                    repliesContainer.id = `review-replies-${parentId}`;
                    repliesContainer.className = 'replies-container';
                    repliesContainer.style.marginLeft = '40px';
                    repliesContainer.style.marginTop = '16px';
                    const parentReviewContainer = document.querySelector(`#reply-container-${parentId}`);
                    if (parentReviewContainer) {
                        parentReviewContainer.appendChild(repliesContainer);
                    }
                }

                // Immediately show the new reply while waiting for the server response
                if (data.reply && data.reply.content) {
                    const tempReply = document.createElement('div');
                    tempReply.id = `review-reply-${data.reply.id}`;
                    tempReply.className = 'mb-2 comment-container';
                    tempReply.style.border = '1px solid #EDEDED';
                    tempReply.style.paddingLeft = '16px';
                    tempReply.innerHTML = `
                        <div class="gap-3 d-flex align-items-start">
                            <div class="comment-container-user-icon">
                                <x-svg-icon name="user" size="18" color="#35758c" />
                            </div>
                            <div class="w-100">
                                <h4 class="h5-semibold">${data.reply.user_name || 'You'}</h4>
                                <span class="h6-ragular" style="color:#ADADAD;">Just now</span>
                                <p class="mt-2 h6-ragular">${data.reply.content}</p>
                            </div>
                        </div>
                    `;
                    repliesContainer.insertAdjacentElement('afterbegin', tempReply);
                }

                // Still fetch the proper HTML from server to replace the temporary reply
                await this.fetchAndAddReply(data.reply.id, parentId, mediaId);
                if (typeof showToast !== 'undefined') showToast('Reply added successfully', 'success');
                return data;
            } else {
                throw new Error(data.message || 'Failed to add reply');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast(error.message || 'Error adding reply', 'danger');
            throw error;
        }
    }
    async fetchAndAddReply(replyId, parentId, mediaId) {
        try {
            const response = await fetch(`${this.config.getReplyHtmlEndpoint}/${replyId}/${parentId}/html`, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                const html = await response.text();
                const repliesContainer = document.querySelector(`#reply-container-${parentId} .replies-container`)
                    || document.querySelector(`#review-replies-${parentId}`);
                if (repliesContainer) {
                    // Remove the temporary reply if it exists
                    const tempReply = document.querySelector(`#review-reply-${replyId}`);
                    if (tempReply) {
                        tempReply.remove();
                    }
                    // Add the proper reply HTML
                    repliesContainer.insertAdjacentHTML('afterbegin', html);
                    
                    // Make sure the replies container is visible
                    repliesContainer.style.display = 'block';
                    const parentContainer = document.querySelector(`#reply-container-${parentId}`);
                    if (parentContainer) {
                        parentContainer.style.display = 'block';
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching reply HTML:', error);
            // Even if fetch fails, the temporary reply will remain visible
        }
    }
    async deleteReply(replyId) {
        try {
            const response = await fetch(`${this.config.deleteReplyEndpoint}/${replyId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.removeReplyFromDOM(replyId);
                if (typeof showToast !== 'undefined') showToast('Reply deleted successfully', 'success');
                return data;
            } else {
                throw new Error(data.message || 'Failed to delete reply');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast(error.message || 'Error deleting reply', 'danger');
            throw error;
        }
    }
    handleReplySubmit(wrapper) {
        const textarea = wrapper.querySelector('.review-reply-textarea');
        const content = textarea.value.trim();
        const mediaId = wrapper.dataset.mediaId;
        const parentId = wrapper.dataset.parentId;
        const action = wrapper.dataset.action;
        if (!content) return;
        if (action === 'add-review') {
            this.addReview(content, mediaId);
        } else {
            this.addReply(parentId, content, mediaId);
        }
    }
    async addReview(content, mediaId) {
        try {
            const endpoint = this.config.addReviewEndpoint || '/reviews/add';
            const response = await fetch(`${endpoint}/${mediaId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content })
            });
            const data = await response.json();
            if (data.success) {
                // Clear the input
                const textarea = document.querySelector('.review-reply-input-wrapper[data-action="add-review"] .review-reply-textarea');
                if (textarea) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                }
                // Fetch and insert the new review HTML
                await this.fetchAndAddReview(data.comment.id, mediaId);
                if (typeof showToast !== 'undefined') showToast('Review added successfully', 'success');
                return data;
            } else {
                if (response.status === 409 && typeof showToast !== 'undefined') {
                    showToast(data.message || 'You have already added a review for this media.', 'warning');
                    return;
                }
                throw new Error(data.message || 'Failed to add review');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast(error.message || 'Error adding review', 'danger');
            // Do not throw error to avoid uncaught error in promise
        }
    }
    async fetchAndAddReview(commentId,mediaId) {
        try {
              const endpoint = this.config.getReviewHtmlEndpoint || '/reviews';
              const response = await fetch(`${endpoint}/${commentId}/html`, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                const html = await response.text();
                // Insert at the top of the reviews list
                // Try to find a reviews/comments list container
                const reviewsContainer = document.querySelector('.comments-list-container') || document.querySelector('.reviews-list-container') || document.querySelector('.mt-4');
                if (reviewsContainer) {
                    reviewsContainer.insertAdjacentHTML('afterbegin', html);
                }
            }
        } catch (error) {
            // Silent fail
        }
    }
    setupModalDeleteHandler() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-review-reply-confirm-btn')) {
                const replyId = e.target.getAttribute('data-reply-id');
                const modal = e.target.closest('.modal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                }
                this.deleteReply(replyId);
            }
            if (e.target.classList.contains('delete-review-comment-confirm-btn')) {
                const commentId = e.target.getAttribute('data-comment-id');
                const modal = e.target.closest('.modal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                }
                this.deleteReview(commentId);
            }
        });
    }
    async deleteReview(commentId) {
        try {
            const response = await fetch(`/reviews/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.removeReviewFromDOM(commentId);
                if (typeof showToast !== 'undefined') showToast('Review deleted successfully', 'success');
                return data;
            } else {
                throw new Error(data.message || 'Failed to delete review');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast(error.message || 'Error deleting review', 'danger');
        }
    }
    removeReviewFromDOM(commentId) {
        const reviewElement = document.querySelector(`.comment-container [data-comment-id="${commentId}"]`);
        // Go up to the .comment-container
        const container = reviewElement ? reviewElement.closest('.comment-container') : null;
        if (container) container.remove();
    }
    toggleReplyInput(button) {
        const reviewId = button.dataset.reviewId;
        const replyInputWrapper = document.getElementById(`reply-container-${reviewId}`);
        const repliesContainer = document.getElementById(`review-replies-${reviewId}`);
        const arrowDown = button.querySelector('.arrow-down-icon');
        const arrowUp = button.querySelector('.arrow-up-icon');
        const isVisible = replyInputWrapper && replyInputWrapper.style.display !== 'none';
        if (replyInputWrapper) replyInputWrapper.style.display = isVisible ? 'none' : 'block';
        if (repliesContainer) repliesContainer.style.display = isVisible ? 'none' : 'block';
        if (arrowDown) arrowDown.style.display = isVisible ? '' : 'none';
        if (arrowUp) arrowUp.style.display = isVisible ? 'none' : '';
        if (!isVisible && replyInputWrapper) {
            const textarea = replyInputWrapper.querySelector('.review-reply-textarea');
            if (textarea) textarea.focus();
        }
    }
    removeReplyFromDOM(replyId) {
        let replyElement = document.querySelector(`#review-reply-${replyId}`);
        if (replyElement) replyElement.remove();
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxReviews;
} else {
    window.AjaxReviews = AjaxReviews;
}

// Replace the old initialization block with a global instance
// document.addEventListener('DOMContentLoaded', function() {
//     const reviewRepliesContainers = document.querySelectorAll('.review-replies-container[data-ajax-review-replies]');
//     reviewRepliesContainers.forEach(container => {
//         const config = JSON.parse(container.dataset.ajaxReviewReplies || '{}');
//         container.ajaxReviewsInstance = new AjaxReviews(config);
//     });
// });
document.addEventListener('DOMContentLoaded', function() {
    window.ajaxReviewsInstance = new AjaxReviews({});
}); 