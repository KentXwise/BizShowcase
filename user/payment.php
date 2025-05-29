<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$subscription = get_user_subscription($conn, $_SESSION['user_id']);
$payment = get_user_payment($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="css/payment.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="form-section">
            <div class="form-title">Payment Dashboard</div>
            <?php if ($subscription): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-3">Subscription Details</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span class="status-<?php echo $subscription['subscription_status'] === 'approved' ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($subscription['subscription_status']); ?></span></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($subscription['subscription_type']); ?></p>
                                <p><strong>Amount:</strong> $<?php echo number_format($subscription['amount'], 2); ?></p>
                            </div>
                            <?php if ($payment): ?>
                                <div class="col-md-6">
                                    <p><strong>Payment Status:</strong> <span class="status-<?php echo $payment['payment_status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></span></p>
                                    <?php if ($payment['receipt_path']): ?>
                                        <a href="<?php echo $payment['receipt_path']; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-file-pdf me-2"></i>View Receipt</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($subscription['subscription_status'] === 'approved' && (!$payment || $payment['payment_status'] === 'pending')): ?>
                            <div class="mt-4">
                                <button class="btn btn-success initiate-payment" data-subscription-id="<?php echo $subscription['subscription_id']; ?>" id="initiate-payment-btn">
                                    <span class="btn-text"><i class="fas fa-money-bill-wave me-2"></i>Initiate Payment</span>
                                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                                </button>
                                <p class="text-muted mt-2"><i class="fas fa-info-circle me-2"></i>Please visit the admin office to complete your payment in cash.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <p class="text-muted mb-3"><i class="fas fa-exclamation-triangle me-2"></i>No active subscription found. Please submit a subscription request first.</p>
                        <a href="subscription.php" class="btn btn-primary"><i class="fas fa-arrow-right me-2"></i>Go to Subscription</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initiate payment
            $('.initiate-payment').click(function() {
                const $btn = $(this);
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner-border');
                const subscriptionId = $btn.data('subscription-id');

                // Show loading state
                $btn.prop('disabled', true);
                $btnText.html('<i class="fas fa-spinner me-2"></i>Initiating...');
                $spinner.removeClass('d-none');

                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'initiate_payment', subscription_id: subscriptionId },
                    success: function(response) {
                        alert('Payment initiated. Please visit the admin office to complete payment.');
                        $btnText.html('<i class="fas fa-money-bill-wave me-2"></i>Initiate Payment');
                        $spinner.addClass('d-none');
                        $btn.prop('disabled', false);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                        $btnText.html('<i class="fas fa-money-bill-wave me-2"></i>Initiate Payment');
                        $spinner.addClass('d-none');
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Sidebar toggle
            window.toggleSidebar = function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
            };

            // Auto-collapse sidebar on page load
            window.onload = function() {
                toggleSidebar();
            };
        });
    </script>
</body>
</html>