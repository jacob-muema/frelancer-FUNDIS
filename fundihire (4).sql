-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 05:25 PM
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
-- Database: `fundihire`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `client_job_reports`
-- (See below for the actual view)
--
CREATE TABLE `client_job_reports` (
`id` int(11)
,`title` varchar(100)
,`description` text
,`location` varchar(100)
,`budget` decimal(10,2)
,`status` enum('open','assigned','completed','cancelled')
,`payment_status` enum('unpaid','paid')
,`is_featured` tinyint(1)
,`created_at` timestamp
,`completion_date` datetime
,`client_name` varchar(255)
,`applications_count` bigint(21)
,`client_rating` decimal(14,4)
,`fees_paid` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `fundi_job_reports`
-- (See below for the actual view)
--
CREATE TABLE `fundi_job_reports` (
`id` int(11)
,`title` varchar(100)
,`location` varchar(100)
,`budget` decimal(10,2)
,`status` enum('open','assigned','completed','cancelled')
,`created_at` timestamp
,`completion_date` datetime
,`client_name` varchar(255)
,`bid_amount` decimal(10,2)
,`application_status` enum('pending','accepted','rejected')
,`application_date` timestamp
,`fundi_rating` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `fundi_skills`
--

CREATE TABLE `fundi_skills` (
  `id` int(11) NOT NULL,
  `fundi_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `experience_years` decimal(3,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fundi_skills`
--

INSERT INTO `fundi_skills` (`id`, `fundi_id`, `skill_id`, `experience_years`) VALUES
(12, 31, 1, NULL),
(13, 31, 2, NULL),
(14, 31, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(100) NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `status` enum('open','assigned','completed','cancelled') DEFAULT 'open',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `completion_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `client_id`, `title`, `description`, `location`, `budget`, `status`, `payment_status`, `is_featured`, `completion_date`, `created_at`, `updated_at`) VALUES
(1, 27, '3 bedroom', '3 bedroom house', 'Nairobi', 120000.00, 'completed', 'paid', 0, NULL, '2025-03-21 00:15:08', '2025-03-30 04:49:19'),
(2, 27, 'Nataka fundi wa malngo', 'fundi wa mlango exp 4-5years', 'Kitui', 23000.00, 'completed', 'paid', 0, NULL, '2025-03-21 01:02:15', '2025-03-30 04:48:58'),
(3, 27, 'bed making', 'i want a sereous bed', 'Kitui', 19000.00, 'completed', 'paid', 0, NULL, '2025-03-21 07:59:28', '2025-03-29 21:12:36'),
(4, 27, 'decde', 'eccedxdwxd', 'Nairobi', 20000.00, 'open', 'unpaid', 0, NULL, '2025-03-21 08:01:54', '2025-03-21 08:01:54'),
(5, 27, 'ghghjcghjgh', 'wdfeffef', 'Kitui', 33232.00, 'open', 'unpaid', 1, NULL, '2025-03-30 04:59:19', '2025-03-30 04:59:19'),
(6, 27, 'painting', 'zhdrhegewasassaD', 'Kitui', 10000.00, 'open', 'unpaid', 0, NULL, '2025-04-01 21:36:05', '2025-04-01 21:36:05'),
(7, 33, 'chakula', 'qwwe', 'dwdedwfdsdadd', 111111.00, 'open', 'unpaid', 0, NULL, '2025-04-01 22:02:58', '2025-04-01 22:02:58'),
(8, 34, 'fdfdfdfd', 'dfdfdfdfrdf', 'xdgxdgdxfxfxxc', 45455454.00, 'open', 'unpaid', 1, NULL, '2025-04-01 22:56:34', '2025-04-01 22:56:34'),
(9, 34, 'trffdfg', 'gffgfgfgfg', 'dsfdsdsfdsf', 10000.00, 'open', 'unpaid', 1, NULL, '2025-04-01 23:23:44', '2025-04-01 23:23:44'),
(10, 33, 'chakula', 'qwwe', 'dwdedwfdsdadd', 111111.00, 'open', 'unpaid', 0, NULL, '2025-04-03 06:32:27', '2025-04-03 06:32:27');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `fundi_id` int(11) NOT NULL,
  `proposal` text DEFAULT NULL,
  `bid_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `fundi_id`, `proposal`, `bid_amount`, `status`, `created_at`) VALUES
(1, 1, 31, 'unaona aje nikufanyie hii job', 14000.00, 'accepted', '2025-03-21 00:38:10'),
(2, 2, 31, 'hiii naezana', 17000.00, 'accepted', '2025-03-21 01:06:45'),
(3, 3, 31, 'scddfccfd', 20000.00, 'accepted', '2025-03-21 08:00:24'),
(4, 4, 32, 'hgjuhgvhbj v', 79988989.00, 'pending', '2025-03-29 20:49:15'),
(5, 5, 32, '2ffererfw', 2221221.00, 'pending', '2025-03-30 05:02:10'),
(6, 6, 32, 'I AM A GOOD PAINTER', 10000.00, 'pending', '2025-04-01 21:47:07'),
(7, 9, 35, 'rfdfdfd', 43434343.00, 'pending', '2025-04-01 23:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `job_milestones`
--

CREATE TABLE `job_milestones` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','approved') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_reviews`
--

CREATE TABLE `job_reviews` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_transactions`
--

CREATE TABLE `mpesa_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_ref` varchar(50) NOT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `merchant_request_id` varchar(100) DEFAULT NULL,
  `mpesa_receipt` varchar(50) DEFAULT NULL,
  `transaction_date` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `result_code` varchar(10) DEFAULT NULL,
  `result_desc` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mpesa_transactions`
--

INSERT INTO `mpesa_transactions` (`id`, `user_id`, `phone_number`, `amount`, `transaction_ref`, `checkout_request_id`, `merchant_request_id`, `mpesa_receipt`, `transaction_date`, `status`, `result_code`, `result_desc`, `created_at`, `updated_at`) VALUES
(1, 27, '254740491425', 1000.00, 'FH1743312895392', 'ws_CO_202503300734553679', 'AG_202503300734557142', 'SIM635737', '2025-03-30 08:37:14', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 05:34:55', '2025-03-30 05:37:14'),
(2, 27, '254740491425', 1000.00, 'FH1743313042460', 'ws_CO_202503300737228670', 'AG_202503300737229789', 'SIM121987', '2025-03-30 08:37:23', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 05:37:22', '2025-03-30 05:37:23'),
(3, 27, '254740491425', 1000.00, 'FH1743313121549', 'ws_CO_202503300738423700', 'AG_202503300738422555', 'SIM234338', '2025-03-30 08:43:56', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 05:38:42', '2025-03-30 05:43:56'),
(4, 27, '254740491425', 1000.00, 'FH1743313443824', 'ws_CO_202503300744035212', 'AG_202503300744038085', 'SIM772363', '2025-03-30 08:44:04', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 05:44:03', '2025-03-30 05:44:04'),
(5, 27, '254740491425', 1000.00, 'FH1743314609744', 'ws_CO_202503300803309123', 'AG_202503300803307828', 'SIM810030', '2025-03-30 09:14:14', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 06:03:30', '2025-03-30 06:14:14'),
(6, 27, '254740491425', 1000.00, 'FH1743314678501', 'ws_CO_202503300804394492', 'AG_202503300804392114', 'SIM392301', '2025-03-30 09:14:12', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 06:04:39', '2025-03-30 06:14:12'),
(7, 27, '254740491425', 1000.00, 'FH1743315189285', 'ws_CO_202503300813101442', 'AG_202503300813103259', 'SIM875060', '2025-03-30 09:13:10', 'completed', '0', 'The service request is processed successfully.', '2025-03-30 06:13:10', '2025-03-30 06:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `fundi_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform_fees`
--

CREATE TABLE `platform_fees` (
  `id` int(11) NOT NULL,
  `fee_type` enum('job_posting','job_completion','subscription','featured_listing') NOT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `fixed_amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_fees`
--

INSERT INTO `platform_fees` (`id`, `fee_type`, `percentage`, `fixed_amount`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'job_posting', NULL, 200.00, 'Fee charged when a client posts a new job', 1, '2025-03-29 21:11:18', '2025-03-29 21:11:18'),
(2, 'job_completion', 5.00, NULL, 'Percentage fee charged when a job is completed', 1, '2025-03-29 21:11:18', '2025-03-29 21:11:18'),
(3, 'subscription', NULL, 1000.00, 'Monthly subscription fee for premium features', 1, '2025-03-29 21:11:18', '2025-03-29 21:11:18'),
(4, 'featured_listing', NULL, 500.00, 'Fee to feature a job listing at the top of search results', 1, '2025-03-29 21:11:18', '2025-03-29 21:11:18');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_items`
--

CREATE TABLE `portfolio_items` (
  `id` int(11) NOT NULL,
  `fundi_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `project_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_reports`
--

CREATE TABLE `saved_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_name` varchar(100) NOT NULL,
  `report_type` enum('jobs','earnings','payments','applications') NOT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`) VALUES
(1, 'Carpentry'),
(7, 'Cleaning'),
(3, 'Electrical'),
(6, 'Landscaping'),
(5, 'Masonry'),
(4, 'Painting'),
(2, 'Plumbing'),
(8, 'Transport');

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_type` enum('basic','premium','enterprise') NOT NULL DEFAULT 'basic',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `transaction_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_type`, `start_date`, `end_date`, `amount_paid`, `is_active`, `auto_renew`, `created_at`, `updated_at`, `transaction_id`) VALUES
(1, 27, '', '2025-03-30', '2025-12-30', 1000.00, 1, 0, '2025-03-30 06:04:29', '2025-03-30 06:14:14', '5');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `transaction_type` enum('fee_payment','subscription','payout','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fee_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `job_id`, `transaction_type`, `amount`, `fee_amount`, `status`, `payment_method`, `transaction_reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 27, 3, 'fee_payment', 19000.00, 950.00, 'completed', 'direct', NULL, NULL, '2025-03-29 21:12:36', '2025-03-29 21:12:36'),
(2, 27, 2, 'fee_payment', 23000.00, 1150.00, 'completed', 'direct', NULL, NULL, '2025-03-30 04:48:58', '2025-03-30 04:48:58'),
(3, 27, 1, 'fee_payment', 120000.00, 6000.00, 'completed', 'direct', NULL, NULL, '2025-03-30 04:49:19', '2025-03-30 04:49:19'),
(4, 27, 5, 'fee_payment', 200.00, NULL, 'completed', 'direct', NULL, NULL, '2025-03-30 04:59:19', '2025-03-30 04:59:19'),
(5, 27, 6, 'fee_payment', 200.00, NULL, 'completed', 'direct', NULL, NULL, '2025-04-01 21:36:05', '2025-04-01 21:36:05'),
(6, 34, 8, 'fee_payment', 200.00, NULL, 'completed', 'direct', NULL, NULL, '2025-04-01 22:56:34', '2025-04-01 22:56:34'),
(7, 34, 9, 'fee_payment', 200.00, NULL, 'completed', 'direct', NULL, NULL, '2025-04-01 23:23:44', '2025-04-01 23:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('fundi','client') NOT NULL,
  `skills` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL DEFAULT '',
  `bio` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `user_type`, `skills`, `created_at`, `profile_image`, `location`, `bio`) VALUES
(1, 'walter', 'walter22@gmail.com', '0723426164', '$2y$10$ExxfxdMmsAmM4o8VY5ep5uYE9P4XY2rtFTzIIl0Eb0qdHlGEJq..m', 'client', NULL, '2025-03-14 15:19:56', NULL, '', ''),
(2, 'Alex', 'alexmuindu339@gmail.com', '0797728463', '$2y$10$DJiQkm6UBziTlSd9yh288u85LtRJqNHKjqHHVaKJgAc3F5hKxQ/Jy', 'client', NULL, '2025-03-14 16:01:49', NULL, '', ''),
(7, 'Alex', 'alexmuindu444@gmail.com', '0723426164', '$2y$10$LyK28VwKZ79Plxhba7YH1eDu51Q1dIasCxUKcmzVst.0/Z/Z2lSCO', 'client', NULL, '2025-03-14 16:13:11', NULL, '', ''),
(8, 'Alex', 'alexmuindu3@gmail.com', '0723426164', '$2y$10$g90gsDBlx8N4Tus4ZA65VOBl68OcLkw85qewCekLKz.ziF7GGVUcu', 'client', NULL, '2025-03-14 16:14:24', NULL, '', ''),
(9, 'Alex', 'alexmuindu@gmail.com', '0723426164', '$2y$10$WwZ/3hKGCd6Q16t40MLh5uwyUw2nyHbyn0TJoGkOj84ApTGOFR0eW', 'client', NULL, '2025-03-14 16:14:59', NULL, '', ''),
(13, 'mat', 'matanohpee@gmail.com', '0723426164', '$2y$10$rrBQ.prUB0BC5.M8Tm.TLumKI7fBrkxqLQjd8Qs0/KNuvkZOi1siC', 'client', NULL, '2025-03-14 16:30:12', NULL, '', ''),
(14, 'Alex', 'alexmuindu9@gmail.com', '0723426164', '$2y$10$ayrHTaVQy/h2OlH0yKj6SunlZO0dDvsMBW00nad96Je.rBE/kNkSa', 'fundi', 'plumber', '2025-03-14 16:33:50', NULL, '', ''),
(15, 'john', 'john@gmail.com', '0712345678', '$2y$10$UQeAxWN6UhUuzM505X1P1u/f9MZU0f70f8s8rwmCnRVLDaqwDK59u', 'fundi', 'plumber', '2025-03-14 16:40:25', NULL, '', ''),
(16, 'jeff', 'jeff@gmail.com', '0723426164', '$2y$10$HVJeZC5frFp5lggOaXmcguIyR2bpZln85oPstdakJZ3hvHH.FdTFy', 'fundi', 'plumber', '2025-03-14 17:18:48', NULL, '', ''),
(17, 'marcus', 'marcus3250@gmail.com', '0723426164', '$2y$10$lmoTvIqqEa4LIa.h2YCkCemkWIQgMQJUWARpy7SGPYi3sBMhfsVce', 'fundi', '', '2025-03-14 17:34:07', NULL, '', ''),
(18, 'james', 'james339@gmail.com', '0723426164', '$2y$10$.P9/VM47t1GyotjgYqem3OiynNOhg1Bd..iu7AgR6sql3XnVqRyae', 'fundi', 'plumber', '2025-03-14 17:45:08', NULL, '', ''),
(19, 'mary', 'mary@gmail.com', '0723426164', '$2y$10$d03TDbkxqEDPSmgEKfR7uOxOHIsDT2HOWcaADZpCig4Q4hyuH5/uW', 'fundi', 'plumber', '2025-03-14 18:13:27', NULL, '', ''),
(20, 'joan', 'joan@gmail.com', '0723426164', '$2y$10$5C6JG1/Yl.w71I0lasTDseiv8IRrv6B7bsQB9.t2/5f4LWL.Cbb7O', 'client', NULL, '2025-03-14 18:20:52', NULL, '', ''),
(23, 'jane', 'jane@gmail.com', '0723426164', '$2y$10$vPqeGeufuTAtVPJAk.5EpehHXkKqXZmZOaqBWfPDcs4YomjU2CPIS', 'client', NULL, '2025-03-15 11:07:53', NULL, '', ''),
(24, 'levi', 'levi@gmail.com', '0723426164', '$2y$10$oPgUw0zSPU4bypg9GmNTP.2JqLZpUd4k.O36lXEXoaAJBqcD9VexO', 'client', NULL, '2025-03-18 18:18:03', NULL, '', ''),
(25, 'Jacob Muema', 'jacobmuema02@gmail.com', '0740491425', '$2y$10$k2YW/E.Z1cTYi8rEUOUSkuUs6ydS9j0ukYKL6WCwDoivUJwynznV6', 'client', NULL, '2025-03-18 22:31:59', NULL, '', ''),
(26, 'Jacob Muema', 'jacobmuema0@gmail.com', '0740491425', '$2y$10$eFkFmqJmbXLr4URym7kQlOeQP94QP8vn7EBYC/rganv1Rf.vAur2q', 'client', NULL, '2025-03-18 22:36:38', NULL, '', ''),
(27, 'Jacob Muema', 'jacobmuema12@gmail.com', '0740491425', '$2y$10$FqsraBO/S05Mf3nF/2ePbOVVBrIFjU4ILVbKcUifhObF3TF9Ppjry', 'client', NULL, '2025-03-20 21:33:29', NULL, 'kenya', 'fggfgghhj'),
(28, 'Jacob Muema', 'jacobmuema22@gmail.com', '0740491425', '$2y$10$yu5LMvDEgSAlkjfkx18/auMbaEpQAedDXPY4IefIo9v2IlLdUruDq', 'client', NULL, '2025-03-20 21:45:56', NULL, '', ''),
(29, 'Jacob Muema', 'jacobmuema32@gmail.com', '0740491425', '$2y$10$9ZAFSTcwo3J/l4VZenTzMOkbrg5RzZxHwe7TML3JzsRt647uj0/VC', 'client', NULL, '2025-03-20 21:46:49', NULL, '', ''),
(30, 'Jacob Muema', 'jacobmuema9@gmail.com', '0740491425', '$2y$10$p4IcDyONq/m7L.ELfzsSF.09UG07HR3oyp4kzIyHiTQ14DcYR2YNq', 'client', NULL, '2025-03-20 21:48:56', NULL, '', ''),
(31, 'Jacob Muema', 'j@gmail.com', '0740491425', '$2y$10$N5i2qtClWF0H/zf59bgbfeaB1TS814/jB/mRRNGinHnfdaJXhmG8q', 'fundi', 'PLUMBER', '2025-03-20 21:52:51', NULL, 'Nairobi', 'a pro carpenter,plumber and electrician'),
(32, 'Jacob Muema', 'jacobmuema09@gmail.com', '0740491425', '$2y$10$nRvhsemi6/lz187XgGPCb.4Hc5YC8Nc3HEfZfhUi12KkGFq1edrxC', 'fundi', 'efedwfew', '2025-03-29 20:48:14', NULL, '', ''),
(33, 'Jeik Jakoo', 'jakoojeik@gmail.com', '0740491425', '$2y$10$uSPazQ0BX/306Ia5U4zL3eaR.DHQOoq7w525/rozQfZNRNp4PhbIa', 'client', NULL, '2025-04-01 22:01:46', NULL, '', ''),
(34, 'Jacob Muema', 'jacobmuema72@gmail.com', '0740491425', '$2y$10$T2.YL3N4F01ZnaDRUtfb4O03NUZxrQPejWF63mc2L1i/bPeh2HW7W', 'client', NULL, '2025-04-01 22:53:18', NULL, '', ''),
(35, 'Jacob Muema', 'jacobmuema62@gmail.com', '0740491425', '$2y$10$kJcQmPZPdj6.W/eFFnzDfeU8T/tH0Dy6M1Mae9tSnFO/PdE2S3i/u', 'fundi', 'bhnhghg', '2025-04-01 23:33:43', NULL, '', ''),
(36, 'Jacob Muema', 'jacobmuema07@gmail.com', '0740491425', '$2y$10$kkF1ZyICqHTH1bU4VB5VPeUdTL04Ne2E9QvNzvQzMjbzyv1rAVhuu', 'client', NULL, '2025-04-09 20:08:36', NULL, '', ''),
(37, 'Jacob Muema', 'jacobmuema42@gmail.com', '0740491425', '$2y$10$8fPIMys2yaFbREv9Oky9SOtqEyxpBbFhzyAmo.h9ue4l1WJn9WLW6', 'fundi', 'bhnhghg', '2025-04-10 14:36:34', NULL, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `verification_documents`
--

CREATE TABLE `verification_documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `document_url` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `client_job_reports`
--
DROP TABLE IF EXISTS `client_job_reports`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `client_job_reports`  AS SELECT `j`.`id` AS `id`, `j`.`title` AS `title`, `j`.`description` AS `description`, `j`.`location` AS `location`, `j`.`budget` AS `budget`, `j`.`status` AS `status`, `j`.`payment_status` AS `payment_status`, `j`.`is_featured` AS `is_featured`, `j`.`created_at` AS `created_at`, `j`.`completion_date` AS `completion_date`, `u`.`name` AS `client_name`, (select count(0) from `job_applications` where `job_applications`.`job_id` = `j`.`id`) AS `applications_count`, (select avg(`job_reviews`.`rating`) from `job_reviews` where `job_reviews`.`job_id` = `j`.`id` and `job_reviews`.`reviewee_id` = `j`.`client_id`) AS `client_rating`, (select sum(`transactions`.`amount`) from `transactions` where `transactions`.`job_id` = `j`.`id` and `transactions`.`transaction_type` = 'fee_payment') AS `fees_paid` FROM (`jobs` `j` join `users` `u` on(`j`.`client_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `fundi_job_reports`
--
DROP TABLE IF EXISTS `fundi_job_reports`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `fundi_job_reports`  AS SELECT `j`.`id` AS `id`, `j`.`title` AS `title`, `j`.`location` AS `location`, `j`.`budget` AS `budget`, `j`.`status` AS `status`, `j`.`created_at` AS `created_at`, `j`.`completion_date` AS `completion_date`, `u`.`name` AS `client_name`, `ja`.`bid_amount` AS `bid_amount`, `ja`.`status` AS `application_status`, `ja`.`created_at` AS `application_date`, (select avg(`job_reviews`.`rating`) from `job_reviews` where `job_reviews`.`job_id` = `j`.`id` and `job_reviews`.`reviewee_id` = `ja`.`fundi_id`) AS `fundi_rating` FROM ((`jobs` `j` join `job_applications` `ja` on(`j`.`id` = `ja`.`job_id`)) join `users` `u` on(`j`.`client_id` = `u`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fundi_skills`
--
ALTER TABLE `fundi_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fundi_id` (`fundi_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`,`fundi_id`),
  ADD KEY `fundi_id` (`fundi_id`);

--
-- Indexes for table `job_milestones`
--
ALTER TABLE `job_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `job_reviews`
--
ALTER TABLE `job_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `fundi_id` (`fundi_id`);

--
-- Indexes for table `platform_fees`
--
ALTER TABLE `platform_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fundi_id` (`fundi_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indexes for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fundi_skills`
--
ALTER TABLE `fundi_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `job_milestones`
--
ALTER TABLE `job_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_reviews`
--
ALTER TABLE `job_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_fees`
--
ALTER TABLE `platform_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_reports`
--
ALTER TABLE `saved_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `verification_documents`
--
ALTER TABLE `verification_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fundi_skills`
--
ALTER TABLE `fundi_skills`
  ADD CONSTRAINT `fundi_skills_ibfk_1` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fundi_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_milestones`
--
ALTER TABLE `job_milestones`
  ADD CONSTRAINT `job_milestones_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_reviews`
--
ALTER TABLE `job_reviews`
  ADD CONSTRAINT `job_reviews_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  ADD CONSTRAINT `mpesa_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD CONSTRAINT `portfolio_items_ibfk_1` FOREIGN KEY (`fundi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD CONSTRAINT `saved_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD CONSTRAINT `verification_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
