<?php
session_start();
require_once '../admin/includes/db_connect.php';
require_once '../admin/includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'suspend_user':
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id) {
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            echo "Success";
        }
        break;

    case 'delete_user':
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id) {
            $stmt = $conn->prepare("UPDATE users SET status = 'deleted' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            echo "Success";
        }
        break;

    case 'delete_category':
        $category_id = $_POST['category_id'] ?? 0;
        if ($category_id) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            echo "Success";
        }
        break;

    case 'approve_category':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            $stmt = $conn->prepare("SELECT category_name FROM category_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("INSERT INTO categories (category_name, status) VALUES (?, 'approved')");
            $stmt->execute([$request['category_name']]);

            $stmt = $conn->prepare("UPDATE category_requests SET status = 'approved' WHERE request_id = ?");
            $stmt->execute([$request_id]);
            echo "Success";
        }
        break;

    case 'reject_category':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            $stmt = $conn->prepare("UPDATE category_requests SET status = 'rejected' WHERE request_id = ?");
            $stmt->execute([$request_id]);
            echo "Success";
        }
        break;

    case 'approve_request':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            $stmt = $conn->prepare("SELECT user_id, subscription_type FROM subscription_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            $amount = $request['subscription_type'] === 'monthly' ? 150 : 1800;
            $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, subscription_type, subscription_status, amount) VALUES (?, ?, 'approved', ?)");
            $stmt->execute([$request['user_id'], $request['subscription_type'], $amount]);

            $stmt = $conn->prepare("UPDATE subscription_requests SET status = 'approved' WHERE request_id = ?");
            $stmt->execute([$request_id]);

            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'subscription_approved', 'Your subscription request has been approved')");
            $stmt->execute([$request['user_id']]);
            echo "Success";
        }
        break;

    case 'delete_request':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            $stmt = $conn->prepare("DELETE FROM subscription_requests WHERE request_id = ?");
            $stmt->execute([$request_id]);
            echo "Success";
        }
        break;

    case 'approve_payment':
        $payment_id = $_POST['payment_id'] ?? 0;
        if ($payment_id) {
            $stmt = $conn->prepare("SELECT subscription_id, user_id, amount FROM payments WHERE payment_id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate receipt (simplified as PDF path)
            $receipt_path = '../assets/receipts/receipt_' . $payment_id . '.pdf';
            // In a real system, use a PDF library like TCPDF to generate the receipt
            file_put_contents($receipt_path, "Receipt for Payment ID: $payment_id, Amount: {$payment['amount']}"); // Placeholder

            $stmt = $conn->prepare("UPDATE payments SET payment_status = 'paid', receipt_path = ? WHERE payment_id = ?");
            $stmt->execute([$receipt_path, $payment_id]);

            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'payment_approved', 'Your payment has been approved')");
            $stmt->execute([$payment['user_id']]);
            echo "Success";
        }
        break;

    case 'reject_payment':
        $payment_id = $_POST['payment_id'] ?? 0;
        if ($payment_id) {
            $stmt = $conn->prepare("UPDATE payments SET payment_status = 'rejected' WHERE payment_id = ?");
            $stmt->execute([$payment_id]);
            echo "Success";
        }
        break;
}
?>