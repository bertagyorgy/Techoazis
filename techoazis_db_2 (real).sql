-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Nov 23. 22:10
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `techoazis_db`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `badges`
--

CREATE TABLE `badges` (
  `badge_id` int(11) NOT NULL,
  `badge_name` varchar(100) NOT NULL,
  `badge_description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 1, 'Nagyon egyszerűen', '2025-11-18 20:37:21'),
(2, 1, 2, 'HTML + CSS alapokkal érdemes kezdeni!', '2025-11-18 19:26:15'),
(3, 1, 3, 'Ezután jöhet a JavaScript, nagyon fontos lesz!', '2025-11-18 19:26:15'),
(4, 2, 1, 'A Python egyszerűbb kezdőknek, de C# erősebb rendszerszinten.', '2025-11-18 19:26:15'),
(5, 3, 2, 'Nagyon jó összefoglaló, köszi!', '2025-11-18 19:26:15'),
(6, 3, 1, 'Nincs is itt összeszedve semmi.', '2025-11-20 16:35:44'),
(7, 1, 1, 'Ne a nulladik index-szel 😆', '2025-11-20 16:39:53'),
(8, 3, 1, 'Haló, kéne írni ide valamit nem?', '2025-11-20 16:51:33'),
(9, 3, 1, 'Na, mostmár csoportok is vannak hell yeah', '2025-11-20 18:02:56'),
(10, 4, 6, 'include \'fájlneved.php\'; ennyi, de ha csak egyszer akarod, akkor include_once-al jobb.', '2025-11-21 22:25:22'),
(11, 6, 1, 'Wow, mindenképp kiróbálom!', '2025-11-21 22:26:32'),
(12, 5, 1, 'Köszönöm, legközelebb nem rontom el 🤣🤣🤞', '2025-11-21 22:27:19'),
(13, 6, 1, 'Na, megnéztem, nincsenek nagy változások', '2025-11-23 22:04:57');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `group_image` varchar(255) DEFAULT 'default_group.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`, `group_description`, `created_at`, `group_image`) VALUES
(1, 'Webfejlesztés', 'Frontend, backend és full-stack témák', '2025-11-18 19:24:56', 'default_group.png'),
(2, 'Programozás', 'Általános programozás: C#, Python, Java stb.', '2025-11-18 19:24:56', 'default_group.png'),
(3, 'Hardver', 'PC építés, alkatrészek, optimalizálás', '2025-11-18 19:24:56', 'default_group.png'),
(4, 'Tech hírek', 'Friss újdonságok a tech világban', '2025-11-18 19:24:56', 'default_group.png');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `images`
--

