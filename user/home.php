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

// Set current date and time (12:10 PM PST on Monday, May 26, 2025)
$currentDateTime = new DateTime('2025-05-26 12:10:00', new DateTimeZone('America/Los_Angeles'));

// Function to get counts for likes, favorites, and comments
function get_post_counts($conn, $post_id) {
    $stmt = $conn->prepare("SELECT 
        (SELECT COUNT(*) FROM likes WHERE post_id = ?) AS like_count,
        (SELECT COUNT(*) FROM favorites WHERE post_id = ?) AS favorite_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = ?) AS comment_count");
    $stmt->execute([$post_id, $post_id, $post_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/home.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="home.php">BizShowcase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="home.php">Home</a></li>
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
                <div class="card filter-card">
                    <div class="card-body">
                        <h5 class="card-title">Filters</h5>
                        <div class="mb-3">
                            <label for="categoryFilter" class="form-label">Category</label>
                            <select id="categoryFilter" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="searchUser" class="form-label">Search Users</label>
                            <input type="text" id="searchUser" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <h2 class="mb-4">Business Posts</h2>
                <p class="text-muted">Current Date and Time: <?php echo $currentDateTime->format('l, F j, Y g:i A T'); ?></p>
                <div id="postsContainer" class="row">
                    <?php foreach ($posts as $post): ?>
                        <?php 
                        $counts = get_post_counts($conn, $post['post_id']);
                        $images = get_post_images($conn, $post['post_id']);
                        // Check if the user has liked or favorited this post
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
                        $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                        $has_liked = $stmt->fetchColumn() > 0;
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = ? AND user_id = ?");
                        $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                        $has_favorited = $stmt->fetchColumn() > 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($post['description'], 0, 100)) . (strlen($post['description']) > 100 ? '...' : ''); ?></p>
                                    <?php if (!empty($images)): ?>
                                        <div class="post-images-container">
                                            <img src="../<?php echo htmlspecialchars($images[0]['image_path']); ?>" 
                                                 class="post-image" 
                                                 alt="Post Image" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#postModal-<?php echo $post['post_id']; ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="post-images-container text-muted">No images available</div>
                                    <?php endif; ?>
                                    <div class="post-stats">
                                        <span><i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $counts['like_count']; ?></span> Likes</span>
                                        <span><i class="fas fa-star"></i> <span class="favorite-count"><?php echo $counts['favorite_count']; ?></span> Favorites</span>
                                        <span><i class="fas fa-comment"></i> <span class="comment-count"><?php echo $counts['comment_count']; ?></span> Comments</span>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary action-btn like-btn" data-post-id="<?php echo $post['post_id']; ?>" <?php echo $has_liked ? 'disabled' : ''; ?>>
                                            <i class="fas fa-thumbs-up"></i> <?php echo $has_liked ? 'Liked' : 'Like'; ?>
                                        </button>
                                        <button class="btn btn-success action-btn favorite-btn" data-post-id="<?php echo $post['post_id']; ?>" <?php echo $has_favorited ? 'disabled' : ''; ?>>
                                            <i class="fas fa-star"></i> <?php echo $has_favorited ? 'Favorited' : 'Favorite'; ?>
                                        </button>
                                        <button class="btn btn-info action-btn follow-btn" data-user-id="<?php echo $post['user_id']; ?>">
                                            <i class="fas fa-user-plus"></i> Follow
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <textarea class="form-control comment-text" placeholder="Add a comment..."></textarea>
                                        <button class="btn btn-primary action-btn mt-2 comment-btn" data-post-id="<?php echo $post['post_id']; ?>">Comment</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Modal -->
                            <div class="modal fade" id="postModal-<?php echo $post['post_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if (!empty($images)): ?>
                                                <div id="carousel-<?php echo $post['post_id']; ?>" class="carousel slide">
                                                    <div class="carousel-inner">
                                                        <?php foreach ($images as $index => $image): ?>
                                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                                     class="d-block" 
                                                                     alt="Post Image">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php if (count($images) > 1): ?>
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $post['post_id']; ?>" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon"></span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $post['post_id']; ?>" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon"></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <p>No images available.</p>
                                            <?php endif; ?>
                                            <p class="mt-3"><?php echo htmlspecialchars($post['description']); ?></p>
                                            <div class="post-stats">
                                                <span><i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $counts['like_count']; ?></span> Likes</span>
                                                <span><i class="fas fa-star"></i> <span class="favorite-count"><?php echo $counts['favorite_count']; ?></span> Favorites</span>
                                                <span><i class="fas fa-comment"></i> <span class="comment-count"><?php echo $counts['comment_count']; ?></span> Comments</span>
                                            </div>
                                            <button class="btn btn-outline-primary mt-3 show-comments-btn" data-post-id="<?php echo $post['post_id']; ?>">
                                                Show Comments
                                            </button>
                                            <div class="comments-section mt-3" style="display: none;">
                                                <h6>Comments</h6>
                                                <div class="comments-list"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="actionToast" class="toast" role="alert">
            <div class="toast-body"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to render posts from JSON data
            function renderPosts(posts) {
                let html = '';
                posts.forEach(post => {
                    html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card post-card" data-post-id="${post.post_id}">
                                <div class="card-body">
                                    <h5 class="card-title">${post.company_name}</h5>
                                    <p class="card-text">${post.description.substring(0, 100)}${post.description.length > 100 ? '...' : ''}</p>
                                    <div class="post-images-container">
                                        ${post.images.length > 0 ? 
                                            `<img src="../${post.images[0].image_path}" class="post-image" alt="Post Image" data-bs-toggle="modal" data-bs-target="#postModal-${post.post_id}">` : 
                                            `<div class="post-images-container text-muted">No images available</div>`
                                        }
                                    </div>
                                    <div class="post-stats">
                                        <span><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span> Likes</span>
                                        <span><i class="fas fa-star"></i> <span class="favorite-count">0</span> Favorites</span>
                                        <span><i class="fas fa-comment"></i> <span class="comment-count">0</span> Comments</span>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary action-btn like-btn" data-post-id="${post.post_id}" ${post.has_liked ? 'disabled' : ''}>
                                            <i class="fas fa-thumbs-up"></i> ${post.has_liked ? 'Liked' : 'Like'}
                                        </button>
                                        <button class="btn btn-success action-btn favorite-btn" data-post-id="${post.post_id}" ${post.has_favorited ? 'disabled' : ''}>
                                            <i class="fas fa-star"></i> ${post.has_favorited ? 'Favorited' : 'Favorite'}
                                        </button>
                                        <button class="btn btn-info action-btn follow-btn" data-user-id="${post.user_id}">
                                            <i class="fas fa-user-plus"></i> Follow
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <textarea class="form-control comment-text" placeholder="Add a comment..."></textarea>
                                        <button class="btn btn-primary action-btn mt-2 comment-btn" data-post-id="${post.post_id}">Comment</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="postModal-${post.post_id}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">${post.company_name}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${post.images.length > 0 ? `
                                                <div id="carousel-${post.post_id}" class="carousel slide">
                                                    <div class="carousel-inner">
                                                        ${post.images.map((img, index) => `
                                                            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                                                <img src="../${img.image_path}" class="d-block" alt="Post Image">
                                                            </div>
                                                        `).join('')}
                                                    </div>
                                                    ${post.images.length > 1 ? `
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${post.post_id}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon"></span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-${post.post_id}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon"></span>
                                                        </button>
                                                    ` : ''}
                                                </div>
                                            ` : `<p>No images available.</p>`}
                                            <p class="mt-3">${post.description}</p>
                                            <div class="post-stats">
                                                <span><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span> Likes</span>
                                                <span><i class="fas fa-star"></i> <span class="favorite-count">0</span> Favorites</span>
                                                <span><i class="fas fa-comment"></i> <span class="comment-count">0</span> Comments</span>
                                            </div>
                                            <button class="btn btn-outline-primary mt-3 show-comments-btn" data-post-id="${post.post_id}">
                                                Show Comments
                                            </button>
                                            <div class="comments-section mt-3" style="display: none;">
                                                <h6>Comments</h6>
                                                <div class="comments-list"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                $('#postsContainer').html(html);
            }

            // Fetch initial counts for existing posts
            $('#postsContainer .post-card').each(function() {
                let postId = $(this).data('post-id');
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'get_post_counts', post_id: postId },
                    success: function(response) {
                        if (response.counts) {
                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                            let $modal = $(`#postModal-${postId}`);
                            $card.find('.like-count').text(response.counts.like_count);
                            $card.find('.favorite-count').text(response.counts.favorite_count);
                            $card.find('.comment-count').text(response.counts.comment_count);
                            $modal.find('.like-count').text(response.counts.like_count);
                            $modal.find('.favorite-count').text(response.counts.favorite_count);
                            $modal.find('.comment-count').text(response.counts.comment_count);
                        }
                    }
                });
            });

            // Filter posts by category
            $('#categoryFilter').change(function() {
                let categoryId = $(this).val();
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'filter_posts', category_id: categoryId },
                    success: function(response) {
                        if (response.posts) {
                            renderPosts(response.posts);
                            // Fetch counts for filtered posts
                            $('#postsContainer .post-card').each(function() {
                                let postId = $(this).data('post-id');
                                $.ajax({
                                    url: '../ajax/user_actions.php',
                                    method: 'POST',
                                    data: { action: 'get_post_counts', post_id: postId },
                                    success: function(response) {
                                        if (response.counts) {
                                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                                            let $modal = $(`#postModal-${postId}`);
                                            $card.find('.like-count').text(response.counts.like_count);
                                            $card.find('.favorite-count').text(response.counts.favorite_count);
                                            $card.find('.comment-count').text(response.counts.comment_count);
                                            $modal.find('.like-count').text(response.counts.like_count);
                                            $modal.find('.favorite-count').text(response.counts.favorite_count);
                                            $modal.find('.comment-count').text(response.counts.comment_count);
                                        }
                                    }
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Filter error:', xhr.responseText);
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
                        if (response.posts) {
                            renderPosts(response.posts);
                            // Fetch counts for searched posts
                            $('#postsContainer .post-card').each(function() {
                                let postId = $(this).data('post-id');
                                $.ajax({
                                    url: '../ajax/user_actions.php',
                                    method: 'POST',
                                    data: { action: 'get_post_counts', post_id: postId },
                                    success: function(response) {
                                        if (response.counts) {
                                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                                            let $modal = $(`#postModal-${postId}`);
                                            $card.find('.like-count').text(response.counts.like_count);
                                            $card.find('.favorite-count').text(response.counts.favorite_count);
                                            $card.find('.comment-count').text(response.counts.comment_count);
                                            $modal.find('.like-count').text(response.counts.like_count);
                                            $modal.find('.favorite-count').text(response.counts.favorite_count);
                                            $modal.find('.comment-count').text(response.counts.comment_count);
                                        }
                                    }
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Search error:', xhr.responseText);
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
                        if (response.success) {
                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                            let $modal = $(`#postModal-${postId}`);
                            let currentLikes = parseInt($card.find('.like-count').text()) || 0;
                            $card.find('.like-count').text(currentLikes + 1);
                            $modal.find('.like-count').text(currentLikes + 1);
                            $card.find('.like-btn').prop('disabled', true).html('<i class="fas fa-thumbs-up"></i> Liked');
                        }
                    },
                    error: function(xhr) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            showToast(response.error);
                        }
                        console.error('Like error:', xhr.responseText);
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
                        if (response.success) {
                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                            let $modal = $(`#postModal-${postId}`);
                            let currentFavorites = parseInt($card.find('.favorite-count').text()) || 0;
                            $card.find('.favorite-count').text(currentFavorites + 1);
                            $modal.find('.favorite-count').text(currentFavorites + 1);
                            $card.find('.favorite-btn').prop('disabled', true).html('<i class="fas fa-star"></i> Favorited');
                        }
                    },
                    error: function(xhr) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            showToast(response.error);
                        }
                        console.error('Favorite error:', xhr.responseText);
                    }
                });
            });

            // Comment on post
            $(document).on('click', '.comment-btn', function() {
                let postId = $(this).data('post-id');
                let commentText = $(this).prev('.comment-text').val();
                if (commentText.trim() === '') return;
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'comment_post', post_id: postId, comment_text: commentText },
                    success: function(response) {
                        if (response.success) {
                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                            let $modal = $(`#postModal-${postId}`);
                            let currentComments = parseInt($card.find('.comment-count').text()) || 0;
                            $card.find('.comment-count').text(currentComments + 1);
                            $modal.find('.comment-count').text(currentComments + 1);
                            $card.find('.comment-text').val('');
                            // If comments are visible in the modal, refresh them
                            let $commentsSection = $modal.find('.comments-section');
                            if ($commentsSection.is(':visible')) {
                                fetchComments(postId);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Comment error:', xhr.responseText);
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
                        if (response.success) {
                            $(this).prop('disabled', true).text('Following');
                        }
                    },
                    error: function(xhr) {
                        console.error('Follow error:', xhr.responseText);
                    }
                });
            });

            // Show comments in modal
            $(document).on('click', '.show-comments-btn', function() {
                let postId = $(this).data('post-id');
                let $modal = $(`#postModal-${postId}`);
                let $commentsSection = $modal.find('.comments-section');
                let $button = $(this);

                if ($commentsSection.is(':visible')) {
                    $commentsSection.hide();
                    $button.text('Show Comments');
                } else {
                    fetchComments(postId);
                    $commentsSection.show();
                    $button.text('Hide Comments');
                }
            });

            // Function to fetch and display comments
            function fetchComments(postId) {
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'get_comments', post_id: postId },
                    success: function(response) {
                        let $modal = $(`#postModal-${postId}`);
                        let $commentsList = $modal.find('.comments-list');
                        let html = '';
                        if (response.error) {
                            html = '<p>Error: ' + response.error + '</p>';
                        } else if (response.comments) {
                            if (response.comments.length === 0) {
                                html = '<p>No comments yet.</p>';
                            } else {
                                response.comments.forEach(comment => {
                                    html += `
                                        <div class="border-bottom py-2">
                                            <strong>${comment.username}</strong> <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                                            <p class="mb-0">${comment.comment_text}</p>
                                        </div>`;
                                });
                            }
                        } else {
                            html = '<p>Unexpected response format.</p>';
                        }
                        $commentsList.html(html);
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to fetch comments.';
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMsg = response.error;
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                        }
                        showToast(errorMsg);
                        console.error('Fetch comments error:', xhr.responseText);
                    }
                });
            }

            // Refresh posts after deletion
            window.addEventListener('storage', function(e) {
                if (e.key === 'postDeleted') {
                    $.ajax({
                        url: 'home.php',
                        method: 'GET',
                        success: function(response) {
                            $('#postsContainer').html($(response).find('#postsContainer').html());
                            // Re-fetch counts for newly loaded posts
                            $('#postsContainer .post-card').each(function() {
                                let postId = $(this).data('post-id');
                                $.ajax({
                                    url: '../ajax/user_actions.php',
                                    method: 'POST',
                                    data: { action: 'get_post_counts', post_id: postId },
                                    success: function(response) {
                                        if (response.counts) {
                                            let $card = $(`.post-card[data-post-id="${postId}"]`);
                                            let $modal = $(`#postModal-${postId}`);
                                            $card.find('.like-count').text(response.counts.like_count);
                                            $card.find('.favorite-count').text(response.counts.favorite_count);
                                            $card.find('.comment-count').text(response.counts.comment_count);
                                            $modal.find('.like-count').text(response.counts.like_count);
                                            $modal.find('.favorite-count').text(response.counts.favorite_count);
                                            $modal.find('.comment-count').text(response.counts.comment_count);
                                        }
                                    }
                                });
                            });
                        }
                    });
                }
            });

            // Toast notification function
            function showToast(message) {
                $('#actionToast .toast-body').text(message);
                new bootstrap.Toast($('#actionToast')).show();
            }
        });
    </script>
</body>
</html>