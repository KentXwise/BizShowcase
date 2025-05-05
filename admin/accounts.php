<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$subscribed_users = get_subscribed_users($conn);
$unsubscribed_users = get_unsubscribed_users($conn);
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
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="subscribed-users-table">
                <?php foreach ($subscribed_users as $user): ?>
                    <tr data-user-id="<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['subscription_type']); ?></td>
                        <td>
                            <?php
                            $suspendedUntil = $user['suspended_until'] ? new DateTime($user['suspended_until']) : null;
                            $now = new DateTime();
                            if ($suspendedUntil && $suspendedUntil > $now) {
                                echo 'Suspended until ' . $suspendedUntil->format('Y-m-d H:i:s');
                            } else {
                                echo 'Active';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>">Suspend</button>
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
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="unsubscribed-users-table">
                <?php foreach ($unsubscribed_users as $user): ?>
                    <tr data-user-id="<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            $suspendedUntil = $user['suspended_until'] ? new DateTime($user['suspended_until']) : null;
                            $now = new DateTime();
                            if ($suspendedUntil && $suspendedUntil > $now) {
                                echo 'Suspended until ' . $suspendedUntil->format('Y-m-d H:i:s');
                            } else {
                                echo 'Active';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>">Suspend</button>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user['user_id']; ?>">Delete</button>
                            <button class="btn btn-info btn-sm update-user" data-user-id="<?php echo $user['user_id']; ?>">Update</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="suspendModalLabel">Suspend User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="suspendDays" class="form-label">Number of days to suspend (0 to unsuspend):</label>
                        <input type="number" class="form-control" id="suspendDays" min="0" value="0">
                    </div>
                    <input type="hidden" id="suspendUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmSuspend">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Delete user
            $(document).on('click', '.delete-user', function() {
                if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    let userId = $(this).data('user-id');
                    $.ajax({
                        url: '../ajax/admin_actions.php',
                        method: 'POST',
                        data: { action: 'delete_user', user_id: userId },
                        success: function(response) {
                            if (response.success) {
                                alert('User deleted successfully!');
                                $(`tr[data-user-id="${userId}"]`).remove();
                            } else {
                                alert('Error deleting user: ' + (response.error || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('An error occurred while deleting the user.');
                        }
                    });
                }
            });

            // Suspend user - Open modal
            $(document).on('click', '.suspend-user', function() {
                let userId = $(this).data('user-id');
                $('#suspendUserId').val(userId);
                $('#suspendModal').modal('show');
            });

            // Confirm suspend
            $('#confirmSuspend').on('click', function() {
                let userId = $('#suspendUserId').val();
                let days = $('#suspendDays').val();
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'suspend_user', user_id: userId, days: days },
                    success: function(response) {
                        if (response.success) {
                            alert('User suspension updated successfully!');
                            $('#suspendModal').modal('hide');
                            window.location.reload(); // Reload to update status
                        } else {
                            alert('Error suspending user: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('An error occurred while suspending the user.');
                    }
                });
            });
        });
    </script>
</body>
</html>