INSERT INTO `images` (`image_id`, `post_id`, `image_path`) VALUES
(1, 1, './uploads/posts/webdev.jpg'),
(2, 3, './uploads/posts/ai.jpg'),
(3, 1, './uploads/posts/htmlandcss.jpeg'),
(4, 4, './uploads/posts/4_1763756851_4212.jpg'),
(5, 6, './uploads/posts/6_1763760130_6025.jpg'),
(6, 6, './uploads/posts/6_1763760130_1499.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `login`
--

CREATE TABLE `login` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `login`
--

INSERT INTO `login` (`login_id`, `user_id`, `login_date`) VALUES
(1, 1, '2025-11-10 13:59:42'),
(2, 2, '2025-11-10 17:29:50'),
(3, 3, '2025-11-10 17:35:29'),
(4, 3, '2025-11-10 17:43:54'),
(5, 3, '2025-11-10 17:49:13'),
(6, 5, '2025-11-10 19:06:38'),
(7, 6, '2025-11-11 10:09:13'),
(8, 3, '2025-11-11 10:26:42'),
(9, 3, '2025-11-11 15:05:44'),
(10, 3, '2025-11-11 17:07:36'),
(11, 6, '2025-11-11 17:07:52'),
(12, 3, '2025-11-11 17:56:09'),
(13, 6, '2025-11-11 18:11:45'),
(14, 5, '2025-11-11 19:12:40'),
(15, 3, '2025-11-12 07:21:48'),
(16, 6, '2025-11-12 08:06:53'),
(17, 3, '2025-11-12 20:33:23'),
(18, 3, '2025-11-12 20:37:25'),
(19, 6, '2025-11-12 21:31:38'),
(20, 3, '2025-11-13 08:24:26'),
(21, 3, '2025-11-14 08:09:45'),
(22, 3, '2025-11-14 08:20:29'),
(23, 3, '2025-11-14 08:24:19'),
(24, 8, '2025-11-14 09:49:25'),
(25, 8, '2025-11-14 10:06:38'),
(26, 1, '2025-11-14 10:18:17'),
(27, 1, '2025-11-14 10:20:59'),
(28, 1, '2025-11-14 10:27:04'),
(29, 10, '2025-11-14 10:29:59'),
(30, 11, '2025-11-14 13:55:55'),
(31, 8, '2025-11-15 10:51:09'),
(32, 1, '2025-11-15 17:53:09'),
(33, 3, '2025-11-16 14:15:46'),
(34, 3, '2025-11-16 20:26:01'),
(35, 13, '2025-11-16 20:36:18'),
(36, 3, '2025-11-16 20:39:56'),
(37, 12, '2025-11-16 21:02:14'),
(38, 3, '2025-11-16 21:02:59'),
(39, 6, '2025-11-16 21:07:03'),
(40, 6, '2025-11-17 09:50:00'),
(41, 3, '2025-11-17 09:50:58'),
(42, 9, '2025-11-17 11:52:26'),
(43, 3, '2025-11-17 12:55:40'),
(44, 3, '2025-11-18 19:55:32'),
(45, 14, '2025-11-19 08:26:39'),
(46, 6, '2025-11-21 12:27:37');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `group_id`, `title`, `content`, `created_at`) VALUES
(1, 1, 1, 'Hogyan kezdjem el a webfejlesztést?', 'Sziasztok! Teljesen új vagyok a webfejlesztésben. Mivel érdemes kezdeni?', '2025-11-18 19:25:47'),
(2, 2, 2, 'Melyik a jobb kezdőknek: Python vagy C#?', 'Kezdőként gondolkodom, melyik nyelv lenne jobb? Mi a véleményetek?', '2025-11-18 19:24:23'),
(3, 1, 4, '2025 Tech trendjei', 'Összeszedtem pár érdekességet a 2025-ös év technológiai újításairól.', '2025-11-18 19:25:47'),
(4, 1, 2, 'PHP kérdés', 'Sziasztok! Az lenne a kérdésem, hogy PHP-ban hogyan kell egy másik php fájlt meghívni? Előre is köszönöm!', '2025-11-21 21:27:31'),
(5, 6, 1, 'Javascript kisokos 1. hét', 'Javascriptben és még sok másik nyelvben fontos megemlíteni, hogy 3 különböző egyenlőségjelet használunk:\r\n= értékadás\r\n== összehasonlítás\r\n=== összehasonlítás és típusösszehasonlítás is egyben\r\nEzek összekeverése súlyos hibákat is okozhat a kódunkban, érdemes tisztába lenni ezekkel.', '2025-11-21 21:36:55'),
(6, 6, 4, 'ChatGPT 5.1', 'A legújabb ChatGTP még kifinomultabb érveléssel rendelkezik és be is lehet állítani is akár, hogy mennyire terhelje meg magát bizonyos kérdésekkel, így időt spórolhatunk, továbbá jobb kódírási teljesítménye lett.\r\nVálaszai valódi fejlesztői visszajelzésekkel lett továbbfejlesztve és tisztább, megbízhatóbb kódokat generál.\r\nTovábbi részletek: https://openai.com/index/gpt-5-1/', '2025-11-21 22:22:10');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `is_active` char(1) NOT NULL DEFAULT 'A',
  `registration_date` datetime DEFAULT current_timestamp(),
  `user_role` char(1) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `activation_code` varchar(128) DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT 'images/anonymous.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `user_password`, `is_active`, `registration_date`, `user_role`, `ip`, `activation_code`, `profile_image`) VALUES
