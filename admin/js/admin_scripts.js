$(document).ready(function() {
    // Generic confirmation for delete actions (excluding delete-user, which is handled in account.php)
    $('.delete-category, .delete-request').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            let action = $(this).hasClass('delete-category') ? 'delete_category' : 'delete_request';
            let id = $(this).data('category-id') || $(this).data('request-id');
            $.ajax({
                url: '../ajax/admin_actions.php',
                method: 'POST',
                data: { action: action, [action === 'delete_category' ? 'category_id' : 'request_id']: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Item deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || xhr.responseText || 'Unknown error'));
                }
            });
        }
    });

    // Approve/reject actions
    $('.approve-category, .reject-category, .approve-request, .approve-payment, .reject-payment').click(function() {
        let action = $(this).hasClass('approve-category') ? 'approve_category' :
                     $(this).hasClass('reject-category') ? 'reject_category' :
                     $(this).hasClass('approve-request') ? 'approve_request' :
                     $(this).hasClass('approve-payment') ? 'approve_payment' : 'reject_payment';
        let id = $(this).data('request-id') || $(this).data('payment-id');
        $.ajax({
            url: '../ajax/admin_actions.php',
            method: 'POST',
            data: { action: action, [action.includes('category') || action.includes('request') ? 'request_id' : 'payment_id']: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || 'Action completed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || xhr.responseText || 'Unknown error'));
            }
        });
    });
});