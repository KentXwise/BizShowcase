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

// Fetch deleted users
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email FROM users WHERE status = 'deleted'");
$stmt->execute();
$deleted_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure status and suspension_end_date are available for each user (for subscribed and unsubscribed users)
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
    <link href="css/accounts.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2196f3;">
  <div class="container-fluid justify-content-between">
    
    <!-- Hamburger menu for small screens -->
    <button class="btn btn-outline-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
      ☰
    </button>

    <!-- Branding -->
    <div class="d-flex align-items-center">
      <img src="img/logo.png" alt="Profile" width="55px" class="rounded-circle me-2">
      <span class="navbar-brand fw-bold">BIZShowcase</span>
    </div>

    <img src="img/gigago.png" alt="Profile" width="50px" class="rounded-circle">
  </div>
</nav>


<div class="container-fluid">
  <div class="row">
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
    <div class="col-lg-10 content-area" style="overflow-x: auto;">
      <h1 class="fw-bold mb-4">Manage Accounts</h1>
      

    <div class="card-custom mb-5">
        <h3>Subscribed Users</h3>
        <table class="table" id="subscribed-users-table">
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
                    <tr data-user-id="<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['subscription_type']); ?></td>
                        <td>
                            <?php if (is_user_suspended($user)): ?>
                                <button class="btn btn-success btn-sm unsuspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#unsuspendConfirmModal">Unsuspend</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#suspendModal">Suspend</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                            <button class="btn btn-info btn-sm view-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewUserModal">View</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-custom mb-5">

        <h3>Unsubscribed Users</h3>
        <table class="table" id="unsubscribed-users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unsubscribed_users as $user): ?>
                    <tr data-user-id="<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if (is_user_suspended($user)): ?>
                                <button class="btn btn-success btn-sm unsuspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#unsuspendConfirmModal">Unsuspend</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm suspend-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#suspendModal">Suspend</button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                            <button class="btn btn-info btn-sm view-user" data-user-id="<?php echo $user['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewUserModal">View</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-custom mb-5">

        <h3>Deleted Accounts</h3>
        <table class="table" id="deleted-users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deleted_users as $user): ?>
                    <tr data-user-id="<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Suspend Modal (for entering days) -->
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
                    <button type="button" class="btn btn-warning" id="proceedSuspend">Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Confirmation Modal -->
    <div class="modal fade" id="suspendConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Suspension</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to suspend this user for <span id="suspendDaysDisplay"></span> days? This action cannot be undone.</p>
                    <input type="hidden" id="suspendConfirmUserId">
                    <input type="hidden" id="suspendConfirmDays">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmSuspend">Suspend</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Success Modal -->
    <div class="modal fade" id="suspendSuccessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>User suspended successfully!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Error Modal -->
    <div class="modal fade" id="suspendErrorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please enter a valid number of days (greater than 0).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#suspendModal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unsuspend Confirmation Modal -->
    <div class="modal fade" id="unsuspendConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Unsuspend</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to unsuspend this user?</p>
                    <input type="hidden" id="unsuspendUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmUnsuspend">Unsuspend</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unsuspend Success Modal -->
    <div class="modal fade" id="unsuspendSuccessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>User unsuspended successfully!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Success Modal -->
    <div class="modal fade" id="deleteSuccessModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>User deleted successfully!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title">User Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title border-bottom pb-2 mb-3">Account Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">First Name</label>
                                    <span id="viewFirstName" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Last Name</label>
                                    <span id="viewLastName" class="form-control"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Email</label>
                                    <span id="viewEmail" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Contact Number</label>
                                    <span id="viewContactNumber" class="form-control"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Address</label>
                                    <span id="viewAddress" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Birthday</label>
                                    <span id="viewBirthday" class="form-control"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title border-bottom pb-2 mb-3">Business Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Company Name</label>
                                    <span id="viewCompanyName" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Postal Code</label>
                                    <span id="viewPostalCode" class="form-control"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Business Email</label>
                                    <span id="viewBusinessEmail" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Business Number</label>
                                    <span id="viewBusinessNumber" class="form-control"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Business Address</label>
                                    <span id="viewBusinessAddress" class="form-control"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Seller Type</label>
                                    <span id="viewSellerType" class="form-control"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Suspend Modal: Populate user ID and days
            $('.suspend-user').on('click', function() {
                const userId = $(this).data('user-id');
                $('#suspendUserId').val(userId);
                $('#suspendDays').val(''); // Clear the input field
            });

            // Suspend Modal: Reset when closed
            $('#suspendModal').on('hidden.bs.modal', function () {
                $('#suspendDays').val('');
                $('#suspendUserId').val('');
            });

            // Suspend Modal: Proceed to confirmation
            $('#proceedSuspend').on('click', function() {
                const userId = $('#suspendUserId').val();
                const days = parseInt($('#suspendDays').val());

                if (!userId) {
                    alert('No user selected. Please try again.');
                    return;
                }

                if (isNaN(days) || days <= 0) {
                    $('#suspendErrorModal').modal('show');
                    return;
                }

                // Populate the confirmation modal
                $('#suspendConfirmUserId').val(userId);
                $('#suspendConfirmDays').val(days);
                $('#suspendDaysDisplay').text(days);

                // Hide the suspend modal and show the confirmation modal
                $('#suspendModal').modal('hide');
                $('#suspendConfirmModal').modal('show');
            });

            // Suspend Confirmation Modal: Handle confirmation
            $('#confirmSuspend').on('click', function() {
                const userId = $('#suspendConfirmUserId').val();
                const days = parseInt($('#suspendConfirmDays').val());

                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'suspend_user', user_id: userId, days: days },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#suspendConfirmModal').modal('hide');
                            $('#suspendSuccessModal').modal('show');
                            // Reload the page after the success modal is closed
                            $('#suspendSuccessModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            alert('Error: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.error || xhr.responseText || 'Unknown error'));
                    }
                });
            });

            // Unsuspend Modal: Populate user ID
            $('.unsuspend-user').on('click', function() {
                const userId = $(this).data('user-id');
                $('#unsuspendUserId').val(userId);
            });

            // Unsuspend Modal: Reset when closed
            $('#unsuspendConfirmModal').on('hidden.bs.modal', function () {
                $('#unsuspendUserId').val('');
            });

            // Unsuspend Confirmation: Handle unsuspend action
            $('#confirmUnsuspend').on('click', function() {
                const userId = $('#unsuspendUserId').val();

                if (!userId) {
                    alert('No user selected. Please try again.');
                    return;
                }

                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'unsuspend_user', user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#unsuspendConfirmModal').modal('hide');
                            $('#unsuspendSuccessModal').modal('show');
                            // Reload the page after the success modal is closed
                            $('#unsuspendSuccessModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            alert('Error: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.error || xhr.responseText || 'Unknown error'));
                    }
                });
            });

            // Delete Modal: Populate user ID
            $('.delete-user').on('click', function() {
                const userId = $(this).data('user-id');
                $('#deleteUserId').val(userId);
            });

            // Delete Modal: Reset when closed
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteUserId').val('');
            });

            // Delete Confirmation: Handle deletion
            $('#confirmDelete').on('click', function() {
                const userId = $('#deleteUserId').val();
                const $row = $(`tr[data-user-id="${userId}"]`);
                const name = $row.find('td:eq(0)').text();
                const email = $row.find('td:eq(1)').text();

                if (!userId) {
                    alert('No user selected. Please try again.');
                    return;
                }

                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'delete_user', user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Remove the user from the Subscribed or Unsubscribed table
                            $row.remove();

                            // Add the user to the Deleted Accounts table
                            const $deletedTableBody = $('#deleted-users-table tbody');
                            $deletedTableBody.append(`
                                <tr data-user-id="${userId}">
                                    <td>${name}</td>
                                    <td>${email}</td>
                                </tr>
                            `);

                            $('#deleteModal').modal('hide');
                            $('#deleteSuccessModal').modal('show');
                        } else {
                            alert('Error: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.error || xhr.responseText || 'Unknown error'));
                    }
                });
            });

            // View User Modal: Populate user information via AJAX
            $('.view-user').on('click', function() {
                const userId = $(this).data('user-id');

                $.ajax({
                    url: 'ajax/get_user_details.php',
                    method: 'POST',
                    data: { user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const user = response.user;
                            const business = response.business || {};

                            $('#viewFirstName').text(user.first_name || 'N/A');
                            $('#viewLastName').text(user.last_name || 'N/A');
                            $('#viewEmail').text(user.email || 'N/A');
                            $('#viewAddress').text(user.address || 'N/A');
                            $('#viewContactNumber').text(user.contact_number || 'N/A');
                            $('#viewBirthday').text(user.birthday || 'N/A');
                            $('#viewCompanyName').text(business.company_name || 'N/A');
                            $('#viewPostalCode').text(business.postal_code || 'N/A');
                            $('#viewBusinessEmail').text(business.business_email || 'N/A');
                            $('#viewBusinessAddress').text(business.business_address || 'N/A');
                            $('#viewBusinessNumber').text(business.business_number || 'N/A');
                            $('#viewSellerType').text(business.seller_type || 'N/A');
                        } else {
                            alert('Error: ' + (response.error || 'Unable to load user details'));
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