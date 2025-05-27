<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$categories = get_categories($conn);
$category_requests = get_category_requests($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);
    $stmt = $conn->prepare("INSERT INTO categories (category_name, status) VALUES (?, 'approved')");
    $stmt->execute([$category_name]);
    header("Location: category.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categories - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/category.css" rel="stylesheet">
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
    <div class="col-lg-10 content-area">
      <h1 class="fw-bold mb-4">Category List</h1>
      <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold">Services</h4>
        </div>
        
        <form method="POST" class="mb-4">
            <input type="hidden" name="add_category" value="1">
            <div class="mb-3">
                <label class="form-label">New Category</label>
                <input type="text" class="form-control" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>

        <h3>Existing Categories</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($category['status']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-category" data-category-id="<?php echo $category['category_id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Category Requests</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Category Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($category_requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($request['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm approve-category" data-request-id="<?php echo $request['request_id']; ?>">Approve</button>
                            <button class="btn btn-danger btn-sm reject-category" data-request-id="<?php echo $request['request_id']; ?>">Reject</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.delete-category').click(function() {
                if (confirm('Are you sure you want to delete this category?')) {
                    let categoryId = $(this).data('category-id');
                    $.ajax({
                        url: '../ajax/admin_actions.php',
                        method: 'POST',
                        data: { action: 'delete_category', category_id: categoryId },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });

            $('.approve-category').click(function() {
                let requestId = $(this).data('request-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'approve_category', request_id: requestId },
                    success: function() {
                        location.reload();
                    }
                });
            });

            $('.reject-category').click(function() {
                let requestId = $(this).data('request-id');
                $.ajax({
                    url: '../ajax/admin_actions.php',
                    method: 'POST',
                    data: { action: 'reject_category', request_id: requestId },
                    success: function() {
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
