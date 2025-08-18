// ChangeQuestionType.js - Handles question type selection and form field management

class QuestionTypeManager {
    constructor() {
        this.typeButtons = document.querySelectorAll('.question-types .types-of');
        this.questionBlocks = document.querySelectorAll('.the-question .question-of');
        this.questionTypeField = document.getElementById('question_type');
        this.answerLists = document.querySelectorAll('.the-question .answer-list'); // Only target main form
        this.form = document.querySelector('form:not([action*="edit"])'); // Only target add form
        this.initialize();
    }

    initialize() {
        this.bindEvents();
        this.setActiveType('text'); // Set default type
        this.initializeAnswerManagement();
        this.initializeFormValidation();
    }

    bindEvents() {
        // Add click event listeners to type buttons
        this.typeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.setActiveType(btn.dataset.type));
        });
    }

    setActiveType(selectedType) {
        // Update active button state
        this.typeButtons.forEach(btn => {
            const isActive = btn.dataset.type === selectedType;
            btn.classList.toggle('active', isActive);
        });

        // Update the hidden question_type field
        if (this.questionTypeField) {
            this.questionTypeField.value = selectedType;
        }

        // Show/hide question blocks based on type
        this.questionBlocks.forEach(block => {
            const isMatch = block.dataset.type === selectedType;
            block.classList.toggle('d-none', !isMatch);

            // Enable/disable inputs based on active type
            const inputs = block.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.removeAttribute('disabled');
            });
        });
    }

    // Method to get current active type
    getCurrentType() {
        return this.questionTypeField ? this.questionTypeField.value : 'text';
    }

    // Method to set type programmatically (useful for form restoration)
    setType(type) {
        this.setActiveType(type);
    }

    // Initialize answer management for multiple/single choice questions
    initializeAnswerManagement() {
        this.answerLists.forEach(list => {
            list.addEventListener('click', (e) => this.handleAnswerAction(e));
        });
    }

    // Handle adding/removing answers
    handleAnswerAction(e) {
        const addBtn = e.target.closest('.add-answer');
        const deleteBtn = e.target.closest('.delete-answer');

        if (addBtn) {
            this.addAnswer(addBtn);
        }

        if (deleteBtn) {
            this.deleteAnswer(deleteBtn);
        }
    }

    // Add new answer row
    addAnswer(addBtn) {
        const row = addBtn.closest('.answer-row');
        const answerList = row.parentElement;
        const clone = row.cloneNode(true);
        
        // Clear input value
        clone.querySelector('input').value = '';
        
        // Show Add button in new row, hide Delete
        clone.querySelector('.add-answer').style.display = 'inline-block';
        clone.querySelector('.delete-answer').style.display = 'none';
        
        // Hide Add button in current row, show Delete
        row.querySelector('.add-answer').style.display = 'none';
        row.querySelector('.delete-answer').style.display = 'inline-block';
        
        answerList.appendChild(clone);
    }

    // Delete answer row
    deleteAnswer(deleteBtn) {
        const row = deleteBtn.closest('.answer-row');
        const answerList = row.parentElement;
        
        if (answerList.children.length > 1) {
            row.remove();
            
            // Ensure the last row always has Add button visible
            const lastRow = answerList.lastElementChild;
            lastRow.querySelector('.add-answer').style.display = 'inline-block';
            lastRow.querySelector('.delete-answer').style.display = 'none';
        }
    }

    // Initialize form validation
    initializeFormValidation() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.validateForm(e));
        }
    }

    // Validate form before submission
    validateForm(e) {
        const questionGroupId = document.getElementById('form_question_group_id').value;
        const questionType = this.getCurrentType();
        let questionValue = '';
        
        // Get question value based on active type
        if (questionType === 'text') {
            questionValue = document.getElementById('text_question').value.trim();
        } else if (questionType === 'multiple_choice') {
            questionValue = document.getElementById('text_question_multiple').value.trim();
        } else if (questionType === 'single_choice') {
            questionValue = document.getElementById('text_question_single').value.trim();
        }

        if (!questionGroupId) {
            e.preventDefault();
            alert('Please select a question group.');
            return false;
        }

        if (!questionValue) {
            e.preventDefault();
            alert('Please enter a question.');
            return false;
        }

        // For multiple/single choice, validate that at least one answer is provided
        if ((questionType === 'multiple_choice' || questionType === 'single_choice') && questionType !== 'text') {
            const answers = document.querySelectorAll('input[name="answers[]"]');
            let hasAnswer = false;
            answers.forEach(answer => {
                if (answer.value.trim()) {
                    hasAnswer = true;
                }
            });
            
            if (!hasAnswer) {
                e.preventDefault();
                alert('Please provide at least one answer option.');
                return false;
            }
        }
    }

    // Reset form fields
    resetForm() {
        // Reset question inputs
        document.getElementById('text_question').value = '';
        document.getElementById('text_question_multiple').value = '';
        document.getElementById('text_question_single').value = '';
        
        // Clear answer fields in main form only
        const mainForm = document.querySelector('.the-question');
        const answerInputs = mainForm.querySelectorAll('input[name="answers[]"]');
        answerInputs.forEach((input, index) => {
            if (index > 0) {
                input.closest('.answer-row').remove();
            } else {
                input.value = '';
            }
        });
        
        // Reset to default question type but don't clear sessionStorage
        this.setActiveType('text');
    }
    
    // Reset form completely including sessionStorage
    clearForm() {
        this.resetForm();
        sessionStorage.removeItem('selectedQuestionType');
    }

    // Clear form after successful submission
    clearFormAfterSuccess() {
        this.clearForm();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the reviewers questions page
    if (document.querySelector('.question-types')) {
        window.questionTypeManager = new QuestionTypeManager();
        // Restore the previously selected type
        window.questionTypeManager.restoreType();
    }
});

// Export for use in other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuestionTypeManager;
}
