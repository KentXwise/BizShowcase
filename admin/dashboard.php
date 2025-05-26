<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$stats = get_dashboard_stats($conn);
$subscribed_users = get_subscribed_users($conn);
$unsubscribed_users = get_unsubscribed_users($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2196f3;">
  <div class="container-fluid justify-content-between">
    
    <!-- Hamburger menu for small screens -->
    <button class="btn btn-outline-light d-lg-none" type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarOffcanvas"
        aria-controls="sidebarOffcanvas">
        ☰
    </button>

    <!-- Branding -->
    <div class="d-flex align-items-center">
      <img src="img/logo.png" alt="Profile" width="55px" class="rounded-circle me-2">
      <span class="navbar-brand fw-bold">BIZShowcase</span>
    </div>

    <img src="img/gigago.png" alt="Profile" width="50px" class="rounded-circle">
  </div>
</nav>

<div class="container-fluid">
    <div class="row">
      <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-10 p-4 ms-lg-220">
            <h1 class="fw-bold">Overview</h1>

            <!-- Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="overview-card">
                        <h5>Total Users</h5>
                        <p><?php echo $stats['total_users']; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="overview-card">
                        <h5>Subscribed Users</h5>
                        <p><?php echo $stats['subscribed_users']; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="overview-card">
                        <h5>Total Posts</h5>
                        <p><?php echo $stats['total_posts']; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="overview-card">
                        <h5>Unsubscribed Users</h5>
                        <p><?php echo $stats['unsubscribed_users']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Customers Table -->
            <h2 class="fw-bold mt-5">Subscribers</h2>
            <div class="data-table">
              <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subscription Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribed_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['subscription_type']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
              </div>
            </div>
            <br>

            <h2 class="fw-bold">Unsubscribe</h2>
            <div class="data-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unsubscribed_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- end of col-md-10 -->
    </div> <!-- end of row -->
</div> <!-- end of container-fluid -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
