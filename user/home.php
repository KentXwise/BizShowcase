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

// Set current date and time (02:21 AM PST on Wednesday, May 28, 2025)
$currentDateTime = new DateTime('2025-05-28 02:21:00', new DateTimeZone('America/Los_Angeles'));

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Search Bar and Category Dropdown -->
        <div class="search-bar-section">
            <div class="search-bar-container">
                <div class="input-group search-bar" style="max-width: 500px; width: 100%;">
                    <input type="text" id="searchUser" class="form-control" placeholder="Search users..." aria-label="Search users">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <div class="category-dropdown" style="min-width: 150px;">
                    <select id="categoryFilter" class="form-select" aria-label="Filter by category">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Posts Section -->
        <div class="container posts-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Business Posts</h2>
                        <span class="text-muted small"><?php echo $currentDateTime->format('l, F j, Y g:i A T'); ?></span>
                    </div>
                    <div id="postsContainer" class="row">
                        <?php foreach ($posts as $post): ?>
                            <?php 
                            $counts = get_post_counts($conn, $post['post_id']);
                            $images = get_post_images($conn, $post['post_id']);
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
                            $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                            $has_liked = $stmt->fetchColumn() > 0;
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = ? AND user_id = ?");
                            $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                            $has_favorited = $stmt->fetchColumn() > 0;
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>">
                                    <?php if (!empty($images)): ?>
                                        <div class="post-images-container">
                                            <img src="../<?php echo htmlspecialchars($images[0]['image_path']); ?>" 
                                                 class="post-image" 
                                                 alt="Post image for <?php echo htmlspecialchars($post['company_name']); ?>" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#postModal-<?php echo $post['post_id']; ?>" 
                                                 loading="lazy">
                                        </div>
                                    <?php else: ?>
                                        <div class="post-images-container text-muted text-center py-3">No images available</div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($post['company_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($post['description'], 0, 100)) . (strlen($post['description']) > 100 ? '...' : ''); ?></p>
                                        <div class="post-stats">
                                            <span><i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $counts['like_count']; ?></span></span>
                                            <span><i class="fas fa-star"></i> <span class="favorite-count"><?php echo $counts['favorite_count']; ?></span></span>
                                            <span><i class="fas fa-comment"></i> <span class="comment-count"><?php echo $counts['comment_count']; ?></span></span>
                                        </div>
                                        <div class="d-flex gap-2 mb-3">
                                            <button class="btn btn-outline-primary action-btn like-btn" 
                                                    data-post-id="<?php echo $post['post_id']; ?>" 
                                                    <?php echo $has_liked ? 'disabled' : ''; ?>
                                                    aria-label="<?php echo $has_liked ? 'Liked' : 'Like'; ?> post">
                                                <i class="fas fa-thumbs-up"></i> <?php echo $has_liked ? 'Liked' : 'Like'; ?>
                                            </button>
                                            <button class="btn btn-outline-success action-btn favorite-btn" 
                                                    data-post-id="<?php echo $post['post_id']; ?>" 
                                                    <?php echo $has_favorited ? 'disabled' : ''; ?>
                                                    aria-label="<?php echo $has_favorited ? 'Favorited' : 'Favorite'; ?> post">
                                                <i class="fas fa-star"></i> <?php echo $has_favorited ? 'Favorited' : 'Favorite'; ?>
                                            </button>
                                            <button class="btn btn-outline-info action-btn follow-btn" 
                                                    data-user-id="<?php echo $post['user_id']; ?>"
                                                    aria-label="Follow user">
                                                <i class="fas fa-user-plus"></i> Follow
                                            </button>
                                        </div>
                                        <div>
                                            <textarea class="form-control comment-text" 
                                                      placeholder="Add a comment..." 
                                                      aria-label="Comment input for post <?php echo $post['post_id']; ?>"></textarea>
                                            <button class="btn comment-btn mt-2 comment-btn" 
                                                    data-post-id="<?php echo $post['post_id']; ?>"
                                                    aria-label="Submit comment">
                                                Comment
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Post Modal -->
                                <div class="modal fade" id="postModal-<?php echo $post['post_id']; ?>" tabindex="-1" aria-labelledby="postModalLabel-<?php echo $post['post_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="postModalLabel-<?php echo $post['post_id']; ?>">
                                                    <?php echo htmlspecialchars($post['company_name']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-0">
                                                    <div class="col-md-8">
                                                        <?php if (!empty($images)): ?>
                                                            <div id="carousel-<?php echo $post['post_id']; ?>" class="carousel slide">
                                                                <div class="carousel-inner">
                                                                    <?php foreach ($images as $index => $image): ?>
                                                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                                                 class="d-block w-100" 
                                                                                 alt="Image <?php echo $index + 1; ?> for <?php echo htmlspecialchars($post['company_name']); ?>" 
                                                                                 loading="lazy">
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <?php if (count($images) > 1): ?>
                                                                    <button class="carousel-control-prev" type="button" 
                                                                            data-bs-target="#carousel-<?php echo $post['post_id']; ?>" 
                                                                            data-bs-slide="prev" 
                                                                            aria-label="Previous image">
                                                                        <span class="carousel-control-prev-icon"></span>
                                                                    </button>
                                                                    <button class="carousel-control-next" type="button" 
                                                                            data-bs-target="#carousel-<?php echo $post['post_id']; ?>" 
                                                                            data-bs-slide="next" 
                                                                            aria-label="Next image">
                                                                        <span class="carousel-control-next-icon"></span>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-muted">No images available.</p>
                                                        <?php endif; ?>
                                                        <div class="modal-post-content">
                                                            <p><?php echo htmlspecialchars($post['description']); ?></p>
                                                            <div class="modal-post-stats">
                                                                <span><i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $counts['like_count']; ?></span></span>
                                                                <span><i class="fas fa-star"></i> <span class="favorite-count"><?php echo $counts['favorite_count']; ?></span></span>
                                                                <span><i class="fas fa-comment"></i> <span class="comment-count"><?php echo $counts['comment_count']; ?></span></span>
                                                            </div>
                                                            <div class="d-flex gap-2 mb-3">
                                                                <button class="btn btn-outline-primary action-btn like-btn" 
                                                                        data-post-id="<?php echo $post['post_id']; ?>" 
                                                                        <?php echo $has_liked ? 'disabled' : ''; ?>
                                                                        aria-label="<?php echo $has_liked ? 'Liked' : 'Like'; ?> post">
                                                                    <i class="fas fa-thumbs-up"></i> <?php echo $has_liked ? 'Liked' : 'Like'; ?>
                                                                </button>
                                                                <button class="btn btn-outline-success action-btn favorite-btn" 
                                                                        data-post-id="<?php echo $post['post_id']; ?>" 
                                                                        <?php echo $has_favorited ? 'disabled' : ''; ?>
                                                                        aria-label="<?php echo $has_favorited ? 'Favorited' : 'Favorite'; ?> post">
                                                                    <i class="fas fa-star"></i> <?php echo $has_favorited ? 'Favorited' : 'Favorite'; ?>
                                                                </button>
                                                                <button class="btn btn-outline-info action-btn follow-btn" 
                                                                        data-user-id="<?php echo $post['user_id']; ?>"
                                                                        aria-label="Follow user">
                                                                    <i class="fas fa-user-plus"></i> Follow
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="comments-panel">
                                                            <button class="btn btn-outline-primary show-comments-btn" 
                                                                    data-post-id="<?php echo $post['post_id']; ?>"
                                                                    aria-expanded="false"
                                                                    aria-controls="comments-section-<?php echo $post['post_id']; ?>">
                                                                Show Comments
                                                            </button>
                                                            <div class="comments-section" id="comments-section-<?php echo $post['post_id']; ?>" style="display: none;">
                                                                <h6>Comments</h6>
                                                                <div class="comments-list"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close modal">Close</button>
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
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body"></div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Function to show loading state
            function showLoading($element, show) {
                if (show) {
                    $element.prop('disabled', true).append('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
                } else {
                    $element.prop('disabled', false).find('.spinner-border').remove();
                }
            }

            // Function to render posts from JSON data
            function renderPosts(posts) {
                let html = '';
                posts.forEach(post => {
                    const imagesHtml = post.images && post.images.length > 0 ? post.images.map((image, index) => `
                        <div class="carousel-item ${index === 0 ? 'active' : ''}">
                            <img src="../${image.image_path}" class="d-block w-100" alt="Image ${index + 1} for ${post.company_name}" loading="lazy">
                        </div>
                    `).join('') : '';

                    const carouselControls = post.images && post.images.length > 1 ? `
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${post.post_id}" data-bs-slide="prev" aria-label="Previous image">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-${post.post_id}" data-bs-slide="next" aria-label="Next image">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    ` : '';

                    html += `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card post-card" data-post-id="${post.post_id}">
                                <div class="post-images-container">
                                    ${post.images && post.images.length > 0 ? 
                                        `<img src="../${post.images[0].image_path}" class="post-image" alt="Post image for ${post.company_name}" data-bs-toggle="modal" data-bs-target="#postModal-${post.post_id}" loading="lazy">` : 
                                        `<div class="post-images-container text-muted text-center py-3">No images available</div>`
                                    }
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">${post.company_name}</h5>
                                    <p class="card-text">${post.description.substring(0, 100)}${post.description.length > 100 ? '...' : ''}</p>
                                    <div class="post-stats">
                                        <span><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span></span>
                                        <span><i class="fas fa-star"></i> <span class="favorite-count">0</span></span>
                                        <span><i class="fas fa-comment"></i> <span class="comment-count">0</span></span>
                                    </div>
                                    <div class="d-flex gap-2 mb-3">
                                        <button class="btn btn-outline-primary action-btn like-btn" 
                                                data-post-id="${post.post_id}" 
                                                ${post.has_liked ? 'disabled' : ''} 
                                                aria-label="${post.has_liked ? 'Liked' : 'Like'} post">
                                            <i class="fas fa-thumbs-up"></i> ${post.has_liked ? 'Liked' : 'Like'}
                                        </button>
                                        <button class="btn btn-outline-success action-btn favorite-btn" 
                                                data-post-id="${post.post_id}" 
                                                ${post.has_favorited ? 'disabled' : ''} 
                                                aria-label="${post.has_favorited ? 'Favorited' : 'Favorite'} post">
                                            <i class="fas fa-star"></i> ${post.has_favorited ? 'Favorited' : 'Favorite'}
                                        </button>
                                        <button class="btn btn-outline-info action-btn follow-btn" 
                                                data-user-id="${post.user_id}" 
                                                aria-label="Follow user">
                                            <i class="fas fa-user-plus"></i> Follow
                                        </button>
                                    </div>
                                    <div>
                                        <textarea class="form-control comment-text" 
                                                  placeholder="Add a comment..." 
                                                  aria-label="Comment input for post ${post.post_id}"></textarea>
                                        <button class="btn comment-btn mt-2 comment-btn" 
                                                data-post-id="${post.post_id}" 
                                                aria-label="Submit comment">Comment</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="postModal-${post.post_id}" tabindex="-1" aria-labelledby="postModalLabel-${post.post_id}" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="postModalLabel-${post.post_id}">${post.company_name}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-0">
                                                <div class="col-md-8">
                                                    ${post.images && post.images.length > 0 ? `
                                                        <div id="carousel-${post.post_id}" class="carousel slide">
                                                            <div class="carousel-inner">
                                                                ${imagesHtml}
                                                            </div>
                                                            ${carouselControls}
                                                        </div>
                                                    ` : `<p class="text-muted">No images available.</p>`}
                                                    <div class="modal-post-content">
                                                        <p>${post.description}</p>
                                                        <div class="modal-post-stats">
                                                            <span><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span></span>
                                                            <span><i class="fas fa-star"></i> <span class="favorite-count">0</span></span>
                                                            <span><i class="fas fa-comment"></i> <span class="comment-count">0</span></span>
                                                        </div>
                                                        <div class="d-flex gap-2 mb-3">
                                                            <button class="btn btn-outline-primary action-btn like-btn" 
                                                                    data-post-id="${post.post_id}" 
                                                                    ${post.has_liked ? 'disabled' : ''} 
                                                                    aria-label="${post.has_liked ? 'Liked' : 'Like'} post">
                                                                <i class="fas fa-thumbs-up"></i> ${post.has_liked ? 'Liked' : 'Like'}
                                                            </button>
                                                            <button class="btn btn-outline-success action-btn favorite-btn" 
                                                                    data-post-id="${post.post_id}" 
                                                                    ${post.has_favorited ? 'disabled' : ''} 
                                                                    aria-label="${post.has_favorited ? 'Favorited' : 'Favorite'} post">
                                                                <i class="fas fa-star"></i> ${post.has_favorited ? 'Favorited' : 'Favorite'}
                                                            </button>
                                                            <button class="btn btn-outline-info action-btn follow-btn" 
                                                                    data-user-id="${post.user_id}" 
                                                                    aria-label="Follow user">
                                                                <i class="fas fa-user-plus"></i> Follow
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="comments-panel">
                                                        <button class="btn btn-outline-primary show-comments-btn" 
                                                                data-post-id="${post.post_id}"
                                                                aria-expanded="false"
                                                                aria-controls="comments-section-${post.post_id}">
                                                            Show Comments
                                                        </button>
                                                        <div class="comments-section" id="comments-section-${post.post_id}" style="display: none;">
                                                            <h6>Comments</h6>
                                                            <div class="comments-list"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                $('#postsContainer').html(html);

                // Re-initialize Bootstrap modals after dynamic content is added
                $('[data-bs-toggle="modal"]').each(function() {
                    $(this).off('click').on('click', function() {
                        const target = $(this).data('bs-target');
                        $(target).modal('show');
                    });
                });
            }

            // Fetch initial counts for existing posts
            function fetchPostCounts() {
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
                                $modal.find('.modal-post-stats .like-count').text(response.counts.like_count);
                                $modal.find('.modal-post-stats .favorite-count').text(response.counts.favorite_count);
                                $modal.find('.modal-post-stats .comment-count').text(response.counts.comment_count);
                            }
                        },
                        error: function(xhr) {
                            showToast('Failed to fetch post counts.');
                        }
                    });
                });
            }
            fetchPostCounts();

            // Filter posts by category
            $('#categoryFilter').change(function() {
                let categoryId = $(this).val();
                showLoading($(this), true);
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'filter_posts', category_id: categoryId },
                    success: function(response) {
                        if (response.posts) {
                            renderPosts(response.posts);
                            fetchPostCounts();
                        }
                    },
                    error: function(xhr) {
                        showToast('Failed to filter posts.');
                    },
                    complete: function() {
                        showLoading($('#categoryFilter'), false);
                    }
                });
            });

            // Search users
            $('#searchUser').on('input', function() {
                let searchTerm = $(this).val();
                showLoading($(this), true);
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'search_users', search_term: searchTerm },
                    success: function(response) {
                        if (response.posts) {
                            renderPosts(response.posts);
                            fetchPostCounts();
                        }
                    },
                    error: function(xhr) {
                        showToast('Failed to search users.');
                    },
                    complete: function() {
                        showLoading($('#searchUser'), false);
                    }
                });
            });

            // Like post
            $(document).on('click', '.like-btn', function() {
                let $button = $(this);
                let postId = $button.data('post-id');
                showLoading($button, true);
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
                            $modal.find('.modal-post-stats .like-count').text(currentLikes + 1);
                            $card.find('.like-btn').prop('disabled', true).html('<i class="fas fa-thumbs-up"></i> Liked');
                            $modal.find('.like-btn').prop('disabled', true).html('<i class="fas fa-thumbs-up"></i> Liked');
                        }
                    },
                    error: function(xhr) {
                        let response = JSON.parse(xhr.responseText);
                        showToast(response.error || 'Failed to like post.');
                    },
                    complete: function() {
                        showLoading($button, false);
                    }
                });
            });

            // Favorite post
            $(document).on('click', '.favorite-btn', function() {
                let $button = $(this);
                let postId = $button.data('post-id');
                showLoading($button, true);
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
                            $modal.find('.modal-post-stats .favorite-count').text(currentFavorites + 1);
                            $card.find('.favorite-btn').prop('disabled', true).html('<i class="fas fa-star"></i> Favorited');
                            $modal.find('.favorite-btn').prop('disabled', true).html('<i class="fas fa-star"></i> Favorited');
                        }
                    },
                    error: function(xhr) {
                        let response = JSON.parse(xhr.responseText);
                        showToast(response.error || 'Failed to favorite post.');
                    },
                    complete: function() {
                        showLoading($button, false);
                    }
                });
            });

            // Follow user
            $(document).on('click', '.follow-btn', function() {
                let $button = $(this);
                let userId = $button.data('user-id');
                showLoading($button, true);
                $.ajax({
                    url: '../ajax/user_actions.php',
                    method: 'POST',
                    data: { action: 'follow_user', followed_id: userId },
                    success: function(response) {
                        if (response.success) {
                            let $card = $(`.post-card .follow-btn[data-user-id="${userId}"]`);
                            let $modal = $(`#postModal-${post.post_id} .follow-btn[data-user-id="${userId}"]`);
                            $card.prop('disabled', true).html('<i class="fas fa-check"></i> Following');
                            $modal.prop('disabled', true).html('<i class="fas fa-check"></i> Following');
                        }
                    },
                    error: function(xhr) {
                        showToast('Failed to follow user.');
                    },
                    complete: function() {
                        showLoading($button, false);
                    }
                });
            });

            // Comment on post
            $(document).on('click', '.comment-btn', function() {
                let $button = $(this);
                let postId = $button.data('post-id');
                let commentText = $button.prev('.comment-text').val();
                if (commentText.trim() === '') {
                    showToast('Please enter a comment.');
                    return;
                }
                showLoading($button, true);
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
                            $modal.find('.modal-post-stats .comment-count').text(currentComments + 1);
                            $card.find('.comment-text').val('');
                            if ($modal.find('.comments-section').is(':visible')) {
                                fetchComments(postId);
                            }
                        }
                    },
                    error: function(xhr) {
                        showToast('Failed to add comment.');
                    },
                    complete: function() {
                        showLoading($button, false);
                    }
                });
            });

            // Show comments in modal
            $(document).on('click', '.show-comments-btn', function() {
                let $button = $(this);
                let postId = $button.data('post-id');
                let $modal = $(`#postModal-${postId}`);
                let $commentsSection = $modal.find('.comments-section');
                showLoading($button, true);
                if ($commentsSection.is(':visible')) {
                    $commentsSection.hide();
                    $button.text('Show Comments').attr('aria-expanded', 'false');
                    showLoading($button, false);
                } else {
                    fetchComments(postId);
                    $commentsSection.show();
                    $button.text('Hide Comments').attr('aria-expanded', 'true');
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
                            html = `<p class="text-muted">${response.error}</p>`;
                        } else if (response.comments) {
                            if (response.comments.length === 0) {
                                html = '<p class="text-muted">No comments yet.</p>';
                            } else {
                                response.comments.forEach(comment => {
                                    html += `
                                        <div class="comment-item">
                                            <strong>${comment.username}</strong> 
                                            <small>${new Date(comment.created_at).toLocaleString()}</small>
                                            <p>${comment.comment_text}</p>
                                        </div>`;
                                });
                            }
                        } else {
                            html = '<p class="text-muted">Unexpected response format.</p>';
                        }
                        $commentsList.html(html);
                    },
                    error: function(xhr) {
                        showToast('Failed to fetch comments.');
                    },
                    complete: function() {
                        showLoading($(`#postModal-${postId} .show-comments-btn`), false);
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
                            fetchPostCounts();
                        },
                        error: function() {
                            showToast('Failed to refresh posts.');
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