(1, 'kistamáska', 'bertagyorgy@gmail.com', '$2y$10$RECzv8fPtXR5wYgsItunp.3yYXw84UzR95tAsCni8VNgVWKSziAJ2', 'A', '2025-11-11 18:57:24', 'A', '89.133.50.136', NULL, './uploads/profile_images/profile_1.jpg'),
(2, 'kunbéla', 'kunbela17@gmail.com', '$2y$10$er./MPIDc3o41MWuUpV.0.zZft3q/s1fEP8G9IDu8CShqfGYlmYm.', 'A', '2025-11-12 20:03:21', 'F', '89.133.50.136', NULL, 'images/anonymous.png'),
(3, 'bertagyorgy', 'berta.gyorgy@wm-iskola.hu', '$2y$10$ZOiW4bMz48VQPudamc65OOtIbQ.UjBtU4eNobnkQG6VQDOtGYye1.', 'A', '2025-11-10 17:31:59', 'A', '89.133.50.136', NULL, './uploads/profile_images/profile_3.jpg'),
(4, 'geovany', 'zero@gmail.com', '$2y$10$jv2VDqOpEkRNIDxtmaT3Yead21hBsaa/Fn.JxUA5d8DynBYkiodnS', 'T', '2025-11-14 09:53:03', 'F', '37.76.3.96', '4bdf60c252edcc7c2e82065928fc5e90', 'images/anonymous.png'),
(5, 'proba123', 'proba123@gmail.com', '$2y$10$jbYznXaB9ZWM8NoE1x.PfurjeM86wPmwsvOfumFU3aBJhauvDFRIy', 'A', '2025-11-12 21:09:56', 'F', '89.133.50.136', NULL, 'images/anonymous.png'),
(6, 'kisalma', 'kisalma4378@gmail.com', '$2y$10$vDhjwSaSz3705UpDjKqzheTHHDGV0mC0Lty107/HhReeaohMy3el6', 'A', '2025-11-16 21:50:38', 'F', '89.133.50.136', NULL, './uploads/profile_images/profile_6.jpg'),
(7, 'papa', 'papandras01@gmail.com', '$2y$10$vdPPimpbuAGFOD2oLHe7MO8xB6L9pFwWzoRLmsXmzGyDt5cn/OJmi', 'P', '2025-11-10 19:11:47', 'F', '82.131.141.97', 'b8dfd4d73be091a54cd43450fd4a8c7a', 'images/anonymous.png'),
(8, 'pap.mate', 'pap.mate@wm-iskola.hu', '$2y$10$tQZ1wCT7g69Oqj1JdFE5JeqUUULiJRPXWGJ6XSnQhBa/R2YHZCof.', 'A', '2025-11-11 10:08:14', 'A', '195.199.251.129', NULL, './uploads/profile_images/profile_7.jpg'),
(9, 'bertagyuri', 'bertagyuro@gmail.com', '$2y$10$Q7MYLHBqb36oBEHTsHLUWuDPPy2cLN6SSdNUgO9SufctiEiHcx69u', 'A', '2025-11-14 10:17:10', 'F', '195.199.251.129', NULL, './uploads/profile_images/profile_14.webp'),
(10, 'reudav', 'reucovdavid@gmail.com', '$2y$10$wj9nC78bA4T43cH.jZgMGutoAqd8mpQmuX4fAyY6mMleeKssU5rnO', 'A', '2025-11-14 10:18:28', 'F', '89.134.18.49', NULL, './uploads/profile_images/profile_15.jpg'),
(11, 'Bajni', 'budavaribarnabas@gmail.com', '$2y$10$C6N/7tbsOyJL0QzUsAaBsOSBs6p6P9iTDwMu8GAYv7D7/AQew2she', 'A', '2025-11-14 13:55:15', 'F', '86.101.204.221', NULL, 'images/anonymous.png'),
(12, 'kisgéza', 'kisgéza@gmail.com', '$2y$10$YU5XPd.d291GlJE6PFQQNeNOcNSxRF0bzVRSvB61rqE3cxNIdy9tO', 'A', '2025-11-16 20:34:54', 'F', '89.133.50.136', NULL, 'images/anonymous.png'),
(13, 'kisalma2', 'kisalma2@gmail.com', '$2y$10$kD.q9cOdRZDXRrLYA2UqFOPmhRqFqwtGOwCrhJ5eYk72YLCT3uBFu', 'A', '2025-11-16 20:57:53', 'F', '89.133.50.136', NULL, 'images/anonymous.png'),
(14, 'Romeoboss', 'Ravasz.romeo@wm-iskola.hu', '$2y$10$y/4hPXJ8YWJ44VtM6RtQ1uSZ7UctA5wlH/hPiweZrFXVnpC88Dekq', 'A', '2025-11-19 08:25:33', 'F', '195.199.251.129', NULL, 'images/anonymous.png');
-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_badges`
--

CREATE TABLE `user_badges` (
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`);

--
-- A tábla indexei `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- A tábla indexei `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- A tábla indexei `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`),
  ADD UNIQUE KEY `group_name` (`group_name`);

--
-- A tábla indexei `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- A tábla indexei `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`login_id`);

--
-- A tábla indexei `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT a táblához `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `login`
--
ALTER TABLE `login`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT a táblához `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
