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
    $first_name = htmlspecialchars(filter_input(INPUT_POST, 'first_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(filter_input(INPUT_POST, 'last_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $company_name = htmlspecialchars(filter_input(INPUT_POST, 'company_name', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
    $number = htmlspecialchars(filter_input(INPUT_POST, 'number', FILTER_DEFAULT), ENT_QUOTES, 'UTF-8');
    $subscription_type = $_POST['subscription_type'];

    $certificate = $clearance = $permit = '';
    $docs_dir = '../assets/docs/';
    if (!file_exists($docs_dir) && !mkdir($docs_dir, 0775, true)) {
        $error = "Failed to create documents directory.";
    } elseif (!is_writable($docs_dir)) {
        $error = "Documents directory is not writable.";
    } else {
        if (!empty($_FILES['business_certificate']['name'])) {
            $certificate = $docs_dir . time() . '_' . $_FILES['business_certificate']['name'];
            if (!move_uploaded_file($_FILES['business_certificate']['tmp_name'], $certificate)) {
                $error = "Failed to upload business certificate.";
            }
        }
        if (!empty($_FILES['business_clearance']['name'])) {
            $clearance = $docs_dir . time() . '_' . $_FILES['business_clearance']['name'];
            if (!move_uploaded_file($_FILES['business_clearance']['tmp_name'], $clearance)) {
                $error = "Failed to upload business clearance.";
            }
        }
        if (!empty($_FILES['business_permit']['name'])) {
            $permit = $docs_dir . time() . '_' . $_FILES['business_permit']['name'];
            if (!move_uploaded_file($_FILES['business_permit']['tmp_name'], $permit)) {
                $error = "Failed to upload business permit.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO subscription_requests (user_id, first_name, last_name, email, company_name, number, business_certificate, business_clearance, business_permit, subscription_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $first_name, $last_name, $email, $company_name, $number, $certificate, $clearance, $permit, $subscription_type]);
        $success = "Subscription request submitted successfully";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription - BizShowcase</title>
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
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-post.php">Add Post</a></li>
                    <li class="nav-item"><a class="nav-link active" href="subscription.php">Subscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment.php">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Advertising Request Form</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
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
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($business['company_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Number</label>
                <input type="text" class="form-control" name="number" value="<?php echo htmlspecialchars($business['business_number'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Business Certificate</label>
                <input type="file" class="form-control" name="business_certificate" accept=".pdf,.doc,.docx">
            </div>
            <div class="mb-3">
                <label class="form-label">Business Clearance</label>
                <input type="file" class="form-control" name="business_clearance" accept=".pdf,.doc,.docx">
            </div>
            <div class="mb-3">
                <label class="form-label">Business Permit</label>
                <input type="file" class="form-control" name="business_permit" accept=".pdf,.doc,.docx">
            </div>
            <div class="mb-3">
                <label class="form-label">Subscription Type</label>
                <select class="form-select" name="subscription_type" required>
                    <option value="monthly">Monthly ($150)</option>
                    <option value="yearly">Yearly ($1800)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</body>
</html>