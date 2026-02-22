-- Felhasználók tábla feltöltése
INSERT INTO `users` (`user_id`, `username`, `username_slug`, `email`, `user_password`, `is_active`, `registration_date`, `user_role`, `ip`, `activation_code`, `profile_image`, `total_posts`, `total_comments`, `sold_items`, `bought_items`, `avg_rating`, `reset_token_hash`, `reset_token_expires_at`) VALUES
(1, 'kistamáska', 'kistamaska', 'bertagyorgy@gmail.com', '$2y$10$kyug83mm.Kc5/ieyX5G3JOx/kRYCt753zQezR2YAe51r7Fe.AVqUO', 'A', '2025-11-11 18:57:24', 'A', '::1', '', './uploads/profile_images/profile_1.jpg', 0, 0, 0, 0, 0.00, NULL, NULL),
(2, 'kunbéla', 'kunbela', 'kunbela17@gmail.com', '$2y$10$er./MPIDc3o41MWuUpV.0.zZft3q/s1fEP8G9IDu8CShqfGYlmYm.', 'A', '2025-11-12 20:03:21', 'F', '::1', '', 'uploads/profile_images/anonymous.png', 0, 0, 0, 0, 0.00, NULL, NULL),
(3, 'bertagyorgy', 'bertagyorgy', 'bertagyorgy222@gmail.com', '$2y$10$wL5BJkrX8vpjXZKeWemdEu2y3nPUB2kR1bHYwsrYeJdPR1jSYome2', 'P', '2025-11-12 20:11:19', 'F', '::1', 'a685256573e5ccbffda4ae4d5463d1da', 'uploads/profile_images/anonymous.png', 0, 0, 0, 0, 0.00, NULL, NULL),
(5, 'proba123', 'proba123', 'proba123@gmail.com', '$2y$10$jbYznXaB9ZWM8NoE1x.PfurjeM86wPmwsvOfumFU3aBJhauvDFRIy', 'A', '2025-11-12 21:09:56', 'F', '::1', '', 'uploads/profile_images/anonymous.png', 0, 0, 0, 0, 0.00, NULL, NULL),
(6, 'kisalma', 'kisalma', 'kisalma4378@gmail.com', '$2y$10$vDhjwSaSz3705UpDjKqzheTHHDGV0mC0Lty107/HhReeaohMy3el6', 'A', '2025-11-16 21:50:38', 'F', '::1', NULL, './uploads/profile_images/profile_6.jpg', 0, 0, 0, 0, 0.00, NULL, NULL),
(7, 'admin', 'admin', 'papmd2014@gmail.com', '$2y$10$kyug83mm.Kc5/ieyX5G3JOx/kRYCt753zQezR2YAe51r7Fe.AVqUO', 'A', '2025-12-14 12:00:18', 'A', '::1', NULL, './uploads/profile_images/profile_7_1766229028.jpg', 0, 0, 0, 0, 0.00, NULL, NULL);

-- Csoportok tábla feltöltése
INSERT INTO `groups` (`group_id`, `group_name`, `group_description`, `created_at`, `group_image`) VALUES
(1, 'Webfejlesztés', 'Frontend, backend és full-stack témák', '2025-11-18 19:24:56', 'default_group.png'),
(2, 'Programozás', 'Általános programozás: C#, Python, Java stb.', '2025-11-18 19:24:56', 'default_group.png'),
(3, 'Hardver', 'PC építés, alkatrészek, optimalizálás', '2025-11-18 19:24:56', 'default_group.png'),
(4, 'Tech hírek', 'Friss újdonságok a tech világban', '2025-11-18 19:24:56', 'default_group.png');

