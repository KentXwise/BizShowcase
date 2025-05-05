<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$transactions = get_transactions($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transactions - BizShowcase</title>
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
                    <li class="nav-item"><a class="nav-link active" href="transaction.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="request.php">Requests</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Transactions</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Subscription Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['subscription_type']); ?></td>
                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($transaction['payment_status']); ?></td>
                        <td>
                            <?php if ($transaction['payment_status'] === 'pending'): ?>
                                <button class="btn btn-success btn-sm approve-payment" data-payment-id="<?php echo $transaction['payment_id']; ?>">Approve</button>
                                <button class="btn btn-danger btn-sm reject-payment" data-payment-id="<?php echo $transaction['payment_id']; ?>">Reject</button>
                            <?php endif; ?>
                            <?php if ($transaction['receipt_path']): ?>
                                <a href="<?php echo $transaction['receipt_path']; ?>" class="btn btn-sm btn-primary" target="_blank">View Receipt</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('.approve-payment').click(function() {
                let paymentId = $(this).data('payment-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'approve_payment', payment_id: paymentId },
                    success: function() {
                        location.reload();
                    }
                });
            });

            $('.reject-payment').click(function() {
                let paymentId = $(this).data('payment-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'reject_payment', payment_id: paymentId },
                    success: function() {
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>