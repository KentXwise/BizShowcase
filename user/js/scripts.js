$(document).ready(function() {
    // Poll for notifications every 30 seconds
    function checkNotifications() {
        $.ajax({
            url: '../ajax/user_actions.php',
            method: 'POST',
            data: { action: 'check_notifications' },
            success: function(response) {
                if (response.notifications && response.notifications.length > 0) {
                    response.notifications.forEach(function(notification) {
                        alert(notification.message); // Replace with a better notification UI
                    });
                }
            }
        });
    }
    setInterval(checkNotifications, 30000);
});