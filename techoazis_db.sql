CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    is_active CHAR(1) NOT NULL DEFAULT 'A',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_role CHAR(1) NOT NULL,
    ip VARCHAR(45),
    activation_code VARCHAR(128),
    profile_image VARCHAR(255) NOT NULL DEFAULT('images/anonymous.png')
);

CREATE TABLE login (
    login_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0
);

CREATE TABLE posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    group_id INT NOT NULL,                       
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE badges (
    badge_id INT PRIMARY KEY AUTO_INCREMENT,
    badge_name VARCHAR(100) NOT NULL,
    badge_description TEXT,
    icon VARCHAR(255)
);

CREATE TABLE user_badges (
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id)
);

CREATE TABLE images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL
);

CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(100) NOT NULL UNIQUE,
    group_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
