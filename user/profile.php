<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data with error handling
$user = get_user_info($conn, $_SESSION['user_id']) ?: ['first_name' => '', 'last_name' => '', 'email' => '', 'profile_picture' => ''];
$business = get_business_profile($conn, $_SESSION['user_id']) ?: ['company_name' => '', 'business_email' => '', 'business_address' => '', 'business_number' => '', 'seller_type' => ''];
$posts = get_user_posts($conn, $_SESSION['user_id']) ?: [];
$favorites = get_user_favorites($conn, $_SESSION['user_id']) ?: [];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && !empty($_FILES['profile_picture']['name'])) {
    $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="css/profile.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="page-wrapper">
    <div class="main-content">
        <div class="container my-4 profile-container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-start mb-4 flex-column flex-md-row">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: '../assets/img/default-profile.png'); ?>" alt="Profile" class="me-md-3 mb-3 mb-md-0 profile-img">
                        <div class="flex-grow-1">
                            <h2 class="font-prompt fw-medium profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                            <form method="POST" enctype="multipart/form-data" class="mt-2">
                                <div class="mb-3">
                                    <label class="form-label font-roboto">Update Profile Picture</label>
                                    <input type="file" class="form-control font-poppins" name="profile_picture" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-blue-2 font-inter fw-bold rounded">Upload</button>
                            </form>
                        </div>
                        <button class="btn btn-dark ms-md-auto font-inter fw-bold rounded edit-profile-btn" onclick="document.location='settings.php'">Edit Profile</button>
                    </div>
                    <div class="row mb-4 profile-details">
                        <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Email:</strong>
                                <a href="mailto:<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="text-blue-1 font-poppins detail-value text-decoration-underline"><?php echo htmlspecialchars($user['email'] ?? ''); ?></a>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Company:</strong>
                                <span class="text-blue-1 font-poppins detail-value"><?php echo htmlspecialchars($business['company_name'] ?? ''); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Email:</strong>
                                <a href="mailto:<?php echo htmlspecialchars($business['business_email'] ?? ''); ?>" class="text-blue-1 font-poppins detail-value text-decoration-underline"><?php echo htmlspecialchars($business['business_email'] ?? ''); ?></a>
                            </div>
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Address:</strong>
                                <span class="text-blue-1 font-poppins detail-value"><?php echo htmlspecialchars($business['business_address'] ?? ''); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Number:</strong>
                                <span class="text-blue-1 font-poppins detail-value"><?php echo htmlspecialchars($business['business_number'] ?? ''); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong class="font-roboto detail-label">Seller Type:</strong>
                                <span class="text-blue-1 font-poppins detail-value"><?php echo htmlspecialchars($business['seller_type'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="posts-tab" href="#posts" role="tab" aria-controls="posts" aria-selected="true">My Posts</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="favorites-tab" href="#favorites" role="tab" aria-controls="favorites" aria-selected="false">Favorited Posts</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="profileTabContent">
                        <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                            <div class="row">
                                <?php foreach ($posts as $post): ?>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="post-card p-3 p-md-4">
                                            <?php
                                            $images = get_post_images($conn, $post['post_id']) ?: [];
                                            if (!empty($images)):
                                                $image = $images[0]; // Use first image
                                            ?>
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Post Image" class="mb-3 mx-auto d-block post-img">
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: '../assets/img/default-profile.png'); ?>" alt="User" class="rounded-circle me-2 user-img">
                                                    <div>
                                                        <div class="font-roboto fw-semibold user-name"><?php echo htmlspecialchars($post['company_name'] ?? ''); ?></div>
                                                        <div class="font-poppins opacity-50 post-date"><?php echo htmlspecialchars($post['created_at'] ?? ''); ?></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button class="like-button me-2 border-0 bg-transparent" aria-label="Like post">
                                                        <i class="fa-regular fa-heart"></i>
                                                    </button>
                                                    <div class="bg-fuchsia-950 indicator"></div>
                                                </div>
                                            </div>
                                            <h3 class="font-roboto fw-medium mt-2 post-title"><?php echo htmlspecialchars($post['company_name'] ?? ''); ?></h3>
                                            <div class="font-poppins post-description"><?php echo htmlspecialchars($post['description'] ?? ''); ?></div>
                                            <button class="btn btn-sm btn-danger delete-post-btn mt-2 font-inter fw-bold rounded" data-post-id="<?php echo $post['post_id']; ?>">Delete</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="favorites" role="tabpanel" aria-labelledby="favorites-tab">
                            <div class="row">
                                <?php foreach ($favorites as $post): ?>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="post-card p-3 p-md-4">
                                            <?php
                                            $images = get_post_images($conn, $post['post_id']) ?: [];
                                            if (!empty($images)):
                                                $image = $images[0]; // Use first image
                                            ?>
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Favorite Item" class="mb-3 mx-auto d-block post-img">
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: '../assets/img/default-profile.png'); ?>" alt="User" class="rounded-circle me-2 user-img">
                                                    <div>
                                                        <div class="font-roboto fw-semibold user-name"><?php echo htmlspecialchars($post['company_name'] ?? ''); ?></div>
                                                        <div class="font-poppins opacity-50 post-date"><?php echo htmlspecialchars($post['created_at'] ?? ''); ?></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button class="like-button me-2 border-0 bg-transparent" aria-label="Like favorite">
                                                        <i class="fa-regular fa-heart"></i>
                                                    </button>
                                                    <div class="bg-fuchsia-950 indicator"></div>
                                                </div>
                                            </div>
                                            <h3 class="font-roboto fw-medium mt-2 post-title"><?php echo htmlspecialchars($post['company_name'] ?? ''); ?></h3>
                                            <div class="font-poppins post-description"><?php echo htmlspecialchars($post['description'] ?? ''); ?></div>
                                            <button class="btn btn-sm btn-blue-2 mt-2 font-inter fw-bold rounded">View Item</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && mainContent) {
        sidebar.classList.toggle('show');
        sidebar.classList.toggle('collapsed');
        
        if (sidebar.classList.contains('show')) {
            overlay.style.display = window.innerWidth <= 991 ? 'block' : 'none';
            localStorage.setItem('sidebarCollapsed', 'false');
            if (window.innerWidth > 991) {
                mainContent.style.marginLeft = '250px';
            }
        } else {
            overlay.style.display = 'none';
            localStorage.setItem('sidebarCollapsed', 'true');
            if (window.innerWidth > 991) {
                mainContent.style.marginLeft = '80px';
            }
        }
    }
}
// Tab switching function
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#profileTabs .nav-link');
    const tabContents = document.querySelectorAll('#profileTabContent .tab-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('show', 'active'));
            this.classList.add('active');
            const targetContent = document.querySelector(this.getAttribute('href'));
            if (targetContent) {
                targetContent.classList.add('show', 'active');
            }
        });
    });
    // Like button toggle
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function () {
            this.classList.toggle('liked');
        });
    });
});
// Initialize sidebar state on load
window.addEventListener('load', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (sidebar && mainContent && window.innerWidth > 991) {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('show');
            mainContent.style.marginLeft = '80px';
        } else {
            sidebar.classList.add('show');
            sidebar.classList.remove('collapsed');
            mainContent.style.marginLeft = '250px';
        }
    }
});
// Adjust main-content margin on window resize
window.addEventListener('resize', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && mainContent) {
        if (window.innerWidth <= 991) {
            if (!sidebar.classList.contains('show')) {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
                mainContent.style.marginLeft = '0';
            }
        } else {
            if (sidebar.classList.contains('show')) {
                mainContent.style.marginLeft = '250px';
            } else {
                mainContent.style.marginLeft = '80px';
            }
        }
    }
});
// Delete post functionality
$(document).ready(function() {
    $('.delete-post-btn').on('click', function() {
        if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
            let postId = $(this).data('post-id');
            $.ajax({
                url: '../ajax/user_actions.php',
                method: 'POST',
                data: { action: 'delete_post', post_id: postId },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response && response.success) {
                        alert('Post deleted successfully!');
                        window.location.href = 'profile.php';
                    } else {
                        alert('Error deleting post: ' + (response.error || 'Unknown error'));
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>