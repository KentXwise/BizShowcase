<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$categories = get_categories($conn);
$posts = get_all_posts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .post-card { margin-bottom: 20px; }
        .post-images { max-height: 200px; overflow-x: auto; white-space: nowrap; }
        .post-image { max-height: 180px; margin-right: 10px; display: inline-block; }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="subscription.php">Subscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment.php">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Filter by Category</h5>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-3">
                            <input type="text" id="searchUser" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <h2>Business Posts</h2>
                <div id="postsContainer">
                    <?php foreach ($posts as $post): ?>
                        <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                <p><?php echo htmlspecialchars($post['description']); ?></p>
                                <div class="post-images">
                                    <?php
                                    $images = get_post_images($conn, $post['post_id']);
                                    if (empty($images)) {
                                        echo '<p>No images available.</p>';
                                    } else {
                                        foreach ($images as $image):
                                            $fullPath = realpath(__DIR__ . '/../' . $image['image_path']);
                                            if ($fullPath && file_exists($fullPath)) {
                                                echo '<img src="../' . htmlspecialchars($image['image_path']) . '" class="post-image" alt="Post Image">';
                                            } else {
                                                echo '<p>Image not found: ' . htmlspecialchars($image['image_path']) . '</p>';
                                            }
                                        endforeach;
                                    }
                                    ?>
                                </div>
                                <button class="btn btn-sm btn-primary like-btn" data-post-id="<?php echo $post['post_id']; ?>">Like</button>
                                <button class="btn btn-sm btn-success favorite-btn" data-post-id="<?php echo $post['post_id']; ?>">Favorite</button>
                                <button class="btn btn-sm btn-info follow-btn" data-user-id="<?php echo $post['post_id']; ?>">Follow</button>
                                <div class="mt-2">
                                    <textarea class="form-control comment-text" placeholder="Add a comment..."></textarea>
                                    <button class="btn btn-sm btn-primary mt-1 comment-btn" data-post-id="<?php echo $post['post_id']; ?>">Comment</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Filter posts by category
            $('#categoryFilter').change(function() {
                let categoryId = $(this).val();
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'filter_posts', category_id: categoryId },
                    success: function(response) {
                        $('#postsContainer').html(response);
                    }
                });
            });

            // Search users
            $('#searchUser').on('input', function() {
                let searchTerm = $(this).val();
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'search_users', search_term: searchTerm },
                    success: function(response) {
                        $('#postsContainer').html(response);
                    }
                });
            });

            // Like post
            $(document).on('click', '.like-btn', function() {
                let postId = $(this).data('post-id');
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'like_post', post_id: postId },
                    success: function(response) {
                        alert('Post liked!');
                    }
                });
            });

            // Favorite post
            $(document).on('click', '.favorite-btn', function() {
                let postId = $(this).data('post-id');
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'favorite_post', post_id: postId },
                    success: function(response) {
                        alert('Post favorited!');
                    }
                });
            });

            // Comment on post
            $(document).on('click', '.comment-btn', function() {
                let postId = $(this).data('post-id');
                let commentText = $(this).prev('.comment-text').val();
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'comment_post', post_id: postId, comment_text: commentText },
                    success: function(response) {
                        alert('Comment added!');
                        $(this).prev('.comment-text').val('');
                    }
                });
            });

            // Follow user
            $(document).on('click', '.follow-btn', function() {
                let userId = $(this).data('user-id');
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'follow_user', followed_id: userId },
                    success: function(response) {
                        alert('User followed!');
                    }
                });
            });

            // Refresh posts after deletion (triggered from profile.php)
            window.addEventListener('storage', function(e) {
                if (e.key === 'postDeleted') {
                    $.ajax({
                        url: 'home.php',
                        method: 'GET',
                        success: function(response) {
                            $('#postsContainer').html($(response).find('#postsContainer').html());
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>