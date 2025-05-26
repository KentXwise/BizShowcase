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
        $posts_data = [];
        foreach ($posts as $post) {
            $images = get_post_images($conn, $post['post_id']);
            // Check if the user has liked this post
            $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
            $has_liked = $stmt->fetchColumn() > 0;
            // Check if the user has favorited this post
            $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
            $has_favorited = $stmt->fetchColumn() > 0;
            $posts_data[] = [
                'post_id' => $post['post_id'],
                'company_name' => htmlspecialchars($post['company_name']),
                'description' => htmlspecialchars($post['description']),
                'user_id' => $post['user_id'],
                'images' => $images,
                'has_liked' => $has_liked,
                'has_favorited' => $has_favorited
            ];
        }
        echo json_encode(['posts' => $posts_data]);
        break;

    case 'search_users':
        $search_term = $_POST['search_term'] ?? '';
        $users = search_users($conn, $search_term);
        $posts_data = [];
        foreach ($users as $user) {
            $posts = get_user_posts($conn, $user['user_id']);
            foreach ($posts as $post) {
                $images = get_post_images($conn, $post['post_id']);
                // Check if the user has liked this post
                $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                $has_liked = $stmt->fetchColumn() > 0;
                // Check if the user has favorited this post
                $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$post['post_id'], $_SESSION['user_id']]);
                $has_favorited = $stmt->fetchColumn() > 0;
                $posts_data[] = [
                    'post_id' => $post['post_id'],
                    'company_name' => htmlspecialchars($post['company_name']),
                    'description' => htmlspecialchars($post['description']),
                    'user_id' => $post['user_id'],
                    'images' => $images,
                    'has_liked' => $has_liked,
                    'has_favorited' => $has_favorited
                ];
            }
        }
        echo json_encode(['posts' => $posts_data]);
        break;

    case 'like_post':
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'You have already liked this post']);
            } else {
                $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
                $stmt->execute([$post_id, $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            }
        } else {
            echo json_encode(['error' => 'Invalid post ID']);
        }
        break;

    case 'favorite_post':
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'You have already favorited this post']);
            } else {
                $stmt = $conn->prepare("INSERT INTO favorites (post_id, user_id) VALUES (?, ?)");
                $stmt->execute([$post_id, $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            }
        } else {
            echo json_encode(['error' => 'Invalid post ID']);
        }
        break;

    case 'comment_post':
        $post_id = $_POST['post_id'] ?? 0;
        $comment_text = $_POST['comment_text'] ?? '';
        if ($post_id && $comment_text) {
            try {
                $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$post_id, $_SESSION['user_id'], $comment_text]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                error_log("Comment post error for post_id $post_id at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
                echo json_encode(['error' => 'Failed to add comment: ' . $e->getMessage()]);
            }
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
        $user_id = $_SESSION['user_id'];
        $category_id = $_POST['category_id'] ?? 0;
        $company_name = htmlspecialchars($_POST['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_email = htmlspecialchars($_POST['business_email'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_address = htmlspecialchars($_POST['business_address'] ?? '', ENT_QUOTES, 'UTF-8');
        $business_number = htmlspecialchars($_POST['business_number'] ?? '', ENT_QUOTES, 'UTF-8');
        $seller_type = $_POST['seller_type'] ?? '';

        // Validate inputs
        if (!$category_id || !$company_name || !$description || !$business_email || !$business_address || !$business_number || !$seller_type) {
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }

        // Validate category_id
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ? AND status = 'approved'");
        $stmt->execute([$category_id]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['error' => 'Invalid or unapproved category']);
            exit;
        }

        // Call stored procedure to create post
        try {
            $stmt = $conn->prepare("CALL sp_add_post(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $category_id,
                $company_name,
                $description,
                $business_email,
                $business_address,
                $business_number,
                $seller_type
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $post_id = $result['post_id'] ?? 0;

            if (!$post_id) {
                echo json_encode(['error' => 'Failed to create post']);
                exit;
            }

            // Handle image uploads
            $upload_dir = '../assets/images/';
            if (!file_exists($upload_dir) && !mkdir($upload_dir, 0775, true)) {
                echo json_encode(['error' => 'Failed to create images directory']);
                exit;
            }
            if (!is_writable($upload_dir)) {
                echo json_encode(['error' => 'Images directory is not writable']);
                exit;
            }

            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                    $target_file = $upload_dir . $file_name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $stmt = $conn->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                        $stmt->execute([$post_id, 'assets/images/' . $file_name]);
                    }
                }
            }

            echo json_encode(['success' => 'Post created successfully!', 'post_id' => $post_id]);
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
            error_log("sp_add_post error for user $user_id: $error_message");
            echo json_encode(['error' => 'Failed to create post: ' . $error_message]);
        }
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

    case 'get_comments':
        $post_id = $_POST['post_id'] ?? 0;
         if ($post_id) {
        try {
            // Verify post_id exists in posts table
            $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
            $stmt->execute([$post_id]);
            $post_exists = $stmt->fetchColumn() > 0;
            if (!$post_exists) {
                echo json_encode(['error' => 'Post ID does not exist']);
                break;
            }

            // Fetch comments using first_name and last_name
            $stmt = $conn->prepare("SELECT c.comment_text, c.created_at, CONCAT(u.first_name, ' ', u.last_name) AS username 
                                    FROM comments c 
                                    JOIN users u ON c.user_id = u.user_id 
                                    WHERE c.post_id = ? 
                                    ORDER BY c.created_at DESC");
            $stmt->execute([$post_id]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['comments' => $comments]);
        } catch (PDOException $e) {
            error_log("Get comments error for post_id $post_id at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
            echo json_encode(['error' => 'Failed to fetch comments: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid post ID']);
    }
    break;

    case 'get_post_counts':
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id) {
            $stmt = $conn->prepare("SELECT 
                (SELECT COUNT(*) FROM likes WHERE post_id = ?) AS like_count,
                (SELECT COUNT(*) FROM favorites WHERE post_id = ?) AS favorite_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = ?) AS comment_count");
            $stmt->execute([$post_id, $post_id, $post_id]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['counts' => $counts]);
        } else {
            echo json_encode(['error' => 'Invalid post ID']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}
?>