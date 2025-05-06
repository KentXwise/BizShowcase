<?php
session_start();
require_once '../admin/includes/db_connect.php';
require_once '../admin/includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json'); // Ensure JSON response

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'suspend_user':
        $user_id = $_POST['user_id'] ?? 0;
        $days = $_POST['days'] ?? 0;
        if ($user_id && $days > 0) {
            try {
                $end_date = date('Y-m-d H:i:s', strtotime("+$days days"));
                $stmt = $conn->prepare("UPDATE users SET status = 'suspended', suspension_end_date = ? WHERE user_id = ?");
                $stmt->execute([$end_date, $user_id]);

                // Notify the user
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'account_suspended', ?)");
                $stmt->execute([$user_id, "Your account has been suspended until $end_date."]);
                echo json_encode(['success' => true, 'message' => 'User suspended successfully']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid user ID or suspension duration']);
        }
        break;

    case 'unsuspend_user':
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id) {
            try {
                $stmt = $conn->prepare("UPDATE users SET status = 'active', suspension_end_date = NULL WHERE user_id = ?");
                $stmt->execute([$user_id]);

                // Notify the user
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'account_unsuspended', 'Your account has been unsuspended.')");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true, 'message' => 'User unsuspended successfully']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid user ID']);
        }
        break;

    case 'delete_user':
        $user_id = $_POST['user_id'] ?? 0;
        if ($user_id) {
            try {
                $stmt = $conn->prepare("UPDATE users SET status = 'deleted' WHERE user_id = ?");
                $stmt->execute([$user_id]);
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid user ID']);
        }
        break;

    case 'delete_category':
        $category_id = $_POST['category_id'] ?? 0;
        if ($category_id) {
            try {
                $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
                $stmt->execute([$category_id]);
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid category ID']);
        }
        break;

    case 'approve_category':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            try {
                $stmt = $conn->prepare("SELECT category_name FROM category_requests WHERE request_id = ?");
                $stmt->execute([$request_id]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($request) {
                    $stmt = $conn->prepare("INSERT INTO categories (category_name, status) VALUES (?, 'approved')");
                    $stmt->execute([$request['category_name']]);

                    $stmt = $conn->prepare("UPDATE category_requests SET status = 'approved' WHERE request_id = ?");
                    $stmt->execute([$request_id]);
                    echo json_encode(['success' => true, 'message' => 'Category approved successfully']);
                } else {
                    echo json_encode(['error' => 'Category request not found']);
                }
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid request ID']);
        }
        break;

    case 'reject_category':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            try {
                $stmt = $conn->prepare("UPDATE category_requests SET status = 'rejected' WHERE request_id = ?");
                $stmt->execute([$request_id]);
                echo json_encode(['success' => true, 'message' => 'Category request rejected']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid request ID']);
        }
        break;

    case 'approve_request':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            try {
                $stmt = $conn->prepare("SELECT user_id, subscription_type FROM subscription_requests WHERE request_id = ?");
                $stmt->execute([$request_id]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($request) {
                    $amount = $request['subscription_type'] === 'monthly' ? 150 : 1800;
                    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, subscription_type, subscription_status, amount) VALUES (?, ?, 'approved', ?)");
                    $stmt->execute([$request['user_id'], $request['subscription_type'], $amount]);

                    $stmt = $conn->prepare("UPDATE subscription_requests SET status = 'approved' WHERE request_id = ?");
                    $stmt->execute([$request_id]);

                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'subscription_approved', 'Your subscription request has been approved')");
                    $stmt->execute([$request['user_id']]);
                    echo json_encode(['success' => true, 'message' => 'Subscription request approved']);
                } else {
                    echo json_encode(['error' => 'Subscription request not found']);
                }
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid request ID']);
        }
        break;

    case 'delete_request':
        $request_id = $_POST['request_id'] ?? 0;
        if ($request_id) {
            try {
                $stmt = $conn->prepare("DELETE FROM subscription_requests WHERE request_id = ?");
                $stmt->execute([$request_id]);
                echo json_encode(['success' => true, 'message' => 'Subscription request deleted']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid request ID']);
        }
        break;

    case 'approve_payment':
        $payment_id = $_POST['payment_id'] ?? 0;
        if ($payment_id) {
            try {
                $stmt = $conn->prepare("SELECT subscription_id, user_id, amount FROM payments WHERE payment_id = ?");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($payment) {
                    $receipt_path = '../assets/receipts/receipt_' . $payment_id . '.pdf';
                    file_put_contents($receipt_path, "Receipt for Payment ID: $payment_id, Amount: {$payment['amount']}");
                    $stmt = $conn->prepare("UPDATE payments SET payment_status = 'paid', receipt_path = ? WHERE payment_id = ?");
                    $stmt->execute([$receipt_path, $payment_id]);

                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'payment_approved', 'Your payment has been approved')");
                    $stmt->execute([$payment['user_id']]);
                    echo json_encode(['success' => true, 'message' => 'Payment approved successfully']);
                } else {
                    echo json_encode(['error' => 'Payment not found']);
                }
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid payment ID']);
        }
        break;

    case 'reject_payment':
        $payment_id = $_POST['payment_id'] ?? 0;
        if ($payment_id) {
            try {
                $stmt = $conn->prepare("UPDATE payments SET payment_status = 'rejected' WHERE payment_id = ?");
                $stmt->execute([$payment_id]);
                echo json_encode(['success' => true, 'message' => 'Payment rejected']);
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Invalid payment ID']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>