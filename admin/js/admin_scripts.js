$(document).ready(function() {
    // Generic confirmation for delete actions
    $('.delete-user, .delete-category, .delete-request').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            let action = $(this).hasClass('delete-user') ? 'delete_user' :
                         $(this).hasClass('delete-category') ? 'delete_category' : 'delete_request';
            let id = $(this).data('user-id') || $(this).data('category-id') || $(this).data('request-id');
            $.ajax({
                url: '../ajax/admin_actions.php',
                method: 'POST',
                data: { action: action, [action === 'delete_user' ? 'user_id' : action === 'delete_category' ? 'category_id' : 'request_id']: id },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('An error occurred. Please try again.');
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
            success: function() {
                location.reload();
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Suspend user
    $('.suspend-user').click(function() {
        if (confirm('Are you sure you want to suspend this user?')) {
            let userId = $(this).data('user-id');
            $.ajax({
                url: '../ajax/admin_actions.php',
                method: 'POST',
                data: { action: 'suspend_user', user_id: userId },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
});