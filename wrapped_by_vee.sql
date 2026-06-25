-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2026 at 06:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wrapped_by_vee`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'Admin Login', 'Successful login', '::1', '2026-06-20 10:31:42'),
(2, 1, 'Admin Login', 'Successful login', '::1', '2026-06-20 10:38:22'),
(3, 1, 'Admin Login', 'Successful login', '172.20.10.1', '2026-06-20 10:48:25'),
(4, 1, 'Admin Login', 'Successful login', '172.20.10.1', '2026-06-20 10:51:21'),
(5, 1, 'Admin Login', 'Successful login', '::1', '2026-06-20 11:04:01'),
(6, 1, 'Admin Login', 'Successful login', '::1', '2026-06-20 12:05:42'),
(7, 1, 'Admin Login', 'Successful login', '::1', '2026-06-20 12:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_email` varchar(100) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_email`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin@wrappedbyvee.gmail.com', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 16:58:45'),
(2, 'admin@wrappedbyvee.gmail.com', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:00:14'),
(3, 'admin', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:05:18'),
(4, 'admin', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:05:31'),
(5, 'admin', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:09:48'),
(6, 'admin', 'login_failed', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:09:58'),
(7, 'admin', 'login_success', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-23 17:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_locked` tinyint(1) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_login_attempt` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verify_token` varchar(255) DEFAULT NULL,
  `email_verify_expires` datetime DEFAULT NULL,
  `temp_new_email` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `created_at`, `updated_at`, `account_locked`, `locked_until`, `login_attempts`, `last_login_attempt`, `email_verified`, `email_verify_token`, `email_verify_expires`, `temp_new_email`, `reset_token`, `reset_expires`) VALUES
