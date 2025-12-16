CREATE TABLE users (
  user_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(100) NOT NULL,
  email varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  is_active char(1) NOT NULL DEFAULT 'A',
  registration_date datetime DEFAULT current_timestamp(),
  user_role char(1) NOT NULL,
  ip varchar(45) DEFAULT NULL,
  activation_code varchar(128) DEFAULT NULL,
  profile_image varchar(255) NOT NULL DEFAULT 'images/anonymous.png',
  total_posts int(11) DEFAULT 0,
  total_comments int(11) DEFAULT 0,
  sold_items int(11) DEFAULT 0,
  bought_items int(11) DEFAULT 0,
  avg_rating decimal(3,2) DEFAULT 0.00
)

CREATE TABLE login (
    login_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    product_description TEXT NOT NULL,
    price DECIMAL(10,2) NULL, -- alkuképes / opcionális
    product_status ENUM('active','sold','hidden') NOT NULL DEFAULT 'active',
    pickup_location VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_user_id INT NOT NULL,
    buyer_user_id INT NOT NULL,
    deal_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NULL,
    review_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (deal_id)
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
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order TINYINT NOT NULL DEFAULT 1
);


CREATE TABLE groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(100) NOT NULL UNIQUE,
    group_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    group_image VARCHAR(255) DEFAULT 'default_group.png'
);

CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    seller_user_id INT NOT NULL,
    buyer_user_id INT NOT NULL,
    conv_status ENUM('open','deal_made','cancelled') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    UNIQUE (product_id, buyer_user_id)
);

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_user_id INT NOT NULL,
    user_message TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
);

CREATE TABLE deals (
    deal_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    seller_user_id INT NOT NULL,
    buyer_user_id INT NOT NULL,
    conversation_id INT NOT NULL,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products 
(seller_user_id, product_name, category, product_description, price, product_status, pickup_location) VALUES
(1, 'Gaming Egér', 'gaming', 'RGB világítású gamer egér, 6 programozható gombbal.', 8990.00, 'active', 'Budapest'),
(1, 'Bluetooth Hangszóró', 'hangtechnika', 'Vízálló hordozható Bluetooth hangszóró erős basszussal.', 12990.00, 'active', 'Debrecen'),
(1, 'Okosóra', 'okosórák', 'Pulzusmérős okosóra több mint 20 sportmóddal.', 19990.00, 'active', 'Szeged'),
(1, 'Laptop', 'laptopok', 'Kiváló minőségű laptop.', 149900.00, 'active', 'Budapest XI. kerület'),
(1, 'Fényképezőgép', 'fényképezőgépek', 'Nagy látószögű precíz kamera.', 49900.00, 'sold', 'Postázás');



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

INSERT INTO images (image_id, post_id, product_id, image_path, is_primary, sort_order) VALUES
(1, 1, NULL, 'uploads/posts/webdev.jpg', 0, 1),
(2, 3, NULL, 'uploads/posts/ai.jpg', 0, 1),
(3, 1, NULL, 'uploads/posts/htmlandcss.jpeg', 0, 1),
(4, 4, NULL, 'uploads/posts/4_1763756851_4212.jpg', 0, 1),
(5, 6, NULL, 'uploads/posts/6_1763760130_6025.jpg', 0, 1),
(6, 6, NULL, 'uploads/posts/6_1763760130_1499.jpg', 0, 1),
(7, NULL, 1, 'uploads/products/gaming_mouse.png', 1, 1),
(8, NULL, 2, 'uploads/products/bt_speaker.png', 1, 1),
(9, NULL, 3, 'uploads/products/smartwatch.png', 1, 1),
(10, NULL, 4, 'uploads/products/laptop.png', 1, 1),
(11, NULL, 5, 'uploads/products/fenykep.png', 1, 1);


INSERT INTO posts (post_id, user_id, group_id, title, content, created_at) VALUES
(1, 1, 1, 'Hogyan kezdjem el a webfejlesztést?', 'Sziasztok! Teljesen új vagyok a webfejlesztésben. Mivel érdemes kezdeni?', '2025-11-18 19:25:47'),
(2, 2, 2, 'Melyik a jobb kezdőknek: Python vagy C#?', 'Kezdőként gondolkodom, melyik nyelv lenne jobb? Mi a véleményetek?', '2025-11-18 19:25:47'),
(3, 1, 4, '2025 Tech trendjei', 'Összeszedtem pár érdekességet a 2025-ös év technológiai újításairól.', '2025-11-18 19:25:47');

INSERT INTO users (user_id, username, email, user_password, is_active, registration_date, user_role, ip, activation_code, profile_image) VALUES
(1, 'kistamáska', 'bertagyorgy@gmail.com', '$2y$10$RECzv8fPtXR5wYgsItunp.3yYXw84UzR95tAsCni8VNgVWKSziAJ2', 'A', '2025-11-11 18:57:24', 'A', '::1', '', './uploads/profile_images/profile_1.jpg'),
(2, 'kunbéla', 'kunbela17@gmail.com', '$2y$10$er./MPIDc3o41MWuUpV.0.zZft3q/s1fEP8G9IDu8CShqfGYlmYm.', 'A', '2025-11-12 20:03:21', 'F', '::1', '', './images/anonymous.png'),
(3, 'bertagyorgy', 'bertagyorgy222@gmail.com', '$2y$10$wL5BJkrX8vpjXZKeWemdEu2y3nPUB2kR1bHYwsrYeJdPR1jSYome2', 'P', '2025-11-12 20:11:19', 'F', '::1', 'a685256573e5ccbffda4ae4d5463d1da', './images/anonymous.png'),
(5, 'proba123', 'proba123@gmail.com', '$2y$10$jbYznXaB9ZWM8NoE1x.PfurjeM86wPmwsvOfumFU3aBJhauvDFRIy', 'A', '2025-11-12 21:09:56', 'F', '::1', '', './images/anonymous.png'),
(6, 'kisalma', 'kisalma4378@gmail.com', '$2y$10$vDhjwSaSz3705UpDjKqzheTHHDGV0mC0Lty107/HhReeaohMy3el6', 'A', '2025-11-16 21:50:38', 'F', '::1', NULL, './uploads/profile_images/profile_6.jpg');
