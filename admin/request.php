<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$requests = get_subscription_requests($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Requests - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">BizShowcase Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="accounts.php">Accounts</a></li>
                    <li class="nav-item"><a class="nav-link" href="category.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaction.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link active" href="request.php">Requests</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Subscription Requests</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Company</th>
                    <th>Subscription Type</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['subscription_type']); ?></td>
                        <td>
                            <?php if ($request['business_certificate']): ?>
                                <a href="<?php echo $request['business_certificate']; ?>" target="_blank">Certificate</a>
                            <?php endif; ?>
                            <?php if ($request['business_clearance']): ?>
                                <a href="<?php echo $request['business_clearance']; ?>" target="_blank">Clearance</a>
                            <?php endif; ?>
                            <?php if ($request['business_permit']): ?>
                                <a href="<?php echo $request['business_permit']; ?>" target="_blank">Permit</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm approve-request" data-request-id="<?php echo $request['request_id']; ?>">Approve</button>
                            <button class="btn btn-danger btn-sm delete-request" data-request-id="<?php echo $request['request_id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('.approve-request').click(function() {
                let requestId = $(this).data('request-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'approve_request', request_id: requestId },
                    success: function() {
                        location.reload();
                    }
                });
            });

            $('.delete-request').click(function() {
                if (confirm('Are you sure you want to delete this request?')) {
                    let requestId = $(this).data('request-id');
                    $.ajax({
                        url: '../ajax/admin_actions.php',
                        method: 'POST',
                        data: { action: 'delete_request', request_id: requestId },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>