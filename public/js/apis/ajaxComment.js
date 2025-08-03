// AJAX Comment System - Reusable across content types
class AjaxComments {
    constructor(config = {}) {
        this.config = {
            // Default endpoints - can be overridden
            addCommentEndpoint: '/comments/add',
            addReplyEndpoint: '/comments/reply',
            deleteCommentEndpoint: '/comments',
            likeCommentEndpoint: '/comments',
            unlikeCommentEndpoint: '/comments',
            getLikesCountEndpoint: '/comments',
            getCommentHtmlEndpoint: '/comments', // Added for fetching HTML
            
            // Content type specific overrides
            ...config
        };
        
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                        document.querySelector('input[name="_token"]')?.value;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeReadMore();
        this.setupModalDeleteHandler();
        // Initialize existing comments and replies
        this.initializeExistingContent();
    }
    
    initializeExistingContent() {
        // Initialize all existing comments
        document.querySelectorAll('.comment-container').forEach(comment => {
            this.bindEventsForNewComment(comment.id.replace('comment-', ''));
        });
        // Initialize all existing replies
        document.querySelectorAll('[id^="reply-"]').forEach(reply => {
            this.bindEventsForNewReply(reply.id.replace('reply-', ''));
        });
    }
    
    bindEvents() {
        // Use event delegation for all dynamic elements
        document.addEventListener('click', async (e) => {
            // Handle comment submission
            if (e.target.closest('.comment-submit-btn')) {
                e.preventDefault();
                const wrapper = e.target.closest('.comment-input-wrapper');
                await this.handleCommentSubmit(wrapper);
                // Force update UI after first submission
                this.updateCommentsList();
            }
            
            // Handle reply button clicks
            if (e.target.closest('.reply-btn')) {
                e.preventDefault();
                this.toggleReplyInput(e.target.closest('.reply-btn'));
            }
            
            // Handle like button clicks
            if (e.target.closest('[data-action="like-comment"]')) {
                e.preventDefault();
                this.handleLikeComment(e.target.closest('[data-action="like-comment"]'));
            }
            
            // Handle delete button clicks
            if (e.target.closest('[data-action="delete-comment"]')) {
                e.preventDefault();
                this.handleDeleteComment(e.target.closest('[data-action="delete-comment"]'));
            }
        });
        
        // Remove Enter key handler for comment submission
        // document.addEventListener('keydown', (e) => {
        //     if (e.key === 'Enter' && !e.shiftKey && e.target.classList.contains('comment-textarea')) {
        //         e.preventDefault();
        //         const wrapper = e.target.closest('.comment-input-wrapper');
        //         if (wrapper && e.target.value.trim().length > 0) {
        //             this.handleCommentSubmit(wrapper);
        //         }
        //     }
        // });
        
        // Initialize auto-resize for existing comment inputs
        this.initializeCommentInputs();
    }
    
    // Initialize auto-resize functionality for comment inputs
    initializeCommentInputs() {
        document.querySelectorAll('.comment-textarea').forEach(textarea => {
            const submitBtn = textarea?.closest('.comment-input-wrapper')?.querySelector('.comment-submit-btn');
            
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

                // Handle Enter key
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        // Create a new line instead of submitting
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        const value = this.value;
                        this.value = value.substring(0, start) + '\n' + value.substring(end);
                        this.selectionStart = this.selectionEnd = start + 1;
                        autoResize();
                    }
                });

                // Auto-resize on input
                textarea.addEventListener('input', autoResize);
                
