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
$posts = get_user_posts($conn, $_SESSION['user_id']);
$favorites = get_user_favorites($conn, $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file_name = time() . '_' . $_FILES['profile_picture']['name'];
    $file_path = '../assets/img/profiles/' . $file_name;
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$file_path, $_SESSION['user_id']]);
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - BizShowcase</title>
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
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-post.php">Add Post</a></li>
                    <li class="nav-item"><a class="nav-link" href="subscription.php">Subscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment.php">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo $user['profile_picture'] ?: '../assets/img/default-profile.png'; ?>" class="rounded-circle" width="150" height="150" alt="Profile Picture">
                        <h5 class="mt-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Update Profile Picture</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5>User Information</h5>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5>Business Information</h5>
                        <p><strong>Company:</strong> <?php echo htmlspecialchars($business['company_name'] ?? ''); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($business['business_email'] ?? ''); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($business['business_address'] ?? ''); ?></p>
                        <p><strong>Number:</strong> <?php echo htmlspecialchars($business['business_number'] ?? ''); ?></p>
                        <p><strong>Seller Type:</strong> <?php echo htmlspecialchars($business['seller_type'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#posts">My Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#favorites">Favorited Posts</a>
                    </li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane active" id="posts">
                        <?php foreach ($posts as $post): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                    <p><?php echo htmlspecialchars($post['description']); ?></p>
                                    <div class="post-images">
                                        <?php foreach (get_post_images($conn, $post['post_id']) as $image): ?>
                                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" class="post-image" alt="Post Image">
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="btn btn-sm btn-danger delete-post-btn" data-post-id="<?php echo $post['post_id']; ?>">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="tab-pane" id="favorites">
                        <?php foreach ($favorites as $post): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                    <p><?php echo htmlspecialchars($post['description']); ?></p>
                                    <div class="post-images">
                                        <?php foreach (get_post_images($conn, $post['post_id']) as $image): ?>
                                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" class="post-image" alt="Post Image">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.delete-post-btn').on('click', function() {
                if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    let postId = $(this).data('post-id');
                    $.ajax({
                        url: '../ajax/user_actions.php',
                        method: 'POST',
                        data: { action: 'delete_post', post_id: postId },
                        success: function(response) {
                            console.log('Response:', response); // Debug the response
                            if (response && response.success) {
                                alert('Post deleted successfully!');
                                window.location.href = 'profile.php'; // Reload the page
                            } else if (response && response.error) {
                                alert('Error deleting post: ' + response.error);
                            } else {
                                alert('Error deleting post: Unknown error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', error, 'Status:', status, 'XHR:', xhr.responseText);
                            alert('An error occurred while deleting the post.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>