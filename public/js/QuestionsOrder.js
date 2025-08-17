new Sortable(document.getElementById('sortable'), {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function (evt) {
      console.log("Moved item from " + evt.oldIndex + " to " + evt.newIndex);

      // Get all question IDs in their new order
      const questionIds = Array.from(document.getElementById('sortable').children).map(child => {
        return child.getAttribute('data-question-id');
      });

      // Send the new order to the backend
        console.log('Sending question IDs:', questionIds);
        fetch('/questions/switch-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                question_ids: questionIds
            })
        })
      .then(response => {
          console.log('Response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Order updated successfully:', data);
          if (data.success) {
            showToast('Questions order updated successfully', 'success');
          } else {
            showToast(data.message || 'Failed to update questions order', 'danger');
          }
        })
      .catch(error => {
            console.error('Error updating order:', error);
            let errorMessage = 'Failed to update questions order. Please try again.';
            
            if (error.response) {
                // Server responded with error status
                error.response.json().then(data => {
                    errorMessage = data.message || errorMessage;
                    showToast(errorMessage, 'danger');
                }).catch(() => {
                    showToast(errorMessage, 'danger');
                });
            } else if (error instanceof TypeError) {
                // Network error or CORS issue
                showToast('Network error. Please check your connection.', 'danger');
            } else {
                // Other errors
                showToast(errorMessage, 'danger');
            }
        });
    }
  });