<?php
session_start();
require_once '../user/includes/db_connect.php';
require_once '../user/includes/functions.php';

header('Content-Type: application/json'); // Ensure JSON response

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'filter_posts':
        $category_id = $_POST['category_id'] ?? '';
        $posts = $category_id ? get_posts_by_category($conn, $category_id) : get_all_posts($conn);
        foreach ($posts as $post) {
            echo '<div class="card post-card" data-post-id="' . $post['post_id'] . '">';
            echo '<div class="card-body">';
            echo '<h5>' . htmlspecialchars($post['company_name']) . '</h5>';
            echo '<p>' . htmlspecialchars($post['description']) . '</p>';
            echo '<div class="post-images">';
            $images = get_post_images($conn, $post['post_id']);
            foreach ($images as $image) {
                echo '<img src="' . $image['image_path'] . '" class="post-image" alt="Post Image">';
            }
            echo '</div>';
            echo '<button class="btn btn-sm btn-primary like-btn" data-post-id="' . $post['post_id'] . '">Like</button>';
            echo '<button class="btn btn-sm btn-success favorite-btn" data-post-id="' . $post['post_id'] . '">Favorite</button>';
            echo '<button class="btn btn-sm btn-info follow-btn" data-user-id="' . $post['user_id'] . '">Follow</button>';
            echo '<div class="mt-2">';
            echo '<textarea class="form-control comment-text" placeholder="Add a comment..."></textarea>';
            echo '<button class="btn btn-sm btn-primary mt-1 comment-btn" data-post-id="' . $post['post_id'] . '">Comment</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        break;

    case 'search_users':
        $search_term = $_POST['search_term'] ?? '';
        $users = search_users($conn, $search_term);
        foreach ($users as $user) {
            $posts = get_user_posts($conn, $user['user_id']);
            foreach ($posts as $post) {
                echo '<div class="card post-card" data-post-id="' . $post['post_id'] . '">';
                echo '<div class="card-body">';
                echo '<h5>' . htmlspecialchars($post['company_name']) . '</h5>';
                echo '<p>' . htmlspecialchars($post['description']) . '</p>';
                echo '<div class="post-images">';
                $images = get_post_images($conn, $post['post_id']);
                foreach ($images as $image) {
                    echo '<img src="' . $image['image_path'] . '" class="post-image" alt="Post Image">';
                }
                echo '</div>';
                echo '<button class="btn btn-sm btn-primary like-btn" data-post-id="' . $post['post_id'] . '">Like</button>';
                echo '<button class="btn btn-sm btn-success favorite-btn" data-post-id="' . $post['post_id'] . '">Favorite</button>';
                echo '<button class="btn btn-sm btn-info follow-btn" data-user-id="' . $post['user_id'] . '">Follow</button>';
                echo '<div class="mt-2">';
                echo '<textarea class="form-control comment-text" placeholder="Add a comment..."></textarea>';
                echo '<button class="btn btn-sm btn-primary mt-1 comment-btn" data-post-id="' . $post['post_id'] . '">Comment</button>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        break;

    case 'like_post':
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id) {
            $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid post ID']);
        }
        break;

    case 'favorite_post':
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id) {
            $stmt = $conn->prepare("INSERT INTO favorites (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid post ID']);
        }
        break;

    case 'comment_post':
        $post_id = $_POST['post_id'] ?? 0;
        $comment_text = $_POST['comment_text'] ?? '';
        if ($post_id && $comment_text) {
            $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $_SESSION['user_id'], $comment_text]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    case 'follow_user':
        $followed_id = $_POST['followed_id'] ?? 0;
        if ($followed_id) {
            $stmt = $conn->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $followed_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid user ID']);
        }
        break;

    case 'add_post':
        $company_name = htmlspecialchars($_POST['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $category_id = $_POST['category_id'];
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_email = htmlspecialchars($_POST['business_email'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_address = htmlspecialchars($_POST['business_address'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_number = htmlspecialchars($_POST['business_number'] ?? '', ENT_QUOTES, 'UTF-8');
        $seller_type = $_POST['seller_type'];
    
        $stmt = $conn->prepare("INSERT INTO posts (user_id, company_name, category_id, description, business_email, business_address, business_number, seller_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $company_name, $category_id, $description, $business_email, $business_address, $business_number, $seller_type]);
        $post_id = $conn->lastInsertId();
    
        $upload_dir = '../assets/images/';
        if (!file_exists($upload_dir) && !mkdir($upload_dir, 0775, true)) {
            echo json_encode(['error' => 'Failed to create images directory.']);
            exit;
        }
        if (!is_writable($upload_dir)) {
            echo json_encode(['error' => 'Images directory is not writable.']);
            exit;
        }
    
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$post_id, 'assets/images/' . $file_name]); // Store relative path
                }
            }
        }
    
        echo json_encode(['success' => 'Post created successfully!']);
        exit;
    
    case 'delete_post':
        $post_id = $_POST['post_id'] ?? 0;
        $response = ['success' => false];
        if ($post_id) {
            try {
                $stmt = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
                $stmt->execute([$post_id]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($post && $post['user_id'] == $_SESSION['user_id']) {
                    $conn->beginTransaction();
                    $stmt = $conn->prepare("DELETE FROM post_images WHERE post_id = ?");
                    $stmt->execute([$post_id]);
                    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
                    $stmt->execute([$post_id]);
                    $conn->commit();
                    $response['success'] = true;
                } else {
                    $response['error'] = 'You are not authorized to delete this post.';
                }
            } catch (PDOException $e) {
                $conn->rollBack();
                $response['error'] = 'Database error: ' . $e->getMessage();
                error_log('Delete post error for post_id ' . $post_id . ': ' . $e->getMessage()); // Log to server
            }
        } else {
            $response['error'] = 'Invalid post ID.';
        }
        echo json_encode($response);
        exit;
    
    case 'initiate_payment':
        $subscription_id = $_POST['subscription_id'] ?? 0;
        if ($subscription_id) {
            $stmt = $conn->prepare("SELECT amount FROM subscriptions WHERE subscription_id = ? AND user_id = ?");
            $stmt->execute([$subscription_id, $_SESSION['user_id']]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($subscription) {
                $stmt = $conn->prepare("INSERT INTO payments (subscription_id, user_id, amount, payment_status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$subscription_id, $_SESSION['user_id'], $subscription['amount']]);
                echo json_encode(['success' => 'Payment initiated successfully']);
            } else {
                echo json_encode(['error' => 'Invalid subscription']);
            }
        }
        break;

    case 'check_notifications':
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        echo json_encode(['notifications' => $notifications]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}
?>