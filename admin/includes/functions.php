<?php
function get_categories($conn) {
    $stmt = $conn->query("SELECT * FROM categories WHERE status = 'approved'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_posts($conn) {
    $stmt = $conn->query("SELECT p.*, u.first_name, u.last_name FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_posts_by_category($conn, $category_id) {
    $stmt = $conn->prepare("SELECT p.*, u.first_name, u.last_name FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.category_id = ? ORDER BY p.created_at DESC");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_post_images($conn, $post_id) {
    $stmt = $conn->prepare("SELECT * FROM post_images WHERE post_id = ?");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_business_profile($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM business_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function search_users($conn, $search_term) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?");
    $search_term = "%$search_term%";
    $stmt->execute([$search_term, $search_term, $search_term]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_dashboard_stats($conn) {
    $stmt = $conn->query("SELECT * FROM vw_dashboard_stats");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_subscribed_users($conn) {
    $stmt = $conn->query("SELECT * FROM vw_subscribed_users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_unsubscribed_users($conn) {
    $stmt = $conn->query("SELECT u.* FROM users u LEFT JOIN subscriptions s ON u.user_id = s.user_id WHERE s.subscription_id IS NULL OR s.subscription_status != 'approved'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_info($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_favorites($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.* FROM posts p JOIN favorites f ON p.post_id = f.post_id WHERE f.user_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_subscription($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_payment($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.* FROM payments p JOIN subscriptions s ON p.subscription_id = s.subscription_id WHERE s.user_id = ? ORDER BY p.created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_category_requests($conn) {
    $stmt = $conn->query("SELECT * FROM category_requests WHERE status = 'pending'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_subscription_requests($conn) {
    $stmt = $conn->query("SELECT * FROM subscription_requests WHERE status = 'pending'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_transactions($conn) {
    $stmt = $conn->query("SELECT p.*, s.subscription_type, s.user_id FROM payments p JOIN subscriptions s ON p.subscription_id = s.subscription_id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>