-- Posztok tábla feltöltése
INSERT INTO `posts` (`post_id`, `user_id`, `group_id`, `title`, `content`, `created_at`) VALUES
(1, 1, 1, 'Hogyan kezdjem el a webfejlesztést?', 'Sziasztok! Teljesen új vagyok a webfejlesztésben. Mivel érdemes kezdeni?', '2025-11-18 19:25:47'),
(2, 2, 2, 'Melyik a jobb kezdőknek: Python vagy C#?', 'Kezdőként gondolkodom, melyik nyelv lenne jobb? Mi a véleményetek?', '2025-11-18 19:25:47'),
(3, 1, 4, '2025 Tech trendjei', 'Összeszedtem pár érdekességet a 2025-ös év technológiai újításairól.', '2025-11-18 19:25:47'),
(4, 1, 1, 'Milyen hasznuk van keretrendszereknek?', 'Szeretnék saját weboldalon keretrendszereket használni (vue.js, laravel), de nem tudom mennyire éri meg, valaki ki tudná fejteni részletesen?', '2026-01-14 18:45:42'),
(5, 1, 3, 'MSI vagy ASUS videókártya?', 'Személy szerint az ASUS nvidia geforce szériát jobban preferálom, de azért megkérdezek másokat is, ti mit gondoltok?', '2026-01-14 18:55:40'),
(6, 1, 4, 'ChatGPT 5.1', 'A legújabb ChatGPT még kifinomultabb érveléssel rendelkezik...', '2026-01-14 20:27:36');

-- Hozzászólások tábla feltöltése
INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 1, 'Nagyon egyszerűen', '2025-11-18 20:37:21'),
(2, 1, 2, 'HTML + CSS alapokkal érdemes kezdeni!', '2025-11-18 19:27:15'),
(3, 1, 3, 'Ezután jöhet a JavaScript, nagyon fontos lesz!', '2025-11-18 19:28:15'),
(4, 2, 1, 'A Python egyszerűbb kezdőknek, de C# erősebb rendszerszinten.', '2025-11-18 19:29:15'),
(5, 3, 2, 'Nagyon jó összefoglaló, köszi!', '2025-11-18 19:30:15'),
(6, 3, 7, 'tényleg jó!', '2026-01-03 16:11:33'),
(7, 2, 7, 'rendszerszinten egy normális IDE-t se tudtak összerakni a C#-nek az elmúlt 10 év alatt.', '2026-01-03 16:24:41'),
(8, 2, 7, 'a python meg egy kenyérpirítón is elfut', '2026-01-03 16:25:04');

-- Termékek tábla feltöltése
INSERT INTO `products` (`product_id`, `seller_user_id`, `product_name`, `category`, `product_description`, `price`, `product_status`, `main_image_url`, `pickup_location`, `created_at`, `updated_at`) VALUES
(1, 1, 'Gaming Egér', 'gaming', 'RGB világítású gamer egér, 6 programozható gombbal.', 8990.00, 'active', NULL, 'Budapest', '2025-12-14 11:53:39', NULL),
(2, 1, 'Bluetooth Hangszóró', 'hangtechnika', 'Vízálló hordozható Bluetooth hangszóró erős basszussal.', 12990.00, 'active', NULL, 'Debrecen', '2025-12-14 11:53:39', NULL),
(3, 1, 'Okosóra', 'okosórák', 'Pulzusmérős okosóra több mint 20 sportmóddal.', 19990.00, 'active', NULL, 'Szeged', '2025-12-14 11:53:39', NULL),
(4, 1, 'Laptop', 'laptopok', 'Kiváló minőségű laptop.', 149900.00, 'active', NULL, 'Budapest XI. kerület', '2025-12-14 11:53:39', NULL),
(5, 1, 'Fényképezőgép', 'fényképezőgépek', 'Nagy látószögű precíz kamera.', 49900.00, 'active', NULL, 'Postázás', '2025-12-14 11:53:39', NULL);