(1, 'admin', 'admin@wrappedbyvee.com', '$2a$12$Uy7ur9qpd0LDPmXB2JIz2uQPploCbBk512z41xpQxUtfl7OgbReby', 'Administrator', 'admin', 1, '2026-06-20 10:31:31', '2026-06-25 10:45:47', 0, NULL, 0, '2026-06-20 13:51:07', 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branding_settings`
--

CREATE TABLE `branding_settings` (
  `id` int(11) NOT NULL,
  `primary_color` varchar(20) DEFAULT '#C2697E',
  `secondary_color` varchar(20) DEFAULT '#FFE0EC',
  `welcome_message` text DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branding_settings`
--

INSERT INTO `branding_settings` (`id`, `primary_color`, `secondary_color`, `welcome_message`, `favicon`, `created_at`, `updated_at`) VALUES
(1, '#C2697E', '#FFE0EC', 'Welcome to Wrapped by Vee Admin Panel', NULL, '2026-06-20 10:32:45', '2026-06-20 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `business_settings`
--

CREATE TABLE `business_settings` (
  `id` int(11) NOT NULL,
  `business_name` varchar(255) DEFAULT 'Wrapped by Vee',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `business_logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_settings`
--

INSERT INTO `business_settings` (`id`, `business_name`, `email`, `phone`, `whatsapp`, `address`, `facebook`, `instagram`, `twitter`, `linkedin`, `business_logo`, `created_at`, `updated_at`) VALUES
(1, 'Wrapped by Vee', 'info@wrappedbyvee.com', '+255 712 345 678', '+255 712 345 678', 'Dodoma, Tanzania', '', '', '', '', '', '2026-06-20 10:37:34', '2026-06-20 10:37:34');

-- --------------------------------------------------------

--
-- Table structure for table `custom_requests`
--

CREATE TABLE `custom_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `location` text NOT NULL,
  `vision` text NOT NULL,
  `is_gift` tinyint(1) DEFAULT 0,
  `recipient_name` varchar(100) DEFAULT NULL,
  `gift_message` text DEFAULT NULL,
  `contact_method` varchar(50) DEFAULT 'Phone Call',
  `images` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_requests`
--

INSERT INTO `custom_requests` (`id`, `name`, `phone`, `request_type`, `location`, `vision`, `is_gift`, `recipient_name`, `gift_message`, `contact_method`, `images`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sylvia louis Salu', '0688054087', 'Flowers', 'ARUSHA', 'blurryyy', 1, 'hhhh', 'i love youuu', 'Phone Call', '[\"uploads\\/custom\\/1782230758_6a3aaee648342_0.jpg\"]', 'Pending', '2026-06-23 16:05:58', NULL),
(2, 'Sylvia louis Salu', '0688054087', 'Flowers', 'ARUSHA', 'brurrr', 1, 'hhhh', 'bb', 'WhatsApp', '[\"uploads\\/custom\\/1782231444_6a3ab194ee752_0.jpg\"]', 'Pending', '2026-06-23 16:17:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_settings`
--

CREATE TABLE `delivery_settings` (
  `id` int(11) NOT NULL,
  `base_delivery_fee` decimal(10,2) DEFAULT 5000.00,
  `free_delivery_threshold` decimal(10,2) DEFAULT 60000.00,
  `same_day_cutoff_time` varchar(10) DEFAULT '14:00',
  `delivery_instructions` text DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_settings`
--

INSERT INTO `delivery_settings` (`id`, `base_delivery_fee`, `free_delivery_threshold`, `same_day_cutoff_time`, `delivery_instructions`, `contact_phone`, `created_at`, `updated_at`) VALUES
(1, 5000.00, 60000.00, '14:00', 'Our delivery team will contact you to confirm delivery time. Same-day delivery available for orders placed before 2:00 PM.', '+255 712 345 678', '2026-06-20 10:25:33', '2026-06-20 10:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

CREATE TABLE `delivery_zones` (
  `id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `regions` text NOT NULL,
  `cities_towns` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_days` varchar(50) DEFAULT '1-2 days',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_zones`
--

INSERT INTO `delivery_zones` (`id`, `zone_name`, `regions`, `cities_towns`, `delivery_fee`, `estimated_days`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Dar es Salaam City', 'Dar es Salaam', 'Kinondoni,Ilala,Temeke,Ubungo,Kigamboni', 3000.00, 'Same day (2-4 hours)', 1, 1, '2026-06-20 11:01:44'),
(2, 'Dar es Salaam Suburbs', 'Dar es Salaam', 'Mbezi Beach,Tegeta,Bunju,Goba', 5000.00, 'Same day', 1, 2, '2026-06-20 11:01:44'),
(3, 'Arusha City', 'Arusha', 'Arusha City,Njiro,Sakina,Themi', 8000.00, '1 day', 1, 3, '2026-06-20 11:01:44'),
(4, 'Kilimanjaro Region', 'Kilimanjaro', 'Moshi,Rombo,Marangu,Himo', 10000.00, '1-2 days', 1, 4, '2026-06-20 11:01:44'),
(5, 'Mwanza City', 'Mwanza', 'Mwanza City,Ilemela,Nyamagana', 12000.00, '2 days', 1, 5, '2026-06-20 11:01:44'),
(6, 'Dodoma City', 'Dodoma', 'Dodoma City,Makutupora,Ihumwa', 7000.00, '1 day', 1, 6, '2026-06-20 11:01:44'),
(7, 'Morogoro City', 'Morogoro', 'Morogoro City,Mikumi,Turiani', 8000.00, '1-2 days', 1, 7, '2026-06-20 11:01:44'),
(8, 'Tanga City', 'Tanga', 'Tanga City,Pangani,Muheza', 10000.00, '1-2 days', 1, 8, '2026-06-20 11:01:44'),
(9, 'Zanzibar Urban', 'Unguja', 'Zanzibar City,Mbweni,Chukwani', 25000.00, '2-3 days', 1, 9, '2026-06-20 11:01:44'),
(10, 'Pemba Island', 'Pemba', 'Wete,Chake-Chake,Mkoani', 28000.00, '3-4 days', 1, 10, '2026-06-20 11:01:44'),
(11, 'Dodoma CBD', '', NULL, 0.00, 'Same day', 1, 0, '2026-06-21 06:48:02'),
(12, 'Dodoma Outskirts', '', NULL, 5000.00, '1-2 days', 1, 0, '2026-06-21 06:48:02'),
(13, 'Dar es Salaam', '', NULL, 5000.00, '2-3 days', 1, 0, '2026-06-21 06:48:02'),
(15, 'Mwanza', '', NULL, 18000.00, '3-4 days', 1, 0, '2026-06-21 06:48:02'),
(16, 'Other Regions', '', NULL, 20000.00, '3-5 days', 1, 0, '2026-06-21 06:48:02'),
(17, 'Dodoma CBD', '', NULL, 0.00, 'Same day', 1, 0, '2026-06-21 06:54:17'),
(18, 'Dodoma Outskirts', '', NULL, 5000.00, '1-2 days', 1, 0, '2026-06-21 06:54:17'),
(19, 'Dar es Salaam', '', NULL, 15000.00, '2-3 days', 1, 0, '2026-06-21 06:54:17'),
(21, 'Mwanza', '', NULL, 18000.00, '3-4 days', 1, 0, '2026-06-21 06:54:17'),
(22, 'Tanga', '', NULL, 10000.00, '2-3 days', 1, 0, '2026-06-21 06:54:17'),
(23, 'Morogoro', '', NULL, 8000.00, '2-3 days', 1, 0, '2026-06-21 06:54:17'),
(24, 'Zanzibar', '', NULL, 25000.00, '3-5 days', 1, 0, '2026-06-21 06:54:17'),
(25, 'moshi', '', NULL, 5000.00, '2-3 days', 1, 0, '2026-06-21 10:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `email_change_logs`
--

CREATE TABLE `email_change_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `old_email` varchar(255) DEFAULT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT 'Other',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `description`, `amount`, `category`, `note`, `created_at`) VALUES
(1, 'Flower supplies', 25000.00, 'Supplies', 'Bought fresh roses and lilies', '2026-06-21 09:06:45'),
(2, 'Delivery fuel', 8000.00, 'Delivery', 'Fuel for deliveries', '2026-06-21 09:06:45'),
(3, 'Shop rent', 50000.00, 'Rent', 'Monthly rent payment', '2026-06-21 09:06:45'),
(4, 'Marketing materials', 12000.00, 'Marketing', 'Flyers and business cards', '2026-06-21 09:06:45'),
(5, 'Staff salary', 30000.00, 'Salaries', 'Part-time staff payment', '2026-06-21 09:06:45'),
(6, 'Ribbons', 3000.00, 'Utilities', NULL, '2026-06-21 09:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homepage_settings`
--

CREATE TABLE `homepage_settings` (
  `id` int(11) NOT NULL,
  `hero_title` varchar(255) DEFAULT 'Wrapped by Vee',
  `hero_description` text DEFAULT NULL,
  `hero_badge_text` varchar(255) DEFAULT 'New arrivals - Season''s finest blooms',
  `hero_title_text` varchar(255) DEFAULT 'Every bloom tells a story',
  `hero_tagline` varchar(255) DEFAULT '"Where flowers tell stories"',
  `hero_location` varchar(255) DEFAULT 'Handcrafted with love in Dodoma, Tanzania',
  `hero_image` varchar(255) DEFAULT NULL,
  `quote_text` text DEFAULT 'Flowers are the music of the ground. From earth\'s lips, spoken without sound.',
  `quote_author` varchar(100) DEFAULT 'Edwin Curran',
  `features_title` varchar(255) DEFAULT 'Why Wrapped by Vee',
  `features_subtitle` varchar(255) DEFAULT 'The Wrapped by Vee difference',
  `testimonial_1_name` varchar(100) DEFAULT 'Amina T.',
  `testimonial_1_text` text DEFAULT 'The bouquet arrived looking like it had been photographed for a magazine. My mother cried happy tears.',
  `testimonial_1_location` varchar(100) DEFAULT 'Dodoma',
  `testimonial_2_name` varchar(100) DEFAULT 'David M.',
  `testimonial_2_text` text DEFAULT 'Ordered the Golden Hour gift set for my anniversary. Vee went above and beyond.',
  `testimonial_2_location` varchar(100) DEFAULT 'Dodoma',
  `testimonial_3_name` varchar(100) DEFAULT 'Rehema K.',
  `testimonial_3_text` text DEFAULT 'Used Wrapped by Vee for our corporate event centrepieces. Professional and breathtaking.',
  `testimonial_3_location` varchar(100) DEFAULT 'Dodoma',
  `footer_tagline` varchar(255) DEFAULT '"Where flowers tell stories"',
  `footer_location` varchar(255) DEFAULT 'Handcrafted in Dodoma, Tanzania',
  `announcement_text` varchar(255) DEFAULT 'Free delivery on orders over TZS 60,000 in Dodoma',
  `announcement_link_text` varchar(100) DEFAULT 'Shop now',
  `scroll_banner_items` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_settings`
--

INSERT INTO `homepage_settings` (`id`, `hero_title`, `hero_description`, `hero_badge_text`, `hero_title_text`, `hero_tagline`, `hero_location`, `hero_image`, `quote_text`, `quote_author`, `features_title`, `features_subtitle`, `testimonial_1_name`, `testimonial_1_text`, `testimonial_1_location`, `testimonial_2_name`, `testimonial_2_text`, `testimonial_2_location`, `testimonial_3_name`, `testimonial_3_text`, `testimonial_3_location`, `footer_tagline`, `footer_location`, `announcement_text`, `announcement_link_text`, `scroll_banner_items`, `created_at`, `updated_at`) VALUES
(1, 'Wrapped by Vee', 'Luxury Florals & Gift Wrapping', 'New arrivals - Season\'s finest blooms', 'Every bloom tells a story', '\"Where flowers tell stories\"', 'Handcrafted with love in Dodoma, Tanzania', NULL, 'Flowers are the music of the ground. From earth\'s lips, spoken without sound.', 'Edwin Curran', 'Why Wrapped by Vee', 'The Wrapped by Vee difference', 'Amina T.', 'The bouquet arrived looking like it had been photographed for a magazine. My mother cried happy tears.', 'Dodoma', 'David M.', 'Ordered the Golden Hour gift set for my anniversary. Vee went above and beyond.', 'Dodoma', 'Rehema K.', 'Used Wrapped by Vee for our corporate event centrepieces. Professional and breathtaking.', 'Dodoma', '\"Where flowers tell stories\"', 'Handcrafted in Dodoma, Tanzania', 'Free delivery on orders over TZS 60,000 in Dodoma', 'Shop now', '? Fresh Bouquets Daily ? Same-Day Dodoma Delivery ? Handcrafted with Love ? Custom Gift Wrapping ? Weddings & Events ? Corporate Orders Welcome', '2026-06-20 10:25:07', '2026-06-20 10:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`id`, `name`, `amount`, `category`, `source`, `note`, `created_at`) VALUES
(1, 'tuition', 50000.00, 'Workshop', 'Bank Transfer', '', '2026-06-21 08:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT '#C2697E',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `unit` varchar(50) DEFAULT 'piece',
  `cost_price` decimal(12,2) DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'adjust',
  `quantity` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `localization_settings`
--

CREATE TABLE `localization_settings` (
  `id` int(11) NOT NULL,
  `language` varchar(10) DEFAULT 'en',
  `currency` varchar(10) DEFAULT 'TZS',
  `currency_symbol` varchar(10) DEFAULT 'TZS',
  `timezone` varchar(50) DEFAULT 'Africa/Dar_es_Salaam',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `localization_settings`
--

INSERT INTO `localization_settings` (`id`, `language`, `currency`, `currency_symbol`, `timezone`, `created_at`, `updated_at`) VALUES
(1, 'en', 'TZS', 'TZS', 'Africa/Dar_es_Salaam', '2026-06-20 10:32:45', '2026-06-20 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `success` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `username`, `success`, `created_at`) VALUES
(1, '::1', 'admin', 0, '2026-06-20 10:28:25'),
(2, '::1', 'admin', 0, '2026-06-20 10:29:40'),
(3, '::1', 'admin', 0, '2026-06-20 10:29:46'),
(4, '::1', 'admin', 0, '2026-06-20 10:29:56'),
(5, '::1', 'admin', 0, '2026-06-20 10:30:08'),
(6, '::1', 'admin', 1, '2026-06-20 10:31:42'),
(7, '::1', 'admin', 1, '2026-06-20 10:38:22'),
(8, '172.20.10.1', 'admin', 0, '2026-06-20 10:48:01'),
(9, '172.20.10.1', 'admin', 1, '2026-06-20 10:48:25'),
(10, '172.20.10.1', 'admin', 0, '2026-06-20 10:51:07'),
(11, '172.20.10.1', 'admin', 1, '2026-06-20 10:51:21'),
(12, '::1', 'admin', 1, '2026-06-20 11:04:01'),
(13, '::1', 'admin', 1, '2026-06-20 12:05:42'),
(14, '::1', 'admin', 1, '2026-06-20 12:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `admin_id`, `ip_address`, `user_agent`, `login_time`) VALUES
(1, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 10:31:42'),
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 10:38:22'),
(3, 1, '172.20.10.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1', '2026-06-20 10:48:25'),
(4, 1, '172.20.10.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1', '2026-06-20 10:51:21'),
(5, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 11:04:01'),
(6, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-20 12:05:42'),
(7, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 12:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `order_email` tinyint(1) DEFAULT 1,
  `payment_email` tinyint(1) DEFAULT 1,
  `low_stock_alert` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `order_email`, `payment_email`, `low_stock_alert`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-06-20 10:32:45', '2026-06-20 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `occasion_types`
--

CREATE TABLE `occasion_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `occasion_types`
--

INSERT INTO `occasion_types` (`id`, `name`, `slug`, `icon`, `created_at`) VALUES
(1, 'Birthday', 'birthday', '??', '2026-06-20 10:32:45'),
(2, 'Anniversary', 'anniversary', '??', '2026-06-20 10:32:45'),
(3, 'Wedding', 'wedding', '??', '2026-06-20 10:32:45'),
(4, 'Sympathy', 'sympathy', '???', '2026-06-20 10:32:45'),
(5, 'Just Because', 'just-because', '??', '2026-06-20 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'M-Pesa',
  `total_amount` decimal(12,2) NOT NULL,
  `gift_wrap_selected` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Pending',
  `tracking_status` varchar(50) DEFAULT 'Pending',
  `payment_status` varchar(50) DEFAULT 'Pending',
  `items` text DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `client_name`, `client_email`, `address`, `phone`, `payment_method`, `total_amount`, `gift_wrap_selected`, `status`, `tracking_status`, `payment_status`, `items`, `delivery_notes`, `payment_proof`, `created_at`) VALUES
(1, NULL, 'Sylvia louis Salu', 'salusylvia02@gmail.com', 'ARUSHA', '0688054087', 'M-Pesa', 103300.00, 1, 'Pending', 'Pending', 'Pending', '[{\"id\":2,\"name\":\"Eternal Silk Roses\",\"price\":85000,\"qty\":1}]', 'Delivery Zone: Dar es Salaam City\nZone ID: 1\nDelivery Fee: TZS 3,000\nGift Message: hhhhhhhhhhhhhhh', 'uploads/proofs/1781953401_Cutewallpaper.jfif', '2026-06-20 11:03:21'),
(2, NULL, 'Sylvia', '', 'Arusha', '0748147625', 'M-Pesa', 58100.00, 1, 'Pending', 'Pending', 'Pending', '[{\"id\":1,\"name\":\"Fresh Red Roses Bouquet\",\"price\":45000,\"qty\":1}]', 'Delivery Zone: Dar es Salaam Suburbs\nZone ID: 2\nDelivery Fee: TZS 5,000\nGift Message: I love youuu', 'uploads/proofs/1781954048_6a367600278d9.jpeg', '2026-06-20 11:14:08'),
(3, NULL, 'INSTITUTE OF ACCOUNTANCY ARUSHA', 'julius@gmail.com', 'ARUSHA', '0688054087', 'M-Pesa', 58100.00, 1, 'Pending', 'Processing', 'Paid', '[{\"id\":1,\"name\":\"Fresh Red Roses Bouquet\",\"price\":45000,\"qty\":1}]', 'Delivery Zone: Dar es Salaam Suburbs\nZone ID: 2\nDelivery Fee: TZS 5,000\nGift Message: i misss you ', 'uploads/payments/1781955240_6a367aa8c0646.png', '2026-06-20 11:34:00'),
(4, NULL, 'Sylvia louis Salu', NULL, 'ARUSHA', '0688054087', 'M-Pesa', 69900.00, 1, 'Pending', 'Pending', 'Pending', 'White Lily Elegance x1 (TZS 55,000)', 'Delivery Zone: moshi\nZone ID: 25\nDelivery Fee: TZS 5,000\nPayment Method: M-Pesa\n\n--- Gift Information ---\nRecipient Name: Sylvia louis Salu\nRecipient Phone: 0688054087\nRecipient Address: ARUSHA\nGift Message: i love youuu\n', 'uploads/proofs/1782230036_6a3aac14a5570.png', '2026-06-23 15:53:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_instructions`
--

CREATE TABLE `payment_instructions` (
  `id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `instruction_text` text DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `main_category` varchar(50) NOT NULL,
  `sub_category` varchar(50) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `tax_mpesa` decimal(10,2) DEFAULT 0.00,
  `tax_bank` decimal(10,2) DEFAULT 0.00,
  `image_url` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `main_category`, `sub_category`, `price`, `tax_mpesa`, `tax_bank`, `image_url`, `description`, `is_active`, `featured`, `created_at`) VALUES
(2, 'White Lily Elegance', 'Flowers', 'Flowers', 'Fresh Flowers', 55000.00, 0.00, 0.00, 'uploads/products/1782030296_6a379fd88fe8d.jpeg', 'Pure white lilies arranged with eucalyptus leaves for a sophisticated, elegant look. Perfect for weddings and formal events.', 1, 1, '2026-06-21 06:57:00');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, 'Flowers', 'flowers', 1, '2026-06-20 10:32:45'),
(2, 'Gift Packages', 'gift-packages', 2, '2026-06-20 10:32:45'),
(3, 'Decorations', 'decorations', 3, '2026-06-20 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_subcategories`
--

CREATE TABLE `product_subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'homepage_title', 'Wrapped by Vee', 'homepage', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(2, 'homepage_tagline', 'Beautiful Gift Wrapping & Floral Arrangements', 'homepage', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(3, 'homepage_hero_image', 'assets/images/hero.jpg', 'homepage', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(4, 'homepage_about_text', 'We create beautiful gift wrapping and floral arrangements for all occasions. Our team of creative designers will make your gift unforgettable.', 'homepage', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(5, 'homepage_phone', '+255 755 555 555', 'contact', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(6, 'homepage_email', 'info@wrappedbyvee.com', 'contact', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(7, 'homepage_address', 'Dar es Salaam, Tanzania', 'contact', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(8, 'site_name', 'Wrapped', 'general', '2026-06-21 06:46:09', '2026-06-21 09:13:27'),
(9, 'site_description', 'Premium gift wrapping and floral arrangements in Tanzania', 'general', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(10, 'tax_rate', '18', 'general', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(11, 'currency', 'TZS', 'general', '2026-06-21 06:46:09', '2026-06-21 06:46:09'),
(12, 'business_logo', '', 'business', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(13, 'business_name', 'Wrapped by Vee', 'business', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(14, 'business_phone', '+255 755 555 555', 'business', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(15, 'business_email', 'info@wrappedbyvee.com', 'business', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(16, 'business_address', 'Dodoma, Tanzania', 'business', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(17, 'free_delivery_threshold', '50000', 'delivery', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(18, 'delivery_base_fee', '5000', 'delivery', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(19, 'delivery_time', '2-3 business days', 'delivery', '2026-06-21 06:47:48', '2026-06-21 06:47:48'),
(20, 'hero_badge_text', 'New arrivals Season\'s finest blooms', 'homepage', '2026-06-21 06:47:50', '2026-06-21 09:23:29'),
(21, 'hero_title_text', 'Every bloom tells a story', 'homepage', '2026-06-21 06:47:50', '2026-06-25 12:05:16'),
(22, 'hero_tagline', '\"Where flowers tell stories”', 'homepage', '2026-06-21 06:47:50', '2026-06-21 09:25:20'),
(23, 'hero_location', 'Handcrafted with love in Dodoma, Tanzania', 'homepage', '2026-06-21 06:47:50', '2026-06-21 09:25:20'),
(24, 'features_title', 'Curated Collections', 'homepage', '2026-06-21 06:47:50', '2026-06-21 09:25:20'),
(25, 'features_subtitle', 'From quiet love notes to grand celebrations', 'homepage', '2026-06-21 06:47:50', '2026-06-21 09:25:20'),
(26, 'quote_text', 'Flowers are the music of the ground. From earth\'s lips, spoken without sound.', 'homepage', '2026-06-21 06:47:50', '2026-06-21 06:47:50'),
(27, 'quote_author', 'Edwin Curran', 'homepage', '2026-06-21 06:47:50', '2026-06-21 06:47:50'),
(28, 'scroll_banner_items', '? Fresh Bouquets Daily ? Same-Day Dodoma Delivery ? Handcrafted with Love ? Custom Gift Wrapping ? Weddings & Events ? Corporate Orders Welcome', 'homepage', '2026-06-21 06:47:50', '2026-06-21 06:47:50'),
(29, 'brand_name', 'Wrapped by Vee', 'branding', '2026-06-21 09:12:04', '2026-06-21 09:12:04'),
(30, 'brand_tagline', 'Where flowers tell issuess', 'branding', '2026-06-21 09:12:04', '2026-06-21 09:12:21'),
(31, 'brand_color', '#c2697e', 'branding', '2026-06-21 09:12:04', '2026-06-21 09:12:04'),
(32, 'brand_logo', 'uploads/branding/logo_1782033124.jpeg', 'branding', '2026-06-21 09:12:04', '2026-06-21 09:12:04'),
(38, 'site_email', 'info@wrappedbyvee.com', 'general', '2026-06-21 09:13:27', '2026-06-21 09:13:27'),
(39, 'site_phone', '+255 755 555 555', 'general', '2026-06-21 09:13:27', '2026-06-21 09:13:27'),
(40, 'site_address', 'Dodoma, Tanzania', 'general', '2026-06-21 09:13:27', '2026-06-21 09:13:27'),
(50, 'footer_tagline', '\"Where flowers tell stories\"', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(51, 'footer_location', 'Handcrafted in Dodoma, Tanzania', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(52, 'testimonial_1_text', 'The bouquet arrived looking like it had been photographed for a magazine. My mother cried happy tears.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(53, 'testimonial_1_name', 'Amina T.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(54, 'testimonial_1_location', 'Kigoma', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:25:20'),
(55, 'testimonial_2_text', 'Ordered the Golden Hour gift set for my anniversary. Vee went above and beyond.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(56, 'testimonial_2_name', 'David M.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(57, 'testimonial_2_location', 'Dodoma', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(58, 'testimonial_3_text', 'Used Wrapped by Vee for our corporate event centrepieces. Professional and breathtaking.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(59, 'testimonial_3_name', 'Rehema K.', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:23:29'),
(60, 'testimonial_3_location', 'Dar es Salaam', 'homepage', '2026-06-21 09:23:29', '2026-06-21 09:25:20'),
(129, 'mpesa_enabled', '1', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(130, 'mpesa_phone', '+255 742 035 952', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(131, 'mpesa_tax', '0', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(132, 'bank_enabled', '1', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(133, 'bank_name', 'SELCOM BANK ', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(134, 'bank_account', ' 0123456789 ', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(135, 'bank_account_name', 'VANESSA J. MOSHA', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(136, 'bank_tax', '18', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(137, 'default_tax', '18', 'payment', '2026-06-25 12:05:47', '2026-06-25 12:05:47'),
(138, 'facebook', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(139, 'instagram', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(140, 'twitter', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(141, 'pinterest', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(142, 'youtube', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(143, 'tiktok', '', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56'),
(144, 'whatsapp', '+255 742 035 952', 'social', '2026-06-25 12:05:56', '2026-06-25 12:05:56');

-- --------------------------------------------------------

--
-- Table structure for table `store_settings`
--

CREATE TABLE `store_settings` (
  `id` int(11) NOT NULL,
  `store_name` varchar(255) DEFAULT 'Wrapped by Vee',
  `store_phone` varchar(50) DEFAULT NULL,
  `store_address` text DEFAULT NULL,
  `store_whatsapp` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_settings`
--

INSERT INTO `store_settings` (`id`, `store_name`, `store_phone`, `store_address`, `store_whatsapp`, `created_at`, `updated_at`) VALUES
(1, 'Wrapped by Vee', '+255 712 345 678', 'Dodoma, Tanzania', '+255 712 345 678', '2026-06-20 10:32:47', '2026-06-20 10:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_email` (`admin_email`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `branding_settings`
--
ALTER TABLE `branding_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `business_settings`
--
ALTER TABLE `business_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_requests`
--
ALTER TABLE `custom_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `delivery_settings`
--
ALTER TABLE `delivery_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_change_logs`
--
ALTER TABLE `email_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_settings`
--
ALTER TABLE `homepage_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `localization_settings`
--
ALTER TABLE `localization_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `occasion_types`
--
ALTER TABLE `occasion_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_instructions`
--
ALTER TABLE `payment_instructions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_method` (`payment_method`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_subcategories`
--
ALTER TABLE `product_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `store_settings`
--
ALTER TABLE `store_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `website_content`
--
ALTER TABLE `website_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page` (`page`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branding_settings`
--
ALTER TABLE `branding_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_settings`
--
ALTER TABLE `business_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `custom_requests`
--
ALTER TABLE `custom_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_settings`
--
ALTER TABLE `delivery_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `email_change_logs`
--
ALTER TABLE `email_change_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homepage_settings`
--
ALTER TABLE `homepage_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `localization_settings`
--
ALTER TABLE `localization_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `occasion_types`
--
ALTER TABLE `occasion_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_instructions`
--
ALTER TABLE `payment_instructions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_subcategories`
--
ALTER TABLE `product_subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_tags`
--
ALTER TABLE `product_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `store_settings`
--
ALTER TABLE `store_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_change_logs`
--
ALTER TABLE `email_change_logs`
  ADD CONSTRAINT `email_change_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_subcategories`
--
ALTER TABLE `product_subcategories`
  ADD CONSTRAINT `product_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
