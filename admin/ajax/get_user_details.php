<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false];

try {
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        // Debug: Log the user_id
        error_log("Processing user_id: " . $user_id);

        // Fetch user account information
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, address, contact_number, birthday FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch business information
        $stmt = $conn->prepare("SELECT business_id, user_id, company_name, postal_code, business_email, business_address, business_number, seller_type FROM business_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $business = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response['success'] = true;
            $response['user'] = $user;
            $response['business'] = $business ?: [];
        } else {
            $response['error'] = 'User not found';
        }
    } else {
        $response['error'] = 'Invalid user ID';
    }
} catch (PDOException $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
    error_log("PDOException: " . $e->getMessage());
}

echo json_encode($response);
?>