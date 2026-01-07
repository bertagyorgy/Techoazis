-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Jan 06. 23:20
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
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 1, 'Nagyon egyszerűen', '2025-11-18 20:37:21'),
(2, 1, 2, 'HTML + CSS alapokkal érdemes kezdeni!', '2025-11-18 19:27:15'),
(3, 1, 3, 'Ezután jöhet a JavaScript, nagyon fontos lesz!', '2025-11-18 19:28:15'),
(4, 2, 1, 'A Python egyszerűbb kezdőknek, de C# erősebb rendszerszinten.', '2025-11-18 19:29:15'),
(5, 3, 2, 'Nagyon jó összefoglaló, köszi!', '2025-11-18 19:30:15'),
(6, 3, 7, 'tényleg jó!', '2026-01-03 16:11:33'),
(7, 2, 7, 'rendszerszinten egy normális IDE-t se tudtak összerakni a C#-nek az elmúlt 10 év alatt.', '2026-01-03 16:24:41'),
(8, 2, 7, 'a python meg egy kenyérpirítón is elfut', '2026-01-03 16:25:04');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_user_id` int(11) NOT NULL,
  `buyer_user_id` int(11) NOT NULL,
  `conv_status` enum('open','deal_made','cancelled') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `product_id`, `seller_user_id`, `buyer_user_id`, `conv_status`, `created_at`, `updated_at`) VALUES
(2, 2, 3, 6, 'open', '2025-12-14 12:32:51', NULL),
(3, 1, 1, 7, 'open', '2025-12-20 11:33:53', '2026-01-06 17:19:54'),
(4, 3, 1, 7, '', '2026-01-03 13:33:25', NULL),
(5, 4, 1, 7, '', '2026-01-03 13:33:46', NULL),
(6, 2, 1, 7, 'open', '2026-01-06 22:15:06', NULL),
(7, 5, 1, 7, 'open', '2026-01-06 22:23:17', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `deals`
--

CREATE TABLE `deals` (
  `deal_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_user_id` int(11) NOT NULL,
  `buyer_user_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `completed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `deals`
--

INSERT INTO `deals` (`deal_id`, `product_id`, `seller_user_id`, `buyer_user_id`, `conversation_id`, `completed_at`) VALUES
(2, 5, 7, 6, 2, '2025-12-14 18:38:13');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

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
  `post_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `images`
--

