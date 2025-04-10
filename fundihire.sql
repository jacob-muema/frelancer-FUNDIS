-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 02:30 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `client_id`, `title`, `description`, `location`, `budget`, `status`, `created_at`, `updated_at`) VALUES
(1, 27, '3 bedroom', '3 bedroom house', 'Nairobi', 120000.00, 'completed', '2025-03-21 00:15:08', '2025-03-21 00:59:56'),
(2, 27, 'Nataka fundi wa malngo', 'fundi wa mlango exp 4-5years', 'Kitui', 23000.00, 'assigned', '2025-03-21 01:02:15', '2025-03-21 01:11:41');

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
(2, 2, 31, 'hiii naezana', 17000.00, 'accepted', '2025-03-21 01:06:45');

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
(27, 'Jacob Muema', 'jacobmuema12@gmail.com', '0740491425', '$2y$10$.TMG46r8SUp9zosCbH9Je.b5spb.zdx9AkWB80KCHCNWgWFadHLpC', 'client', NULL, '2025-03-20 21:33:29', NULL, 'kenya', 'fggfgghhj'),
(28, 'Jacob Muema', 'jacobmuema22@gmail.com', '0740491425', '$2y$10$yu5LMvDEgSAlkjfkx18/auMbaEpQAedDXPY4IefIo9v2IlLdUruDq', 'client', NULL, '2025-03-20 21:45:56', NULL, '', ''),
(29, 'Jacob Muema', 'jacobmuema32@gmail.com', '0740491425', '$2y$10$9ZAFSTcwo3J/l4VZenTzMOkbrg5RzZxHwe7TML3JzsRt647uj0/VC', 'client', NULL, '2025-03-20 21:46:49', NULL, '', ''),
(30, 'Jacob Muema', 'jacobmuema9@gmail.com', '0740491425', '$2y$10$p4IcDyONq/m7L.ELfzsSF.09UG07HR3oyp4kzIyHiTQ14DcYR2YNq', 'client', NULL, '2025-03-20 21:48:56', NULL, '', ''),
(31, 'Jacob Muema', 'j@gmail.com', '0740491425', '$2y$10$N5i2qtClWF0H/zf59bgbfeaB1TS814/jB/mRRNGinHnfdaJXhmG8q', 'fundi', 'PLUMBER', '2025-03-20 21:52:51', NULL, 'Nairobi', 'a pro carpenter,plumber and electrician');

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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `job_id` (`job_id`);

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
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_milestones`
--
ALTER TABLE `job_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD CONSTRAINT `verification_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
