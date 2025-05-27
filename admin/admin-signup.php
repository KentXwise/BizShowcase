<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_code = filter_input(INPUT_POST, 'admin_code', FILTER_SANITIZE_STRING);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = "Email or username already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (full_name, username, email, password, admin_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $email, $hashed_password, $admin_code]);
            header("Location: admin-login.php");
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
    <title>Admin Sign Up - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin_styles.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fc;" onload="document.body.classList.remove('fade-out')">
    <div class="container-fluid min-vh-100">
        <div class="row">
            <!-- Left: Visual Section -->
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center text-white" style="background: linear-gradient(to right, #6a11cb, #2575fc);">
                <div class="text-center px-4">
                    <img src="../assets/img/Background 1.png" class="img-fluid mb-4" alt="Handshake" style="max-height: 300px;">
                    <h4 class="fw-bold">Connect with businesses worldwide</h4>
                    <p class="text-light">Showcase your products and services to potential customers and partners</p>
                </div>
            </div>
            <!-- Right: Form Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-5">
                <div class="form-section-wrapper w-100">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold mt-2">BizShowcase</h3>
                    </div>
                    <h2 class="fw-bold mb-3">Admin SignUp</h2>
                    <p class="text-muted mb-4">Create your admin account to manage the platform</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
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
                            <label class="form-label">Admin Code</label>
                            <input type="text" class="form-control" name="admin_code" placeholder="Admin Code" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign Up<i class="bi bi-box-arrow-in-right ms-1"></i></button>
                        <p class="text-center mt-3"> Already have an account? <a href="admin-login.php">Log in</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    body.classList.add("page-loaded");

    const links = document.querySelectorAll("a[href='admin-login.php'], a[href='admin-signup.php']");
    links.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const href = link.getAttribute("href");

            // Determine direction of slide
            if (href.includes("signup")) {
                body.classList.add("slide-right");
            } else {
                body.classList.add("slide-left");
            }

            setTimeout(() => {
                window.location.href = href;
            }, 500); // must match CSS duration
        });
    });
});
</script>



</body>
</html>