INSERT INTO `images` (`image_id`, `post_id`, `product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
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
(11, NULL, 5, 'uploads/products/camera.png', 1, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `login`
--

CREATE TABLE `login` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `login`
--

INSERT INTO `login` (`login_id`, `user_id`, `login_date`) VALUES
(1, 7, '2025-12-14 12:11:55'),
(2, 7, '2025-12-14 18:04:37'),
(3, 7, '2025-12-20 11:24:01'),
(4, 1, '2025-12-20 11:48:28'),
(5, 7, '2025-12-20 11:54:42'),
(6, 7, '2025-12-20 12:19:10'),
(7, 1, '2025-12-28 12:33:23'),
(8, 7, '2025-12-28 20:10:21'),
(9, 1, '2025-12-28 20:10:45'),
(10, 7, '2025-12-28 20:11:19'),
(11, 1, '2025-12-28 20:11:48'),
(12, 7, '2025-12-28 20:12:02'),
(13, 1, '2025-12-28 20:12:56'),
(14, 7, '2026-01-03 12:11:48'),
(15, 1, '2026-01-03 12:15:54'),
(16, 7, '2026-01-03 12:54:05'),
(17, 7, '2026-01-03 13:01:58'),
(18, 7, '2026-01-03 13:17:43'),
(19, 7, '2026-01-03 13:21:47'),
(20, 7, '2026-01-04 17:02:46'),
(21, 7, '2026-01-04 20:02:39'),
(22, 1, '2026-01-04 20:26:03'),
(23, 7, '2026-01-04 20:32:57'),
(24, 1, '2026-01-04 20:34:18'),
(25, 7, '2026-01-05 21:27:58'),
(26, 7, '2026-01-05 21:31:41'),
(27, 7, '2026-01-05 21:36:23'),
(28, 7, '2026-01-05 21:40:13'),
(29, 1, '2026-01-05 21:48:42'),
(30, 1, '2026-01-05 21:55:48'),
(31, 7, '2026-01-06 16:30:12'),
(32, 1, '2026-01-06 16:38:45'),
(33, 1, '2026-01-06 17:37:10'),
(34, 1, '2026-01-06 21:59:49'),
(35, 1, '2026-01-06 22:24:19');

-- --------------------------------------------------------
CREATE TABLE `article_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(120) NOT NULL,
  `category_slug` varchar(140) NOT NULL,
  `icon_class` varchar(80) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO article_categories
(category_id, category_name, category_slug, icon_class, sort_order)
VALUES
(1, 'Webfejlesztés', 'webfejlesztes', 'fa-solid fa-code', 10),
(2, 'Backend', 'backend', 'fa-solid fa-server', 20),
(3, 'Frontend', 'frontend', 'fa-solid fa-palette', 30),
(4, 'Adatbázis', 'adatbazis', 'fa-solid fa-database', 40);

CREATE TABLE `articles` (
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `reading_minutes` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

INSERT INTO articles
(article_id, category_id, author_user_id, title, slug, summary, content, cover_image, reading_minutes, status)
VALUES
(1, 1, 1, 'Mi az AJAX és mikor érdemes használni?', 'mi-az-ajax-es-mikor-erdemes-hasznalni', 'Az AJAX lehetővé teszi, hogy az oldal frissítés nélkül kommunikáljon a szerverrel.', 'Az AJAX (Asynchronous JavaScript and XML) egy technika, amellyel a kliens aszinkron módon adatot kérhet le a szervertől.\n\nGyakori felhasználás:\n- chat rendszerek\n- űrlap validálás\n- értesítések',
  'uploads/articles/ajax.jpg', 6, 'published'),
(2, 2, 1, 'Session kezelés PHP-ben érthetően', 'session-kezeles-php-ben-erthetoen', 'A sessionök segítségével állapotot tudunk tárolni PHP alkalmazásokban.', 'A PHP session szerveroldali megoldás, amellyel a felhasználóhoz kötött adatokat kezelhetünk.\n\nTipikus használat:\n- bejelentkezés\n- kosár\n- jogosultság kezelés',
  'uploads/articles/php-session.jpg', 7, 'published'),
(3, 3, 3, 'CSS Grid vs Flexbox - mikor melyiket?', 'css-grid-vs-flexbox-mikor-melyiket', 'A Grid és a Flexbox nem egymás konkurensei, hanem másra valók.', 'A Flexbox egy dimenzióban dolgozik, míg a CSS Grid kétdimenziós elrendezést tesz lehetővé.\n\nFlexbox:\n- navbar\n- listák\n\nGrid:\n- layoutok',
  'uploads/articles/grid-flexbox.jpg', 5, 'published'),
(4, 4, 6, 'SQL JOIN-ek egyszerű példákkal', 'sql-join-ek-egyszeru-peldakkal', 'JOIN segítségével több tábla adatait kapcsolhatjuk össze.', 'A leggyakoribb JOIN típusok:\n\n- INNER JOIN\n- LEFT JOIN\n- RIGHT JOIN\n\nEzek alapjai nélkül nincs komoly SQL tudás.',
  'uploads/articles/sql-joins.jpg', 8, 'published'),
(5, 1, 7, 'MVC architektúra alapjai', 'mvc-architektura-alapjai', 'Az MVC segít átlátható és karbantartható alkalmazásokat készíteni.', 'Az MVC három részből áll:\n\nModel - adatkezelés\nView - megjelenítés\nController - vezérlés\n\nEz a minta skálázható rendszerek alapja.',
  'uploads/articles/mvc.jpg', 6, 'published');


--
-- Tábla szerkezet ehhez a táblához `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `user_message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_user_id`, `user_message`, `sent_at`, `is_read`) VALUES
(1, 2, 3, 'Hol lehet átvenni a terméket?', '2025-12-14 12:34:25', 1),
(2, 2, 6, 'Budapesten', '2025-12-14 12:36:15', 1),
(3, 3, 7, 'x', '2025-12-20 11:35:37', 1),
(4, 3, 7, 'o', '2025-12-20 11:36:13', 1),
(5, 3, 7, 'yooo', '2025-12-20 11:45:26', 1),
(6, 3, 1, 'okeybro', '2025-12-20 11:49:01', 1),
(7, 3, 1, 'mizu?', '2025-12-20 11:49:06', 1),
(8, 3, 7, 'na?', '2025-12-20 12:45:50', 1),
(9, 3, 7, 'yoo', '2025-12-20 12:47:09', 1),
(10, 3, 7, 'yoo', '2025-12-20 12:49:38', 1),
(11, 3, 7, 'hmm', '2025-12-20 12:49:56', 1),
(12, 3, 7, 'lol', '2025-12-20 12:57:52', 1),
(13, 3, 7, 'van', '2025-12-20 12:59:41', 1),
(14, 3, 7, 'ANYÁD', '2026-01-04 20:10:44', 1),
(15, 3, 7, 'yooo', '2026-01-04 20:10:49', 1),
(16, 3, 7, 'na?', '2026-01-04 20:18:48', 1),
(17, 3, 7, 'haloí??', '2026-01-04 20:25:28', 1),
(18, 3, 7, 'na?', '2026-01-04 20:25:46', 1),
(19, 3, 1, 'halo?', '2026-01-04 20:26:22', 1),
(20, 3, 1, 'halo?', '2026-01-04 20:32:31', 1),
(21, 3, 1, 'yoo?', '2026-01-04 20:32:38', 1),
(22, 3, 7, 'mi a szitu?', '2026-01-04 20:33:43', 1),
(23, 3, 1, 'semmi, veled?', '2026-01-04 20:34:43', 1),
(24, 3, 7, 'velem minden oké', '2026-01-04 20:35:10', 1),
(25, 3, 7, 'képzeld el, vettem valamit', '2026-01-04 20:38:12', 1),
(26, 3, 1, 'és mi az?', '2026-01-04 20:38:22', 1),
(27, 3, 7, 'ZSAMOOO', '2026-01-04 20:39:39', 1),
(28, 3, 7, 'najó', '2026-01-05 21:28:09', 1),
(29, 3, 7, 'najóq', '2026-01-05 21:28:23', 1),
(30, 3, 7, 'hmm', '2026-01-05 21:31:52', 1),
(31, 3, 7, 'na?', '2026-01-05 21:36:34', 1),
(32, 3, 7, 'végső tesz:D', '2026-01-05 21:39:57', 1),
(33, 3, 7, 'na???', '2026-01-05 21:40:23', 1),
(34, 3, 7, 'végső végső teszt', '2026-01-05 21:44:53', 1),
(35, 3, 7, 'valaki?', '2026-01-05 21:45:50', 1),
(36, 3, 7, 'újra', '2026-01-05 21:45:55', 1),
(37, 3, 7, 'hahó', '2026-01-05 21:48:02', 1),
(38, 3, 7, 'végre', '2026-01-05 21:48:15', 1),
(39, 3, 1, 'halóó', '2026-01-05 21:49:20', 1),
(40, 3, 7, 'vége', '2026-01-05 21:49:35', 1),
(41, 3, 7, 'újra', '2026-01-05 21:50:59', 1),
(42, 3, 1, 'hola?', '2026-01-05 21:51:16', 1),
(43, 3, 7, 'minden oké?', '2026-01-05 21:51:22', 1),
(44, 3, 7, 'na?', '2026-01-05 21:52:01', 1),
(45, 3, 7, 'kakukk', '2026-01-05 21:52:14', 1),
(46, 3, 7, 'micsoda?', '2026-01-05 21:54:50', 1),
(47, 3, 7, 'halo?', '2026-01-06 16:30:22', 1),
(48, 3, 7, 'baM', '2026-01-06 16:33:23', 1),
(49, 3, 7, 'valami?', '2026-01-06 16:34:36', 1),
(50, 3, 7, 'na most?', '2026-01-06 16:38:16', 1),
(51, 3, 7, 'újra', '2026-01-06 16:38:26', 1),
(52, 3, 1, 'rendben', '2026-01-06 16:39:03', 1),
(53, 3, 1, 'yo?', '2026-01-06 16:39:26', 1),
(54, 3, 7, 'halo', '2026-01-06 16:39:39', 1),
(55, 3, 7, 'na?', '2026-01-06 16:43:16', 1),
(56, 3, 7, 'most?', '2026-01-06 16:44:06', 1),
(57, 3, 7, 'halo?', '2026-01-06 16:46:06', 1),
(58, 3, 1, 'rendben', '2026-01-06 16:46:39', 1),
(59, 3, 1, 'hallooo', '2026-01-06 16:48:07', 1),
(60, 3, 1, 'van', '2026-01-06 16:48:48', 1),
(61, 3, 7, 'nincs', '2026-01-06 16:48:56', 1),
(62, 3, 7, 'halooo', '2026-01-06 16:53:36', 1),
(63, 3, 7, 'de??', '2026-01-06 16:53:51', 1),
(64, 3, 7, 'najó', '2026-01-06 17:10:48', 1),
(65, 3, 1, 'halo?', '2026-01-06 17:11:03', 1),
(66, 3, 7, 'most?', '2026-01-06 17:14:35', 1),
(67, 3, 7, 'hali?', '2026-01-06 17:15:17', 1),
(68, 3, 7, 'haloo', '2026-01-06 17:16:09', 1),
(69, 3, 1, 'kakukk', '2026-01-06 17:16:17', 1),
(70, 3, 1, 'mivan?', '2026-01-06 17:16:31', 1),
(71, 3, 7, 'na lássuk', '2026-01-06 17:19:36', 1),
(72, 3, 1, 'halo?', '2026-01-06 17:19:54', 1),
(73, 3, 7, 'oké?', '2026-01-06 17:36:18', 1),
(74, 3, 7, 'na?', '2026-01-06 17:36:39', 1),
(75, 3, 7, 'hoppá', '2026-01-06 17:37:32', 1),
(76, 3, 1, 'najó', '2026-01-06 17:37:39', 1),
(77, 3, 1, 'ezt figyeld', '2026-01-06 17:38:13', 1),
(78, 3, 1, 'cső', '2026-01-06 22:00:05', 1),
(79, 3, 1, 'asdsajbdks', '2026-01-06 22:00:25', 1),
(80, 7, 7, 'még eladó?', '2026-01-06 22:23:38', 1),
(81, 7, 1, 'igen', '2026-01-06 22:48:43', 1),
(82, 5, 7, 'halo', '2026-01-06 23:03:25', 0),
(83, 5, 7, 'jo', '2026-01-06 23:05:39', 0),
(84, 5, 7, 'hjh', '2026-01-06 23:05:47', 0),
(85, 4, 7, 'nyami', '2026-01-06 23:08:56', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `group_id`, `title`, `content`, `created_at`) VALUES
(1, 1, 1, 'Hogyan kezdjem el a webfejlesztést?', 'Sziasztok! Teljesen új vagyok a webfejlesztésben. Mivel érdemes kezdeni?', '2025-11-18 19:25:47'),
(2, 2, 2, 'Melyik a jobb kezdőknek: Python vagy C#?', 'Kezdőként gondolkodom, melyik nyelv lenne jobb? Mi a véleményetek?', '2025-11-18 19:25:47'),
(3, 1, 4, '2025 Tech trendjei', 'Összeszedtem pár érdekességet a 2025-ös év technológiai újításairól.', '2025-11-18 19:25:47');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `product_description` text NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `product_status` enum('active','sold','hidden') NOT NULL DEFAULT 'active',
  `pickup_location` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`product_id`, `seller_user_id`, `product_name`, `category`, `product_description`, `price`, `product_status`, `pickup_location`, `created_at`, `updated_at`) VALUES
(1, 1, 'Gaming Egér', 'gaming', 'RGB világítású gamer egér, 6 programozható gombbal.', 8990.00, 'active', 'Budapest', '2025-12-14 11:53:39', NULL),
(2, 1, 'Bluetooth Hangszóró', 'hangtechnika', 'Vízálló hordozható Bluetooth hangszóró erős basszussal.', 12990.00, 'active', 'Debrecen', '2025-12-14 11:53:39', NULL),
(3, 1, 'Okosóra', 'okosórák', 'Pulzusmérős okosóra több mint 20 sportmóddal.', 19990.00, 'active', 'Szeged', '2025-12-14 11:53:39', NULL),
(4, 1, 'Laptop', 'laptopok', 'Kiváló minőségű laptop.', 149900.00, 'active', 'Budapest XI. kerület', '2025-12-14 11:53:39', NULL),
(5, 1, 'Fényképezőgép', 'fényképezőgépek', 'Nagy látószögű precíz kamera.', 49900.00, 'active', 'Postázás', '2025-12-14 11:53:39', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `seller_user_id` int(11) NOT NULL,
  `buyer_user_id` int(11) NOT NULL,
  `deal_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `review_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

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
  `profile_image` varchar(255) NOT NULL DEFAULT 'images/anonymous.png',
  `total_posts` int(11) DEFAULT 0,
  `total_comments` int(11) DEFAULT 0,
  `sold_items` int(11) DEFAULT 0,
  `bought_items` int(11) DEFAULT 0,
  `avg_rating` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `user_password`, `is_active`, `registration_date`, `user_role`, `ip`, `activation_code`, `profile_image`, `total_posts`, `total_comments`, `sold_items`, `bought_items`, `avg_rating`) VALUES
(1, 'kistamáska', 'bertagyorgy@gmail.com', '$2y$10$kyug83mm.Kc5/ieyX5G3JOx/kRYCt753zQezR2YAe51r7Fe.AVqUO', 'A', '2025-11-11 18:57:24', 'A', '::1', '', './uploads/profile_images/profile_1.jpg', 0, 0, 0, 0, 0.00),
(2, 'kunbéla', 'kunbela17@gmail.com', '$2y$10$er./MPIDc3o41MWuUpV.0.zZft3q/s1fEP8G9IDu8CShqfGYlmYm.', 'A', '2025-11-12 20:03:21', 'F', '::1', '', './images/anonymous.png', 0, 0, 0, 0, 0.00),
(3, 'bertagyorgy', 'bertagyorgy222@gmail.com', '$2y$10$wL5BJkrX8vpjXZKeWemdEu2y3nPUB2kR1bHYwsrYeJdPR1jSYome2', 'P', '2025-11-12 20:11:19', 'F', '::1', 'a685256573e5ccbffda4ae4d5463d1da', './images/anonymous.png', 0, 0, 0, 0, 0.00),
(5, 'proba123', 'proba123@gmail.com', '$2y$10$jbYznXaB9ZWM8NoE1x.PfurjeM86wPmwsvOfumFU3aBJhauvDFRIy', 'A', '2025-11-12 21:09:56', 'F', '::1', '', './images/anonymous.png', 0, 0, 0, 0, 0.00),
(6, 'kisalma', 'kisalma4378@gmail.com', '$2y$10$vDhjwSaSz3705UpDjKqzheTHHDGV0mC0Lty107/HhReeaohMy3el6', 'A', '2025-11-16 21:50:38', 'F', '::1', NULL, './uploads/profile_images/profile_6.jpg', 0, 0, 0, 0, 0.00),
(7, 'admin', 'papmd2014@gmail.com', '$2y$10$kyug83mm.Kc5/ieyX5G3JOx/kRYCt753zQezR2YAe51r7Fe.AVqUO', 'A', '2025-12-14 12:00:18', 'A', '::1', NULL, './uploads/profile_images/profile_7_1766229028.jpg', 0, 0, 0, 0, 0.00);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_badges`
--

CREATE TABLE `user_badges` (
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_hungarian_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`);

--
-- A tábla indexei `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- A tábla indexei `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`);

--
-- A tábla indexei `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- A tábla indexei `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`buyer_user_id`);

--
-- A tábla indexei `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`deal_id`);

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
-- A tábla indexei `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- A tábla indexei `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- A tábla indexei `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `deal_id` (`deal_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- A tábla indexei `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`user_id`,`badge_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `article-categories`
--
ALTER TABLE `article_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `deals`
--
ALTER TABLE `deals`
  MODIFY `deal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT a táblához `login`
--
ALTER TABLE `login`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT a táblához `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT a táblához `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;