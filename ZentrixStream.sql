-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql201.infinityfree.com
-- Generation Time: Jun 21, 2026 at 04:01 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: ``  // Your database username
--

-- --------------------------------------------------------

--
-- Table structure for table `anime_comments`
--

CREATE TABLE `anime_comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `episode` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `anime_comments`
--

INSERT INTO `anime_comments` (`id`, `user_id`, `anime_id`, `episode`, `comment_text`, `created_at`) VALUES
(1, 1, 269, 4, 'The anime is so great!', '2026-05-12 14:23:19'),
(2, 1, 196935, 1, 'Good', '2026-06-20 20:19:44'),
(3, 1, 198113, 1, 'Gud', '2026-06-20 20:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `anime_history`
--

CREATE TABLE `anime_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anime_id` varchar(50) NOT NULL,
  `anime_title` varchar(255) NOT NULL,
  `episode` varchar(50) DEFAULT NULL,
  `watched_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anime_history`
--

INSERT INTO `anime_history` (`id`, `user_id`, `anime_id`, `anime_title`, `episode`, `watched_at`) VALUES
(1, 1, '182205', 'That Time I Got Reincarnated as a Slime Season 4', '1', '2026-05-10 18:26:53'),
(9, 1, '206914', 'NIPPON SANGOKU: The Three Nations of the Crimson Sun', '1', '2026-05-11 09:54:18'),
(13, 1, '197824', 'Farming Life in Another World 2', '1', '2026-05-12 08:07:44'),
(14, 1, '21', 'ONE PIECE', '1', '2026-05-12 09:10:45'),
(16, 1, '269', 'Bleach', '1', '2026-05-12 14:23:53'),
(27, 1, '189987', 'The Klutzy Class Monitor and the Girl with the Short Skirt', '1', '2026-05-12 14:33:55'),
(29, 1, '195600', '', '2', '2026-06-20 20:16:17'),
(30, 1, '196935', '', '1', '2026-06-20 20:19:39'),
(31, 1, '198113', '', '1', '2026-06-20 20:36:36'),
(32, 1, '170599', '', '1', '2026-06-20 20:37:33'),
(33, 1, '185779', '', '1', '2026-06-20 20:38:12');

-- --------------------------------------------------------

--
-- Table structure for table `movie_comments`
--

CREATE TABLE `movie_comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `media_type` varchar(10) NOT NULL,
  `season` int(11) DEFAULT 1,
  `episode` int(11) DEFAULT 1,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `movie_comments`
--

INSERT INTO `movie_comments` (`id`, `user_id`, `media_id`, `media_type`, `season`, `episode`, `comment_text`, `created_at`) VALUES
(1, 1, 1330021, 'movie', 1, 1, 'It\'s a great film', '2026-05-12 17:04:31'),
(2, 1, 202555, 'tv', 1, 1, 'What a film', '2026-05-12 18:35:06'),
(3, 1, 1451344, 'movie', 1, 1, 'Goo', '2026-06-20 07:36:10'),
(4, 1, 1451344, 'movie', 1, 1, 'Good', '2026-06-20 07:36:16'),
(5, 1, 203744, 'tv', 1, 1, 'Good', '2026-06-20 08:42:13'),
(6, 1, 1339713, 'movie', 1, 1, 'Hi', '2026-06-20 08:49:56'),
(7, 1, 936075, 'movie', 1, 1, 'What a film', '2026-06-20 09:20:43'),
(8, 1, 1367220, 'movie', 1, 1, 'What a film', '2026-06-20 15:43:55'),
(9, 1, 93405, 'tv', 1, 1, 'Gud', '2026-06-20 15:49:11'),
(10, 1, 1084244, 'movie', 1, 1, 'What a moviee', '2026-06-21 07:05:57'),
(11, 1, 1083381, 'movie', 1, 1, 'Good film', '2026-06-21 07:12:25'),
(12, 1, 1084244, 'movie', 1, 1, 'gud', '2026-06-21 07:47:07'),
(13, 1, 1084244, 'movie', 1, 1, 'Pixarr', '2026-06-21 07:54:26');

-- --------------------------------------------------------

--
-- Table structure for table `movie_history`
--

CREATE TABLE `movie_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `media_type` varchar(20) NOT NULL DEFAULT 'movie',
  `title` varchar(255) DEFAULT 'Unknown',
  `season` int(11) DEFAULT 1,
  `episode` int(11) DEFAULT 1,
  `watched_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `progress` int(3) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `movie_history`
--