                // Initial resize
                autoResize();
            }
        });
    }
    
    // Add new comment
    async addComment(content, mediaId) {
        try {          
            const response = await fetch(`${this.config.addCommentEndpoint}/${mediaId}`, {
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
                const textarea = document.querySelector(`[data-media-id="${mediaId}"] .comment-textarea`);
                if (textarea) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    this.updateSubmitButton(textarea);
                }
                
                // Fetch the rendered comment HTML from server
                await this.fetchAndAddComment(data.comment.id, mediaId);
                
                // Show success message
                if (typeof showToast !== 'undefined') {
                    showToast('Comment added successfully', 'success');
                }
                
                return data;
            } else {
                throw new Error(data.message || 'Failed to add comment');
            }
        } catch (error) {
            console.error('Error adding comment:', error);
            if (typeof showToast !== 'undefined') {
                showToast(error.message || 'Error adding comment', 'danger');
            }
            throw error;
        }
    }
    
    // Add reply to comment
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
                // Clear the reply input
                const textarea = document.querySelector(`#reply-comment-${parentId}`);
                if (textarea) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    this.updateSubmitButton(textarea);
                }
                
                // Hide reply input
                const replyContainer = document.querySelector(`#reply-container-${parentId}`);
                if (replyContainer) {
                    replyContainer.style.display = 'none';
                }
                
                // Fetch the rendered reply HTML from server
                await this.fetchAndAddReply(data.reply.id, parentId, mediaId);
                
                // Update reply count
                this.updateReplyCount(parentId);
                
                // Show success message
                if (typeof showToast !== 'undefined') {
                    showToast('Reply added successfully', 'success');
                }
                
                return data;
            } else {
                throw new Error(data.message || 'Failed to add reply');
            }
        } catch (error) {
            console.error('Error adding reply:', error);
            if (typeof showToast !== 'undefined') {
                showToast(error.message || 'Error adding reply', 'danger');
            }
            throw error;
        }
    }
    
    // Fetch rendered comment HTML from server
    async fetchAndAddComment(commentId, mediaId) {
        try {
            const htmlEndpoint = this.config.getCommentHtmlEndpoint || '/comments';
            const response = await fetch(`${htmlEndpoint}/${commentId}/html`, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const commentsContainer = document.querySelector(`[data-media-id="${mediaId}"] .comments-list-container`);

                if (commentsContainer) {
                    // Remove 'No comments yet.' message if present
                    const noCommentsMsg = commentsContainer.querySelector('.comment-container p.h6-ragular');
                    if (noCommentsMsg && noCommentsMsg.textContent.trim() === 'No comments yet.') {
                        noCommentsMsg.closest('.comment-container').remove();
                    }
                    // Insert at the beginning (newest first)
                    commentsContainer.insertAdjacentHTML('afterbegin', html);
                    
                    // Reinitialize read more functionality
                    this.initializeReadMore();
                    
                    // Initialize comment input functionality for the new comment
                    this.bindEventsForNewComment(commentId);
                }
            }
        } catch (error) {
            console.error('Error fetching comment HTML:', error);
        }
    }
    
    // Fetch rendered reply HTML from server
    async fetchAndAddReply(replyId, parentId, mediaId) {
        try {
          const response = await fetch(`/comments/reply/${replyId}/${parentId}/html`, {
            headers: {
              'Accept': 'text/html',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          if (response.ok) {
            const html = await response.text();
            // Insert reply into the correct parent comment's replies-container
            const repliesContainer = document.querySelector(`#comment-${parentId} .replies-container`);

            if (repliesContainer) {
              // Insert at the beginning (newest first)
              repliesContainer.insertAdjacentHTML('afterbegin', html);
              
              // Reinitialize read more functionality
              this.initializeReadMore();
              
              // Initialize comment input functionality for the new comment
              this.bindEventsForNewComment(replyId);
          }
          }
      } catch (error) {
        console.error('Error fetching reply HTML:', error);
      }
    }
    
    // Update comments list
    async updateCommentsList() {
        const container = document.querySelector('.comments-list-container');
        if (!container) return;
        
        try {
            const mediaId = container.closest('.comments-container').dataset.mediaId;
            const response = await fetch(`${this.config.getCommentHtmlEndpoint}?mediaId=${mediaId}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const html = await response.text();
            if (html.trim()) {
                container.innerHTML = html;
                // Reinitialize all comments and replies in the updated list
                this.initializeExistingContent();
            }
        } catch (error) {
            console.error('Error updating comments list:', error);
        }
    }

    // Like/Unlike comment
    async toggleLikeComment(commentId, isLiked) {
        try {
            const method = isLiked ? 'DELETE' : 'POST';
            const endpoint = isLiked ? 
                `${this.config.unlikeCommentEndpoint}/${commentId}/like` :
                `${this.config.likeCommentEndpoint}/${commentId}/like`;
            
            const response = await fetch(endpoint, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update like button UI
                this.updateLikeButton(commentId, data.liked, data.likesCount);
                return data;
            } else {
                throw new Error(data.message || 'Failed to toggle like');
            }
        } catch (error) {
            console.error('Error toggling like:', error);
            if (typeof showToast !== 'undefined') {
                showToast(error.message || 'Error processing like', 'danger');
            }
            throw error;
        }
    }
    
    // Delete comment
    async deleteComment(commentId) {
        try {
            const response = await fetch(`${this.config.deleteCommentEndpoint}/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove comment from DOM
                this.removeCommentFromDOM(commentId);
                
                // Show success message
                if (typeof showToast !== 'undefined') {
                    showToast('Comment deleted successfully', 'success');
                }
                
                return data;
            } else {
                throw new Error(data.message || 'Failed to delete comment');
            }
        } catch (error) {
            console.error('Error deleting comment:', error);
            if (typeof showToast !== 'undefined') {
                showToast(error.message || 'Error deleting comment', 'danger');
            }
            throw error;
        }
    }
    
    // Event handlers
    handleCommentSubmit(wrapper) {
        const textarea = wrapper.querySelector('.comment-textarea');
        const content = textarea.value.trim();
        const action = wrapper.dataset.action;
        const mediaId = wrapper.closest('.comments-container').dataset.mediaId;
        
        if (!content) return;
        
        if (action === 'add-comment') {
            this.addComment(content, mediaId);
        } else if (action === 'add-reply') {
            const parentId = wrapper.dataset.parentId;
            this.addReply(parentId, content, mediaId);
        }
    }
    
    handleLikeComment(button) {
        const commentId = button.dataset.commentId;
        const isLiked = button.dataset.liked === 'true';
        this.toggleLikeComment(commentId, isLiked);
    }
    
    handleDeleteComment(button) {
        // No longer used: modal handles confirmation
    }

    // Listen for modal delete confirmation
    setupModalDeleteHandler() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-comment-confirm-btn')) {
                const commentId = e.target.getAttribute('data-comment-id');
                // Hide the modal
                const modal = e.target.closest('.modal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                    // Move focus to the button that opened the modal
                    const triggerBtn = document.querySelector(`[data-bs-target="#${modal.id}"]`);
                    if (triggerBtn) triggerBtn.focus();
                }
                this.deleteComment(commentId);
            }
        });
    }
    
    toggleReplyInput(button) {
        const commentId = button.dataset.commentId;
        const replyContainer = document.querySelector(`#reply-container-${commentId}`);
        
        if (replyContainer) {
            const isVisible = replyContainer.style.display !== 'none';
            replyContainer.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                const textarea = replyContainer.querySelector('.comment-textarea');
                if (textarea) {
                    textarea.focus();
                }
            }
        }
    }
    
    // DOM manipulation methods
    removeCommentFromDOM(commentId) {
        let commentElement = document.querySelector(`#comment-${commentId}`);
        if (!commentElement) {
            commentElement = document.querySelector(`#reply-${commentId}`);
            if (commentElement) {
                // Try to find parent id from data-parent-id attribute or DOM
                let parentId = null;
                if (commentElement.dataset.parentId) {
                    parentId = commentElement.dataset.parentId;
                } else {
                    const repliesContainer = commentElement.closest('.replies-container');
                    if (repliesContainer) {
                        const parentComment = repliesContainer.closest('.comment-container');
                        if (parentComment && parentComment.id.startsWith('comment-')) {
                            parentId = parentComment.id.replace('comment-', '');
                        }
                    }
                }
                commentElement.remove();
                if (parentId) {
                    this.updateReplyCount(parentId);
                }
                return;
            }
        }
        if (commentElement) {
            const commentsListContainer = commentElement.closest('.comments-list-container');
            commentElement.remove();
            // If no more comments, show 'No comments yet.'
            if (commentsListContainer && commentsListContainer.querySelectorAll('.comment-container[id^="comment-"]').length === 0) {
                const noCommentsDiv = document.createElement('div');
                noCommentsDiv.className = 'comment-container';
                noCommentsDiv.innerHTML = '<p class="h6-ragular">No comments yet.</p>';
                commentsListContainer.appendChild(noCommentsDiv);
            }
        }
    }
    
    updateLikeButton(commentId, isLiked, likesCount) {
        const likeButton = document.querySelector(`[data-comment-id="${commentId}"][data-action="like-comment"]`);
        if (!likeButton) return;

        // Update button state
        likeButton.dataset.liked = isLiked.toString();

        // Toggle icons
        const fillIcon = likeButton.querySelector('.like-fill');
        const emptyIcon = likeButton.querySelector('.like-empty');
        if (fillIcon && emptyIcon) {
            fillIcon.style.display = isLiked ? '' : 'none';
            emptyIcon.style.display = isLiked ? 'none' : '';
        }

        // Update count
        const countElement = likeButton.parentNode.querySelector('.likes-count');
        if (countElement) {
            countElement.textContent = `${likesCount} Likes`;
        }
    }
    
    updateReplyCount(parentId) {
        const commentElement = document.querySelector(`#comment-${parentId}`);
        if (!commentElement) return;
        
        const repliesContainer = commentElement.querySelector('.replies-container');
        const countElement = commentElement.querySelector('.replies-count');
        
        if (countElement) {
            let replyCount = 0;
            if (repliesContainer) {
                replyCount = Array.from(repliesContainer.children).filter(
                    el => el.classList.contains('comment-container') && el.id.startsWith('reply-')
                ).length;
            }
            countElement.textContent = `${replyCount} Replies`;
        }
    }
    
    updateSubmitButton(textarea) {
        const wrapper = textarea.closest('.comment-input-wrapper');
        const submitBtn = wrapper.querySelector('.comment-submit-btn');
        
        if (submitBtn) {
            const hasContent = textarea.value.trim().length > 0;
            submitBtn.style.opacity = hasContent ? '1' : '0.5';
            submitBtn.style.pointerEvents = hasContent ? 'auto' : 'none';
        }
    }
    
    // Bind events for newly added comment
    bindEventsForNewComment(commentId) {
        // Initialize comment input functionality for the new comment
        const commentInput = document.querySelector(`#comment-${commentId} .comment-input-wrapper`);
        if (commentInput) {
            const textarea = commentInput.querySelector('.comment-textarea');
            const submitBtn = commentInput.querySelector('.comment-submit-btn');
            
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

                // Handle Enter key
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        // Create a new line instead of submitting
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        const value = this.value;
                        this.value = value.substring(0, start) + '\n' + value.substring(end);
                        this.selectionStart = this.selectionEnd = start + 1;
                        autoResize();
                    }
                });

                // Auto-resize on input
                textarea.addEventListener('input', autoResize);
                
                // Initial resize
                autoResize();
            }
        }
    }
    
    // Bind events for newly added reply
    bindEventsForNewReply(replyId) {
        // No need to bind events manually since we're using event delegation
        // The main bindEvents method will handle all dynamically added elements
    }
    
    // Utility methods
    initializeReadMore() {
        const maxHeight = 60;
        
        document.querySelectorAll('.comment-text').forEach(function(textElement) {
            const contentWrapper = textElement.closest('.comment-content-wrapper');
            const readMoreBtn = contentWrapper.querySelector('.read-more-btn');
            const readLessBtn = contentWrapper.querySelector('.read-less-btn');
            
            // Remove existing event listeners to prevent duplicates
            if (readMoreBtn) {
                readMoreBtn.replaceWith(readMoreBtn.cloneNode(true));
            }
            if (readLessBtn) {
                readLessBtn.replaceWith(readLessBtn.cloneNode(true));
            }
            
            // Get fresh references after cloning
            const newReadMoreBtn = contentWrapper.querySelector('.read-more-btn');
            const newReadLessBtn = contentWrapper.querySelector('.read-less-btn');
            
            if (textElement.scrollHeight > maxHeight) {
                textElement.style.maxHeight = maxHeight + 'px';
                textElement.style.overflow = 'hidden';
                textElement.classList.add('collapsed');
                if (newReadMoreBtn) newReadMoreBtn.style.display = 'inline-block';
                
                if (newReadMoreBtn) {
                    newReadMoreBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        textElement.style.maxHeight = 'none';
                        textElement.style.overflow = 'visible';
                        textElement.classList.remove('collapsed');
                        newReadMoreBtn.style.display = 'none';
                        if (newReadLessBtn) newReadLessBtn.style.display = 'inline-block';
                    });
                }
                
                if (newReadLessBtn) {
                    newReadLessBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        textElement.style.maxHeight = maxHeight + 'px';
                        textElement.style.overflow = 'hidden';
                        textElement.classList.add('collapsed');
                        newReadLessBtn.style.display = 'none';
                        if (newReadMoreBtn) newReadMoreBtn.style.display = 'inline-block';
                    });
                }
            } else {
                if (newReadMoreBtn) newReadMoreBtn.style.display = 'none';
                if (newReadLessBtn) newReadLessBtn.style.display = 'none';
            }
        });
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxComments;
} else {
    window.AjaxComments = AjaxComments;
}

// Auto-initialize if data attribute is present
document.addEventListener('DOMContentLoaded', function() {
    const commentsContainers = document.querySelectorAll('.comments-container[data-ajax-comments]');
    
    commentsContainers.forEach(container => {
        const config = JSON.parse(container.dataset.ajaxComments || '{}');
        container.ajaxCommentsInstance = new AjaxComments(config);
    });
});