-- Beszélgetések tábla feltöltése
INSERT INTO `conversations` (`conversation_id`, `product_id`, `seller_user_id`, `buyer_user_id`, `conv_status`, `created_at`, `updated_at`, `is_seller_agreed`, `is_buyer_agreed`) VALUES
(2, 2, 3, 6, 'open', '2025-12-14 12:32:51', NULL, 0, 0),
(3, 1, 1, 7, 'open', '2025-12-20 11:33:53', '2026-01-06 17:19:54', 0, 0),
(4, 3, 1, 7, 'open', '2026-01-03 13:33:25', NULL, 0, 0),
(5, 4, 1, 7, 'open', '2026-01-03 13:33:46', NULL, 0, 0),
(6, 2, 1, 7, 'open', '2026-01-06 22:15:06', NULL, 0, 0),
(7, 5, 1, 7, 'open', '2026-01-06 22:23:17', NULL, 0, 0);

-- Üzenetek tábla feltöltése
INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_user_id`, `user_message`, `sent_at`, `is_read`) VALUES
(1, 2, 3, 'Hol lehet átvenni a terméket?', '2025-12-14 12:34:25', 1),
(2, 2, 6, 'Budapesten', '2025-12-14 12:36:15', 1),
(3, 3, 7, 'x', '2025-12-20 11:35:37', 1),
(4, 3, 7, 'o', '2025-12-20 11:36:13', 1),
(5, 3, 7, 'yooo', '2025-12-20 11:45:26', 1),
(6, 3, 1, 'okeybro', '2025-12-20 11:49:01', 1);

-- Adásvételek tábla feltöltése
INSERT INTO `deals` (`deal_id`, `product_id`, `seller_user_id`, `buyer_user_id`, `conversation_id`, `completed_at`) VALUES
(2, 5, 7, 6, 2, '2025-12-14 18:38:13');

-- Cikkek tábla feltöltése
INSERT INTO `articles` (`article_id`, `category_id`, `author_user_id`, `title`, `slug`, `summary`, `content`, `cover_image`, `reading_minutes`, `article_status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Mi az AJAX és mikor érdemes használni?', 'mi-az-ajax-es-mikor-erdemes-hasznalni', 'Az AJAX lehetővé teszi, hogy az oldal frissítés nélkül kommunikáljon a szerverrel.', 'Az AJAX (Asynchronous JavaScript and XML) egy technika, amellyel a kliens aszinkron módon adatot kérhet le a szervertől.\n\nGyakori felhasználás:\n- chat rendszerek\n- űrlap validálás\n- értesítések', 'uploads/articles/ajax.png', 6, 'published', '2026-02-05 21:10:34', NULL),
(2, 2, 1, 'Session kezelés PHP-ben érthetően', 'session-kezeles-php-ben-erthetoen', 'A sessionök segítségével állapotot tudunk tárolni PHP alkalmazásokban.', 'A PHP session szerveroldali megoldás, amellyel a felhasználóhoz kötött adatokat kezelhetünk. Tipikus használat: bejelentkezés, kosár, jogosultság kezelés', 'uploads/articles/php-session.png', 7, 'published', '2026-02-05 21:10:34', NULL),
(3, 3, 3, 'CSS Grid vs Flexbox - mikor melyiket?', 'css-grid-vs-flexbox-mikor-melyiket', 'A Grid és a Flexbox nem egymás konkurensei, hanem másra valók.', 'A Flexbox egy dimenzióban dolgozik, míg a CSS Grid kétdimenziós elrendezést tesz lehetővé.\n\nFlexbox:\n- navbar\n- listák\n\nGrid:\n- layoutok', 'uploads/articles/grid-flexbox.png', 5, 'published', '2026-02-05 21:10:34', NULL),
(4, 4, 6, 'SQL JOIN-ek egyszerű példákkal', 'sql-join-ek-egyszeru-peldakkal', 'JOIN segítségével több tábla adatait kapcsolhatjuk össze.', 'A leggyakoribb JOIN típusok:\n\n- INNER JOIN\n- LEFT JOIN\n- RIGHT JOIN\n\nEzek alapjai nélkül nincs komoly SQL tudás.', 'uploads/articles/sql-joins.png', 8, 'published', '2026-02-05 21:10:34', NULL),
(5, 1, 7, 'MVC architektúra alapjai', 'mvc-architektura-alapjai', 'Az MVC segít átlátható és karbantartható alkalmazásokat készíteni.', 'Az MVC három részből áll: Model - adatkezelés, View - megjelenítés, Controller - vezérlés. Ez a minta skálázható rendszerek alapja.', 'uploads/articles/mvc.jpg', 6, 'published', '2026-02-05 21:10:34', NULL);

