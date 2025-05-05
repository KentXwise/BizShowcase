<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $admin_code = filter_input(INPUT_POST, 'admin_code', FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE email = ? AND admin_code = ?");
    $stmt->execute([$email, $admin_code]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials or admin code";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin_styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 form-section">
                <div class="w-100" style="max-width: 400px;">
                    <h2 class="fw-bold mb-3">Admin Login</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Code</label>
                            <input type="text" class="form-control" name="admin_code" placeholder="Admin Code" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Log in</button>
                        <p class="text-center mt-3">Don't have an account? <a href="admin-signup.php">Sign up as admin</a></p>
                    </form>
                </div>
            </div>
            <div class="col-md-6 image-section">
                <img src="../assets/img/business-handshake.png" alt="Admin Login" width="600" height="600">
            </div>
        </div>
    </div>
</body>
</html>