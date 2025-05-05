<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 form-section">
                <div class="w-100" style="max-width: 400px;">
                    <h2 class="fw-bold mb-3">Welcome back!</h2>
                    <p class="mb-4">Enter your credentials to access your account</p>
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
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label for="remember" class="form-check-label">Remember me!</label>
                            </div>
                            <a href="#" class="text-decoration-none">Forgot Password</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Log in</button>
                        <p class="text-center mt-3">Don’t have an account? <a href="signup.php">Sign up</a></p>
                    </form>
                </div>
            </div>
            <div class="col-md-6 image-section">
                <img src="../assets/img/business-handshake.png" alt="Login Illustration" width="600" height="600">
            </div>
        </div>
    </div>
</body>
</html>