-- Belépési napló tábla feltöltése
INSERT INTO `login` (`login_id`, `user_id`, `login_date`) VALUES
(1, 7, '2025-12-14 12:11:55'),
(2, 7, '2025-12-14 18:04:37'),
(3, 7, '2025-12-20 11:24:01'),
(4, 1, '2025-12-20 11:48:28'),
(5, 7, '2025-12-20 11:54:42');

-- Poszt képek tábla feltöltése
INSERT INTO `post_images` (`image_id`, `post_id`, `image_path`, `sort_order`) VALUES
(1, 1, 'uploads/posts/webdev.jpg', 1),
(2, 3, 'uploads/posts/ai.jpg', 1),
(3, 1, 'uploads/posts/htmlandcss.jpeg', 1),
(4, 4, 'uploads/posts/4_1763756851_4212.jpg', 1),
(5, 6, 'uploads/posts/6_1763760130_6025.jpg', 1),
(6, 6, 'uploads/posts/6_1763760130_1499.jpg', 1);

-- Termék képek tábla feltöltése
INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(7, 1, 'uploads/products/gaming_mouse.png', 1, 1),
(8, 2, 'uploads/products/bt_speaker.png', 1, 1),
(9, 3, 'uploads/products/smartwatch.png', 1, 1),
(10, 4, 'uploads/products/laptop.png', 1, 1),
(11, 5, 'uploads/products/camera.png', 1, 1);

-- 3. Cikk kategóriák (Article Categories) - Eddig üres volt, de a cikkek hivatkoztak rá
-- (A cikkeknél láttam 1, 2, 3, 4-es kategória id-t használni)
INSERT INTO `article_categories` (`category_id`, `category_name`, `category_slug`, `sort_order`) VALUES
(1, 'Webfejlesztés', 'webfejlesztes', 1),
(2, 'Backend', 'backend', 2),
(3, 'Frontend', 'frontend', 3),
(4, 'Adatbázisok', 'adatbazisok', 4);

-- 4. Értékelések (Reviews)
-- Fontos: A deal_id-nak léteznie kell a deals táblában!
INSERT INTO `reviews` (`review_id`, `seller_user_id`, `buyer_user_id`, `deal_id`, `rating`, `comment`, `review_date`) VALUES
(1, 7, 6, 2, 5, 'Minden rendben ment, a fényképezőgép szuper állapotban van!', '2025-12-14 19:00:00');

-- 5. További tesztadatok a meglévő táblákba a sűrűbb tartalomért

-- Újabb termékek (különböző eladóktól)
INSERT INTO `products` (`product_id`, `seller_user_id`, `product_name`, `category`, `product_description`, `price`, `product_status`, `pickup_location`) VALUES
(6, 2, 'Mechanical Keyboard', 'gaming', 'Kék kapcsolós mechanikus billentyűzet.', 15000.00, 'active', 'Győr'),
(7, 6, 'iPhone 13 Pro', 'telefonok', 'Használt, de karcmentes állapotban.', 210000.00, 'active', 'Budapest');

-- Újabb beszélgetés indítása
INSERT INTO `conversations` (`conversation_id`, `product_id`, `seller_user_id`, `buyer_user_id`, `conv_status`) VALUES
(8, 6, 2, 1, 'open');

-- Üzenetek az új beszélgetéshez
INSERT INTO `messages` (`conversation_id`, `sender_user_id`, `user_message`) VALUES
(8, 1, 'Szia! Megvan még a billentyűzet?'),
(8, 2, 'Szia, igen, megvan!');

-- Újabb bejelentkezések
INSERT INTO `login` (`user_id`, `login_date`) VALUES
(1, '2026-02-06 08:30:00'),
(2, '2026-02-06 09:15:00'),
(6, '2026-02-06 14:20:00');