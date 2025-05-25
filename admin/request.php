<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$requests = get_subscription_requests($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Requests - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/request.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2196f3;">
  <div class="container-fluid justify-content-between">
    
    <!-- Hamburger menu for small screens -->
    <button class="btn btn-outline-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
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
    <main class="col-md-10 p-4">
      <h1 class="fw-bold mb-4">Request List</h1>
      <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold">Pending</h4>
        </div>

        <!-- Table -->
        <div class="card-custom mb-5">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Company</th>
                    <th>Subscription Type</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['subscription_type']); ?></td>
                        <td>
                            <?php if ($request['business_certificate']): ?>
                                <a href="<?php echo $request['business_certificate']; ?>" target="_blank">Certificate</a>
                            <?php endif; ?>
                            <?php if ($request['business_clearance']): ?>
                                <a href="<?php echo $request['business_clearance']; ?>" target="_blank">Clearance</a>
                            <?php endif; ?>
                            <?php if ($request['business_permit']): ?>
                                <a href="<?php echo $request['business_permit']; ?>" target="_blank">Permit</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm approve-request" data-request-id="<?php echo $request['request_id']; ?>">Approve</button>
                            <button class="btn btn-danger btn-sm delete-request" data-request-id="<?php echo $request['request_id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.approve-request').click(function() {
                let requestId = $(this).data('request-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'approve_request', request_id: requestId },
                    success: function() {
                        location.reload();
                    }
                });
            });

            $('.delete-request').click(function() {
                if (confirm('Are you sure you want to delete this request?')) {
                    let requestId = $(this).data('request-id');
                    $.ajax({
                        url: '../ajax/admin_actions.php',
                        method: 'POST',
                        data: { action: 'delete_request', request_id: requestId },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>