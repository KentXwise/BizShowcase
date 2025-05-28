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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="css/settings.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="form-section">
            <div class="form-title">Account Information</div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="update_account" value="1">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Birthday</label>
                        <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                    </div>
                    <div class="col-12 mt-5">
                        <h4 class="text-danger">Delete Your Account</h4>
                        <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                        <button type="button" class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            Permanently Delete Account
                        </button>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="submit-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="cancelChanges()">Cancel</button>
                </div>
            </form>

            <!-- Modal for Delete Account -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Delete Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to permanently delete your account? This action cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="delete_account" value="1">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-title mt-5">Business Information</div>
            <form method="POST">
                <input type="hidden" name="update_business" value="1">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control shadow-sm" name="company_name" value="<?php echo htmlspecialchars($business['company_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Email</label>
                        <input type="email" class="form-control shadow-sm" name="business_email" value="<?php echo htmlspecialchars($business['business_email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Number</label>
                        <input type="text" class="form-control shadow-sm" name="business_number" value="<?php echo htmlspecialchars($business['business_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Postal/Zip Code</label>
                        <input type="text" class="form-control shadow-sm" name="postal_code" value="<?php echo htmlspecialchars($business['postal_code'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control shadow-sm" name="business_address" value="<?php echo htmlspecialchars($business['business_address'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seller Type</label>
                        <select class="form-select shadow-sm" name="seller_type">
                            <option value="" <?php echo (isset($business['seller_type']) && $business['seller_type'] === '') ? 'selected' : ''; ?>>Select Seller Type</option>
                            <option value="Sole Entrepreneurship" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Sole Entrepreneurship') ? 'selected' : ''; ?>>Sole Proprietorship</option>
                            <option value="Partnership" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                            <option value="Cooperation" <?php echo (isset($business['seller_type']) && $business['seller_type'] === 'Cooperation') ? 'selected' : ''; ?>>Cooperative</option>
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="submit-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="cancelChanges()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cancelChanges() {
            if (confirm("Are you sure you want to cancel changes?")) {
                location.reload();
            }
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }

        window.onload = function() {
            toggleSidebar(); // Auto-collapse when page loads
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>