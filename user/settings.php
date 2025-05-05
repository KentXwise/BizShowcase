<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = get_user_info($conn, $_SESSION['user_id']);
$business = get_business_profile($conn, $_SESSION['user_id']);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_account'])) {
        $first_name = htmlspecialchars(filter_input(INPUT_POST, 'first_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(filter_input(INPUT_POST, 'last_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $address = htmlspecialchars(filter_input(INPUT_POST, 'address', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $contact_number = htmlspecialchars(filter_input(INPUT_POST, 'contact_number', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $birthday = $_POST['birthday'];

        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, contact_number = ?, birthday = ? WHERE user_id = ?");
        $stmt->execute([$first_name, $last_name, $email, $address, $contact_number, $birthday, $_SESSION['user_id']]);
        $success = "Account updated successfully";
    } elseif (isset($_POST['update_business'])) {
        $company_name = htmlspecialchars(filter_input(INPUT_POST, 'company_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $postal_code = htmlspecialchars(filter_input(INPUT_POST, 'postal_code', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $business_email = filter_input(INPUT_POST, 'business_email', FILTER_SANITIZE_EMAIL);
        $business_address = htmlspecialchars(filter_input(INPUT_POST, 'business_address', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $business_number = htmlspecialchars(filter_input(INPUT_POST, 'business_number', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
        $seller_type = htmlspecialchars(filter_input(INPUT_POST, 'seller_type', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');

        $stmt = $conn->prepare("UPDATE business_profiles SET company_name = ?, postal_code = ?, business_email = ?, business_address = ?, business_number = ?, seller_type = ? WHERE user_id = ?");
        $stmt->execute([$company_name, $postal_code, $business_email, $business_address, $business_number, $seller_type, $_SESSION['user_id']]);
        $success = "Business profile updated successfully";
    } elseif (isset($_POST['delete_account'])) {
        $stmt = $conn->prepare("UPDATE users SET status = 'deleted' WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">BizShowcase</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-post.php">Add Post</a></li>
                    <li class="nav-item"><a class="nav-link" href="subscription.php">Subscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment.php">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Settings</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <h3>Account Information</h3>
        <form method="POST">
            <input type="hidden" name="update_account" value="1">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Birthday</label>
                <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Account</button>
        </form>

        <h3 class="mt-4">Business Information</h3>
        <form method="POST">
            <input type="hidden" name="update_business" value="1">
            <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($business['company_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Postal/Zip Code</label>
                <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($business['postal_code'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Business Email</label>
                <input type="email" class="form-control" name="business_email" value="<?php echo htmlspecialchars($business['business_email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Business Address</label>
                <input type="text" class="form-control" name="business_address" value="<?php echo htmlspecialchars($business['business_address'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Business Number</label>
                <input type="text" class="form-control" name="business_number" value="<?php echo htmlspecialchars($business['business_number'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Seller Type</label>
                <select class="form-control" name="seller_type">
                    <option value="" <?php echo (isset($business['seller_type']) && $business['seller_type'] === '') ? 'selected' : ''; ?>>Select Seller Type</option>
                    <option value="Sole Entrepreneurship" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Sole Entrepreneurship') ? 'selected' : ''; ?>>Sole Entrepreneurship</option>
                    <option value="Partnership" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                    <option value="Cooperation" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Cooperation') ? 'selected' : ''; ?>>Cooperation</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Business</button>
        </form>

        <h3 class="mt-4">Delete Account</h3>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
            <input type="hidden" name="delete_account" value="1">
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>
    </div>
</body>
</html>