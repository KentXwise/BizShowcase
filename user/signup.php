<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $business_name = filter_input(INPUT_POST, 'business_name', FILTER_SANITIZE_STRING);
    $business_address = filter_input(INPUT_POST, 'business_address', FILTER_SANITIZE_STRING);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, email, password) VALUES (?, ?, ?)");
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';
            $stmt->execute([$first_name, $email, $hashed_password]);
            $user_id = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO business_profiles (user_id, company_name, business_address) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $business_name, $business_address]);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 form-section">
                <div class="w-100" style="max-width: 500px;">
                    <h2 class="fw-bold mb-3">Get Started Now</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" name="business_name" placeholder="Business Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Address</label>
                            <input type="text" class="form-control" name="business_address" placeholder="Business Address" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">I agree to the term & policy</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign up</button>
                        <p class="text-center mt-3">Already have an account? <a href="login.php">Log in</a></p>
                    </form>
                </div>
            </div>
            <div class="col-md-6 image-section">
                <img src="../assets/img/business-handshake.png" alt="Sign Up Illustration" width="600" height="600">
            </div>
        </div>
    </div>
</body>
</html>