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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">BizShowcase</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-post.php">Add Post</a></li>
                    <li class="nav-item"><a class="nav-link" href="subscription.php">Subscription</a></li>
                    <li class="nav-item"><a class="nav-link active" href="payment.php">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Payment</h2>
        <?php if ($subscription): ?>
            <div class="card">
                <div class="card-body">
                    <h5>Subscription Status: <?php echo htmlspecialchars($subscription['subscription_status']); ?></h5>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($subscription['subscription_type']); ?></p>
                    <p><strong>Amount:</strong> $<?php echo $subscription['amount']; ?></p>
                    <?php if ($payment): ?>
                        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($payment['payment_status']); ?></p>
                        <?php if ($payment['receipt_path']): ?>
                            <a href="<?php echo $payment['receipt_path']; ?>" class="btn btn-sm btn-primary" target="_blank">View Receipt</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($subscription['subscription_status'] === 'approved' && (!$payment || $payment['payment_status'] === 'pending')): ?>
                        <button class="btn btn-primary mt-3 initiate-payment" data-subscription-id="<?php echo $subscription['subscription_id']; ?>">Initiate Payment</button>
                        <p class="mt-2">Please visit the admin office to complete your payment in cash.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p>No active subscription found. Please submit a subscription request first.</p>
            <a href="subscription.php" class="btn btn-primary">Go to Subscription</a>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.initiate-payment').click(function() {
                let subscriptionId = $(this).data('subscription-id');
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'initiate_payment', subscription_id: subscriptionId },
                    success: function(response) {
                        alert('Payment initiated. Please visit the admin office to complete payment.');
                    }
                });
            });
        });
    </script>
</body>
</html>