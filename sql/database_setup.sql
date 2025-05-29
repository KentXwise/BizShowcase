-- Creating database
CREATE DATABASE bizshowcase;
USE bizshowcase;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    birthday DATE,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active'
);

-- Business Profiles table
CREATE TABLE business_profiles (
    business_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    company_name VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    business_email VARCHAR(100),
    business_address TEXT,
    business_number VARCHAR(20),
    seller_type VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Subscriptions table
CREATE TABLE subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    subscription_type ENUM('monthly', 'yearly'),
    subscription_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Subscription Requests table
CREATE TABLE subscription_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    company_name VARCHAR(100),
    number VARCHAR(20),
    business_certificate VARCHAR(255),
    business_clearance VARCHAR(255),
    business_permit VARCHAR(255),
    subscription_type ENUM('monthly', 'yearly'),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    company_name VARCHAR(100),
    description TEXT,
    business_email VARCHAR(100),
    business_address TEXT,
    business_number VARCHAR(20),
    seller_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Post Images table
CREATE TABLE post_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    image_path VARCHAR(255),
    FOREIGN KEY (post_id) REFERENCES posts(post_id)
);

-- Likes table
CREATE TABLE likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Favorites table
CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Comments table
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    comment_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Follows table
CREATE TABLE follows (
    follow_id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT,
    followed_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(user_id),
    FOREIGN KEY (followed_id) REFERENCES users(user_id)
);

-- Notifications table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('like', 'comment', 'favorite', 'subscription_approved', 'payment_approved'),
    related_id INT,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Payments table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT,
    user_id INT,
    amount DECIMAL(10,2),
    payment_status ENUM('pending', 'paid', 'rejected') DEFAULT 'pending',
    receipt_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Admins table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    admin_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Category Requests table
CREATE TABLE category_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_name VARCHAR(100),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Indexes
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_post_user_id ON posts(user_id);
CREATE INDEX idx_subscription_user_id ON subscriptions(user_id);2
CREATE INDEX idx_payment_subscription_id ON payments(subscription_id);

-- View for Subscribed Users
CREATE VIEW vw_subscribed_users AS
SELECT u.user_id, u.first_name, u.last_name, u.email, s.subscription_type, s.subscription_status
FROM users u
JOIN subscriptions s ON u.user_id = s.user_id
WHERE s.subscription_status = 'approved';

-- View for Dashboard Statistics
CREATE VIEW vw_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM subscriptions WHERE subscription_status = 'approved') AS subscribed_users,
    (SELECT COUNT(*) FROM posts) AS total_posts,
    (SELECT COUNT(*) FROM subscriptions WHERE subscription_status != 'approved') AS unsubscribed_users;

-- Stored Procedure to Add Post
DELIMITER //
CREATE PROCEDURE sp_add_post(
    IN p_user_id INT,
    IN p_category_id INT,
    IN p_company_name VARCHAR(100),
    IN p_description TEXT,
    IN p_business_email VARCHAR(100),
    IN p_business_address TEXT,
    IN p_business_number VARCHAR(20),
    IN p_seller_type VARCHAR(50)
)
BEGIN
    DECLARE subscription_active INT;
    
    -- Check if user has active subscription
    SELECT COUNT(*) INTO subscription_active
    FROM subscriptions
    WHERE user_id = p_user_id AND subscription_status = 'approved';
    
    IF subscription_active > 0 THEN
        INSERT INTO posts (
            user_id, category_id, company_name, description, 
            business_email, business_address, business_number, seller_type
        ) VALUES (
            p_user_id, p_category_id, p_company_name, p_description,
            p_business_email, p_business_address, p_business_number, p_seller_type
        );
        SELECT LAST_INSERT_ID() AS post_id;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User does not have an active subscription';
    END IF;
END //
DELIMITER ;

-- Trigger for Notification on Like
DELIMITER //
CREATE TRIGGER tr_after_like_insert
AFTER INSERT ON likes
FOR EACH ROW
BEGIN
    DECLARE post_owner_id INT;
    
    SELECT user_id INTO post_owner_id FROM posts WHERE post_id = NEW.post_id;
    
    INSERT INTO notifications (user_id, type, related_id, message)
    VALUES (
        post_owner_id,
        'like',
        NEW.post_id,
        CONCAT('Your post received a new like from user #', NEW.user_id)
    );
END //
DELIMITER ;

-- Trigger for Notification on Comment
DELIMITER //
CREATE TRIGGER tr_after_comment_insert
AFTER INSERT ON comments
FOR EACH ROW
BEGIN
    DECLARE post_owner_id INT;
    
    SELECT user_id INTO post_owner_id FROM posts WHERE post_id = NEW.post_id;
    
    INSERT INTO notifications (user_id, type, related_id, message)
    VALUES (
        post_owner_id,
        'comment',
        NEW.post_id,
        CONCAT('Your post received a new 2comment from user #', NEW.user_id)
    );
END //
DELIMITER ;

-- Trigger for Notification on Favorite
DELIMITER //
CREATE TRIGGER tr_after_favorite_insert
AFTER INSERT ON favorites
FOR EACH ROW
BEGIN
    DECLARE post_owner_id INT;
    
    SELECT user_id INTO post_owner_id FROM posts WHERE post_id = NEW.post_id;
    
    INSERT INTO notifications (user_id, type, related_id, message)
    VALUES (
        post_owner_id,
        'favorite',
        NEW.post_id,
        CONCAT('Your post was favorited by user #', NEW.user_id)
    );
END //
DELIMITER ;

-- Updated and Added Queeries
ALTER TABLE users ADD COLUMN suspension_end_date DATETIME DEFAULT NULL;

CREATE INDEX idx_users_status ON users(status);

-- Trial added
CREATE OR REPLACE VIEW vw_subscribed_users AS
SELECT u.user_id, u.first_name, u.last_name, u.email, s.subscription_type
FROM users u
JOIN subscriptions s ON u.user_id = s.user_id
WHERE s.subscription_status = 'approved' AND u.status != 'deleted';

-- 5/15/2025 Added Queery
DELIMITER //

CREATE OR REPLACE PROCEDURE sp_add_post(
    IN p_user_id INT,
    IN p_category_id INT,
    IN p_company_name VARCHAR(100),
    IN p_description TEXT,
    IN p_business_email VARCHAR(100),
    IN p_business_address TEXT,
    IN p_business_number VARCHAR(20),
    IN p_seller_type VARCHAR(50)
)
BEGIN
    DECLARE subscription_active INT;
    DECLARE user_status ENUM('active', 'suspended', 'deleted');
    
    -- Check user status
    SELECT status INTO user_status
    FROM users
    WHERE user_id = p_user_id;
    
    IF user_status != 'active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User account is not active';
    END IF;
    
    -- Check if user has active subscription
    SELECT COUNT(*) INTO subscription_active
    FROM subscriptions s
    LEFT JOIN payments p ON s.subscription_id = p.subscription_id
    WHERE s.user_id = p_user_id 
    AND s.subscription_status = 'approved'
    AND (p.payment_status = 'paid' OR p.payment_status IS NULL);
    
    IF subscription_active > 0 THEN
        INSERT INTO posts (
            user_id, category_id, company_name, description, 
            business_email, business_address, business_number, seller_type
        ) VALUES (
            p_user_id, p_category_id, p_company_name, p_description,
            p_business_email, p_business_address, p_business_number, p_seller_type
        );
        SELECT LAST_INSERT_ID() AS post_id;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User does not have an active subscription';
    END IF;
END //

DELIMITER ;