INSERT INTO `movie_history` (`id`, `user_id`, `media_id`, `media_type`, `title`, `season`, `episode`, `watched_at`, `progress`) VALUES
(1, 1, 931285, 'movie', 'Unknown', 1, 1, '2026-05-12 18:34:32', 0),
(2, 1, 202555, 'tv', 'Unknown', 1, 1, '2026-05-12 18:34:46', 0),
(3, 1, 71915, 'tv', 'Unknown', 1, 1, '2026-05-13 13:52:29', 0),
(4, 1, 203744, 'tv', 'Unknown', 1, 2, '2026-06-20 17:44:18', 1),
(5, 1, 1339713, 'movie', 'Unknown', 1, 1, '2026-06-20 08:49:52', 0),
(6, 1, 936075, 'movie', 'Unknown', 1, 1, '2026-06-21 07:59:28', 0),
(7, 4, 320685, 'tv', 'Unknown', 1, 13, '2026-06-21 07:55:38', 0),
(8, 5, 847742, 'movie', 'Unknown', 1, 1, '2026-06-20 11:02:56', 0),
(9, 5, 1410937, 'movie', 'Unknown', 1, 1, '2026-06-20 11:09:25', 0),
(10, 5, 969681, 'movie', 'Unknown', 1, 1, '2026-06-20 15:42:13', 0),
(11, 5, 270476, 'tv', 'Unknown', 1, 1, '2026-06-20 11:03:18', 0),
(12, 5, 1367220, 'movie', 'Unknown', 1, 1, '2026-06-20 12:19:05', 0),
(13, 1, 296285, 'tv', 'Unknown', 1, 1, '2026-06-20 14:57:50', 0),
(14, 1, 270476, 'tv', 'Unknown', 1, 1, '2026-06-21 07:03:18', 0),
(15, 5, 1046183, 'movie', 'Unknown', 1, 1, '2026-06-20 15:14:19', 0),
(16, 5, 1290190, 'movie', 'Unknown', 1, 1, '2026-06-20 15:27:57', 0),
(17, 5, 1444032, 'movie', 'Unknown', 1, 1, '2026-06-20 15:34:57', 0),
(18, 1, 320685, 'tv', 'Unknown', 1, 1, '2026-06-20 15:55:00', 0),
(19, 1, 1367220, 'movie', 'Unknown', 1, 1, '2026-06-20 19:29:07', 0),
(20, 1, 93405, 'tv', 'Unknown', 1, 1, '2026-06-20 15:52:44', 0),
(21, 1, 37799, 'movie', 'Unknown', 1, 1, '2026-06-20 18:27:57', 0),
(22, 1, 1084244, 'movie', 'Unknown', 1, 1, '2026-06-21 07:54:33', 0),
(23, 1, 1083381, 'movie', 'Unknown', 1, 1, '2026-06-21 07:12:32', 0),
(24, 4, 936075, 'movie', 'Unknown', 1, 1, '2026-06-21 07:59:04', 0);

-- --------------------------------------------------------

--
-- Table structure for table `room_chat`
--

