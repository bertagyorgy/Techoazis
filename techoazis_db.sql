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
    stock_quantity INT NOT NULL DEFAULT 0,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    main_image_url VARCHAR(255)
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
    post_id INT NULL,
    product_id INT NULL,
    image_path VARCHAR(255) NOT NULL
);

CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(255) NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(100) NOT NULL UNIQUE,
    group_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    group_image VARCHAR(255) DEFAULT 'default_group.png'
);

CREATE TABLE shipping_addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    country VARCHAR(100) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    city VARCHAR(100) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50),
    is_billing_address BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_status VARCHAR(50) NOT NULL DEFAULT 'Függőben', 
    payment_method VARCHAR(50), 
    transaction_id VARCHAR(255), 
    shipping_address_id INT NOT NULL, 
    billing_address_id INT
);

CREATE TABLE order_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_snapshot DECIMAL(10,2) NOT NULL
);

INSERT INTO comments (comment_id, post_id, user_id, content, created_at) VALUES
(1, 1, 1, 'Nagyon egyszerűen', '2025-11-18 20:37:21'),
(2, 1, 2, 'HTML + CSS alapokkal érdemes kezdeni!', '2025-11-18 19:27:15'),
(3, 1, 3, 'Ezután jöhet a JavaScript, nagyon fontos lesz!', '2025-11-18 19:28:15'),
(4, 2, 1, 'A Python egyszerűbb kezdőknek, de C# erősebb rendszerszinten.', '2025-11-18 19:29:15'),
(5, 3, 2, 'Nagyon jó összefoglaló, köszi!', '2025-11-18 19:30:15');

INSERT INTO groups (group_id, group_name, group_description, created_at) VALUES
(1, 'Webfejlesztés', 'Frontend, backend és full-stack témák', '2025-11-18 19:24:56'),
(2, 'Programozás', 'Általános programozás: C#, Python, Java stb.', '2025-11-18 19:24:56'),
(3, 'Hardver', 'PC építés, alkatrészek, optimalizálás', '2025-11-18 19:24:56'),
(4, 'Tech hírek', 'Friss újdonságok a tech világban', '2025-11-18 19:24:56');

INSERT INTO images (image_id, post_id, image_path) VALUES
(1, 1, 'uploads/posts/1/starter_guide.png'),
(2, 3, 'uploads/posts/3/tech_trends_2025.jpg');

INSERT INTO posts (post_id, user_id, group_id, title, content, created_at) VALUES
(1, 1, 1, 'Hogyan kezdjem el a webfejlesztést?', 'Sziasztok! Teljesen új vagyok a webfejlesztésben. Mivel érdemes kezdeni?', '2025-11-18 19:25:47'),
(2, 2, 2, 'Melyik a jobb kezdőknek: Python vagy C#?', 'Kezdőként gondolkodom, melyik nyelv lenne jobb? Mi a véleményetek?', '2025-11-18 19:25:47'),
(3, 1, 4, '2025 Tech trendjei', 'Összeszedtem pár érdekességet a 2025-ös év technológiai újításairól.', '2025-11-18 19:25:47');

INSERT INTO users (user_id, username, email, user_password, is_active, registration_date, user_role, ip, activation_code, profile_image) VALUES
(1, 'kistamáska', 'bertagyorgy@gmail.com', '$2y$10$RECzv8fPtXR5wYgsItunp.3yYXw84UzR95tAsCni8VNgVWKSziAJ2', 'A', '2025-11-11 18:57:24', 'A', '::1', '', './profile_images/profile_1.jpg'),
(2, 'kunbéla', 'kunbela17@gmail.com', '$2y$10$er./MPIDc3o41MWuUpV.0.zZft3q/s1fEP8G9IDu8CShqfGYlmYm.', 'A', '2025-11-12 20:03:21', 'F', '::1', '', './images/anonymous.png'),
(3, 'bertagyorgy', 'bertagyorgy222@gmail.com', '$2y$10$wL5BJkrX8vpjXZKeWemdEu2y3nPUB2kR1bHYwsrYeJdPR1jSYome2', 'P', '2025-11-12 20:11:19', 'F', '::1', 'a685256573e5ccbffda4ae4d5463d1da', './images/anonymous.png'),
(5, 'proba123', 'proba123@gmail.com', '$2y$10$jbYznXaB9ZWM8NoE1x.PfurjeM86wPmwsvOfumFU3aBJhauvDFRIy', 'A', '2025-11-12 21:09:56', 'F', '::1', '', './images/anonymous.png');
