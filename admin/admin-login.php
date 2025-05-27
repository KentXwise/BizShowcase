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
    <meta charset="UTF-8">
    <title>Admin Login | BizShowcase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
</head>
<style>
    body {
        opacity: 0;
        transition: opacity 0.8s ease, transform 0.9s ease;
        transform: translateX(0);
    }

    body.page-loaded {
        opacity: 1;
    }

    body.slide-left {
        opacity: 0;
        transform: translateX(-100%);
    }

    body.slide-right {
        opacity: 0;
        transform: translateX(100%);
    }
</style>

<body style="background-color: #f8f9fc;">
        <div class="container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
            <!-- Left: Form Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-5 flex-grow-1">
                <div class="w-100" style="max-width: 480px;">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold mt-2">BizShowcase</h3>
                    </div>
                    <h2 class="fw-bold mb-3">Welcome, Admin!</h2>
                    <p class="text-muted mb-4">Your admin account manage the platform</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Code</label>
                            <input type="text" class="form-control" name="admin_code" placeholder="Admin Code" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign In<i class="bi bi-box-arrow-in-right ms-1"></i></button>
                        <p class="text-center mt-3">Don't have an account? <a href="admin-signup.php">Sign Up</a></p>
                    </form>
                </div>
            </div>

            <!-- Right Illustration -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center text-white flex-grow-1" style="background: linear-gradient(to right, #6a11cb, #2575fc);">
                <div class="text-center px-4">
                    <img src="../assets/img/Background 1.png" class="img-fluid mb-4" alt="Handshake" style="max-height: 300px;">
                    <h4 class="fw-bold">Connect with businesses worldwide</h4>
                    <p class="text-light">Showcase your products and services to potential customers and partners</p>
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

<!-- done boss -->





</body>
</html>