CREATE TABLE `room_chat` (
  `id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_users`
--

CREATE TABLE `room_users` (
  `id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_ready` tinyint(1) DEFAULT 0,
  `last_active` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `room_users`
--

INSERT INTO `room_users` (`id`, `room_code`, `user_id`, `is_ready`, `last_active`) VALUES
(1, '61DN5Y', 1, 0, '2026-06-21 07:31:12'),
(2, '61DN5Y', 6, 0, '2026-06-21 07:32:27'),
(3, 'QC60Y7', 1, 0, '2026-06-21 07:41:03'),
(4, 'QC60Y7', 6, 0, '2026-06-21 07:54:42'),
(5, 'POXFBC', 1, 0, '2026-06-21 07:59:04'),
(6, 'POXFBC', 6, 1, '2026-06-21 07:55:26'),
(7, 'Y074TF', 1, 0, '2026-06-21 08:01:04'),
(8, 'Y074TF', 6, 1, '2026-06-21 08:00:47');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('announcement', ''),
('announcement_active', '0');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(10) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'Solozip', 'ar5102935@gmail.com', '$2y$10$5Pqt/OIn7QsaaHIdhk//OO0LdzRo9nl/cjgzsgJ/AUgQa4yl0qYGi', '2026-05-10 15:16:34', 'admin'),
(2, 'Raju Bhai', 'shinigamiamaan@gmail.com', '$2y$10$bf8W8RREyiqVPT5k0gfnyeoZfOfXevbt53W5h4JiVuPHRD2IlQxwm', '2026-05-23 13:51:34', 'user'),
(3, 'Aadhi', 'aadityatvm@gmail.com', '$2y$10$kKUd7hkVu8U1rfWGIE4OkujsqvAFnpSeqoggiXmmHWPmuhMBLkd.S', '2026-05-28 09:24:12', 'user'),
(4, 'Aztro', 'tombabu472@gmail.com', '$2y$10$/PgvKiHTJECBEqK/Djp/t.zmBzaBFhS0Nb0m.kyZXhT50Zggi/K9y', '2026-06-19 17:24:08', 'admin'),
(5, 'don', 'hashirmuhammed5738@gmail.com', '$2y$10$qF1tLD4JrAL6zvaLMKjxm.Bx/Q6CN2.QqvqB6Q/v4YCF4rPrVTXH.', '2026-06-20 10:56:12', 'user'),
(6, 'Test', 'test@gmail.com', '$2y$10$zbl7WB6Kzp7jPoFFjJS2keo77FLzITYsDaffWisuaW7iaWrc.H9ya', '2026-06-21 07:13:08', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'anime',
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `watchlist`
--

INSERT INTO `watchlist` (`id`, `user_id`, `media_id`, `type`, `title`, `created_at`) VALUES
(1, 1, 197824, 'anime', 'Farming Life in Another World 2', '2026-05-12 14:45:32'),
(3, 1, 124364, 'tv', 'FROM', '2026-05-12 17:11:17'),
(5, 1, 202555, 'tv', 'Daredevil: Born Again', '2026-05-12 18:35:21'),
(6, 1, 1439930, 'movie', 'A Marvel Television Special Presentation - The Punisher: One Last Kill', '2026-05-13 12:59:22'),
(9, 1, 1084244, 'movie', 'Toy Story 5', '2026-06-21 07:54:30'),
(8, 1, 1083381, 'movie', 'Backrooms', '2026-06-21 07:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

CREATE TABLE `watch_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `episode` int(11) NOT NULL DEFAULT 1,
  `watched_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watch_rooms`
--

CREATE TABLE `watch_rooms` (
  `id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `media_id` int(11) NOT NULL,
  `media_type` varchar(20) DEFAULT 'movie',
  `season` int(11) DEFAULT 1,
  `episode` int(11) DEFAULT 1,
  `host_id` int(11) NOT NULL,
  `play_status` varchar(20) DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `watch_rooms`
--

INSERT INTO `watch_rooms` (`id`, `room_code`, `media_id`, `media_type`, `season`, `episode`, `host_id`, `play_status`, `created_at`) VALUES
(1, 'M0ULJ6', 1084244, 'movie', 1, 1, 1, 'waiting', '2026-06-21 07:11:48'),
(2, '61DN5Y', 1083381, 'movie', 1, 1, 1, 'peachify', '2026-06-21 07:12:32'),
(3, 'QC60Y7', 1084244, 'movie', 1, 1, 1, 'peachify', '2026-06-21 07:33:00'),
(4, '9MYJNP', 1084244, 'movie', 1, 1, 1, 'waiting', '2026-06-21 07:41:11'),
(5, 'HFZ2R8', 1084244, 'movie', 1, 1, 1, 'waiting', '2026-06-21 07:41:54'),
(6, 'POXFBC', 1084244, 'movie', 1, 1, 1, 'peachify', '2026-06-21 07:54:32'),
(7, 'Y074TF', 936075, 'movie', 1, 1, 1, 'peachify', '2026-06-21 07:59:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anime_comments`
--
ALTER TABLE `anime_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `anime_history`
--
ALTER TABLE `anime_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_anime_watch` (`user_id`,`anime_id`);

--
-- Indexes for table `movie_comments`
--
ALTER TABLE `movie_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_history`
--
ALTER TABLE `movie_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_chat`
--
ALTER TABLE `room_chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_users`
--
ALTER TABLE `room_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_room` (`room_code`,`user_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `watch_rooms`
--
ALTER TABLE `watch_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anime_comments`
--
ALTER TABLE `anime_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `anime_history`
--
ALTER TABLE `anime_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `movie_comments`
--
ALTER TABLE `movie_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `movie_history`
--
ALTER TABLE `movie_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `room_chat`
--
ALTER TABLE `room_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_users`
--
ALTER TABLE `room_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `watch_history`
--
ALTER TABLE `watch_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watch_rooms`
--
ALTER TABLE `watch_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anime_history`
--
ALTER TABLE `anime_history`
  ADD CONSTRAINT `fk_anime_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
