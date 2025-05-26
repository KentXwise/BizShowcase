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
    <link href="css/transaction.css" rel="stylesheet">
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
    <div class="col-lg-10 content-area">
      <h2 class="fw-bold mb-4">Manage Transactions</h2>
        <div class="data-table card-custom">
            <div class="table-responsive">
            <table class="table align-middle">
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
                                <button class="btn btn-success btn-sm approve-payment" data-payment-id="<?php echo $transaction['payment_id']; ?>">Paid</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
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