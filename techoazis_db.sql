CREATE TABLE users (
  user_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(100) NOT NULL,
  username_slug varchar(100) DEFAULT NULL,
  email varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  is_active char(1) NOT NULL DEFAULT 'A',
  registration_date datetime DEFAULT CURRENT_TIMESTAMP,
  user_role char(1) NOT NULL,
  ip varchar(45) DEFAULT NULL,
  activation_code varchar(128) DEFAULT NULL,
  profile_image varchar(255) NOT NULL DEFAULT 'uploads/profile_images/anonymous.png',
  total_posts int(11) DEFAULT 0,
  total_comments int(11) DEFAULT 0,
  sold_items int(11) DEFAULT 0,
  bought_items int(11) DEFAULT 0,
  avg_rating decimal(3,2) DEFAULT 0.00,
  reset_token_hash varchar(64) DEFAULT NULL,
  reset_token_expires_at datetime DEFAULT NULL
);

CREATE TABLE login (
  login_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id int(11) NOT NULL,
  login_date datetime DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  product_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  seller_user_id int(11) NOT NULL,
  product_name varchar(255) NOT NULL,
  category varchar(255) NOT NULL,
  product_description text NOT NULL,
  price decimal(10,2) DEFAULT NULL,
  product_status enum('active','sold','hidden') NOT NULL DEFAULT 'active',
  main_image_url varchar(255) DEFAULT NULL,
  pickup_location varchar(100) DEFAULT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT NULL
);

CREATE TABLE reviews (
  review_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  seller_user_id int(11) NOT NULL,
  buyer_user_id int(11) NOT NULL,
  deal_id int(11) NOT NULL,
  rating tinyint(4) NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment text DEFAULT NULL,
  review_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY deal_id (deal_id)
);


CREATE TABLE posts (
  post_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id int(11) NOT NULL,
  group_id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE comments (
  comment_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  post_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  content text NOT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE badges (
  badge_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  badge_name varchar(100) NOT NULL,
  badge_description text DEFAULT NULL,
  icon varchar(255) DEFAULT NULL
);

CREATE TABLE user_badges (
  user_id int(11) NOT NULL,
  badge_id int(11) NOT NULL,
  earned_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, badge_id)
);

CREATE TABLE product_images (
  image_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id int(11) NOT NULL,
  image_path varchar(255) NOT NULL,
  is_primary tinyint(1) NOT NULL DEFAULT 0,
  sort_order tinyint(4) NOT NULL DEFAULT 1
);


CREATE TABLE post_images (
  image_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  post_id int(11) NOT NULL,
  image_path varchar(255) NOT NULL,
  sort_order tinyint(4) NOT NULL DEFAULT 1
);

CREATE TABLE groups (
  group_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  group_name varchar(100) NOT NULL,
  group_description text DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  group_image varchar(255) DEFAULT 'default_group.png',
  UNIQUE KEY group_name (group_name)
);

CREATE TABLE conversations (
  conversation_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id int(11) NOT NULL,
  seller_user_id int(11) NOT NULL,
  buyer_user_id int(11) NOT NULL,
  conv_status enum('open','deal_made','archived') DEFAULT 'open',
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT NULL,
  is_seller_agreed tinyint(1) DEFAULT 0,
  is_buyer_agreed tinyint(1) DEFAULT 0,
  UNIQUE KEY product_id (product_id, buyer_user_id)
);

CREATE TABLE messages (
  message_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  conversation_id int(11) NOT NULL,
  sender_user_id int(11) NOT NULL,
  user_message text NOT NULL,
  sent_at datetime DEFAULT CURRENT_TIMESTAMP,
  is_read tinyint(1) DEFAULT 0
);

CREATE TABLE deals (
  deal_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id int(11) NOT NULL,
  seller_user_id int(11) NOT NULL,
  buyer_user_id int(11) NOT NULL,
  conversation_id int(11) NOT NULL,
  completed_at datetime DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE articles (
  article_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_id int(11) NOT NULL,
  author_user_id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  slug varchar(280) NOT NULL,
  summary text DEFAULT NULL,
  content longtext NOT NULL,
  cover_image varchar(255) DEFAULT NULL,
  reading_minutes int(11) DEFAULT NULL,
  article_status enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT NULL
);

CREATE TABLE article_categories (
  category_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_name varchar(120) NOT NULL,
  category_slug varchar(140) NOT NULL,
  icon_class varchar(80) DEFAULT NULL,
  sort_order int(11) NOT NULL DEFAULT 100,
  created_at datetime DEFAULT CURRENT_TIMESTAMP
);