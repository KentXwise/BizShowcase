<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Fetch subscribed and unsubscribed users
$subscribed_users = get_subscribed_users($conn);
$unsubscribed_users = get_unsubscribed_users($conn);

// Ensure status and suspension_end_date are available for each user
foreach ($subscribed_users as &$user) {
    if (!isset($user['status']) || !isset($user['suspension_end_date'])) {
        $stmt = $conn->prepare("SELECT status, suspension_end_date FROM users WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['status'] = $user_data['status'] ?? 'active';
        $user['suspension_end_date'] = $user_data['suspension_end_date'] ?? null;
    }
}
unset($user);

foreach ($unsubscribed_users as &$user) {
    if (!isset($user['status']) || !isset($user['suspension_end_date'])) {
        $stmt = $conn->prepare("SELECT status, suspension_end_date FROM users WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['status'] = $user_data['status'] ?? 'active';
        $user['suspension_end_date'] = $user_data['suspension_end_date'] ?? null;
    }
}
unset($user);

// Function to check if a user is currently suspended
function is_user_suspended($user) {
    $status = $user['status'] ?? 'active';
    $suspension_end_date = $user['suspension_end_date'] ?? null;

    // A user is considered suspended if status is 'suspended' AND suspension_end_date is in the future
    $is_suspended = $status === 'suspended' && !empty($suspension_end_date) && strtotime($suspension_end_date) > time();
    return $is_suspended;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accounts - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin_styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">BizShowcase Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="accounts.php">Accounts</a></li>
                    <li class="nav-item"><a class="nav-link" href="category.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaction.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="request.php">Requests</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 admin-container">
        <h2>Manage Accounts</h2>
        <h3>Subscribed Users</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subscription Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribed_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['subscription_type']); ?></td>
                        <td>
                            <?php if (is_user_suspended($user)): ?>
                                <button class="btn btn-success btn-sm unsuspend-user" data-user-id="<?php echo $user['user_id']; ?>">Unsuspend</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#suspendModal">Suspend</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user['user_id']; ?>">Delete</button>
                            <button class="btn btn-info btn-sm update-user" data-user-id="<?php echo $user['user_id']; ?>">Update</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Unsubscribed Users</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unsubscribed_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if (is_user_suspended($user)): ?>
                                <button class="btn btn-success btn-sm unsuspend-user" data-user-id="<?php echo $user['user_id']; ?>">Unsuspend</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#suspendModal">Suspend</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user['user_id']; ?>">Delete</button>
                            <button class="btn btn-info btn-sm update-user" data-user-id="<?php echo $user['user_id']; ?>">Update</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>How many days would you like to suspend this user?</p>
                    <input type="number" class="form-control" id="suspendDays" min="1" placeholder="Enter number of days" required>
                    <input type="hidden" id="suspendUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmSuspend">Suspend</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Populate user ID in the modal when the suspend button is clicked
            $('.suspend-user').on('click', function() {
                const userId = $(this).data('user-id');
                $('#suspendUserId').val(userId);
                $('#suspendDays').val(''); // Clear the input field
            });

            // Reset the modal when it is closed
            $('#suspendModal').on('hidden.bs.modal', function () {
                $('#suspendDays').val('');
                $('#suspendUserId').val('');
            });

            // Handle suspension confirmation
            $('#confirmSuspend').on('click', function() {
                const userId = $('#suspendUserId').val();
                const days = parseInt($('#suspendDays').val());

                if (!userId) {
                    alert('No user selected. Please try again.');
                    return;
                }

                if (isNaN(days) || days <= 0) {
                    alert('Please enter a valid number of days (greater than 0).');
                    return;
                }

                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'suspend_user', user_id: userId, days: days },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message || 'User suspended successfully!');
                            $('#suspendModal').modal('hide');
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

            // Handle unsuspend action
            $('.unsuspend-user').on('click', function() {
                const userId = $(this).data('user-id');

                if (!userId) {
                    alert('No user selected. Please try again.');
                    return;
                }

                if (!confirm('Are you sure you want to unsuspend this user?')) {
                    return;
                }

                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'unsuspend_user', user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message || 'User unsuspended successfully!');
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
    </script>
</body>
</html>