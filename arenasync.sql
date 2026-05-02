-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 03:02 PM
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
-- Database: `arenasync`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `booked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`user_id`, `event_id`, `booked_at`) VALUES
(4, 1, '2026-05-02 13:38:24'),
(4, 7, '2026-05-02 13:38:24'),
(4, 19, '2026-05-02 13:46:01'),
(4, 22, '2026-05-02 13:39:20');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `game_id` int(11) NOT NULL,
  `organiser_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `date_time`, `game_id`, `organiser_id`) VALUES
(1, '2026-06-20 12:30:00', 1, 6),
(2, '2026-07-06 00:00:00', 4, 6),
(3, '2026-05-15 18:00:00', 1, 8),
(4, '2026-06-02 20:00:00', 3, 8),
(5, '2026-07-20 19:00:00', 7, 8),
(6, '2026-09-05 15:00:00', 10, 8),
(7, '2026-05-22 17:00:00', 2, 9),
(8, '2026-08-10 21:00:00', 6, 9),
(9, '2026-10-18 14:00:00', 9, 9),
(10, '2026-05-30 19:30:00', 4, 10),
(11, '2026-06-25 20:00:00', 5, 10),
(12, '2026-07-12 18:00:00', 8, 10),
(13, '2026-09-19 16:00:00', 1, 10),
(14, '2026-11-07 21:00:00', 3, 10),
(15, '2026-06-14 20:00:00', 6, 11),
(16, '2026-08-28 15:00:00', 10, 11),
(17, '2026-12-05 18:00:00', 2, 11),
(18, '2026-05-08 19:00:00', 5, 12),
(19, '2026-07-04 17:00:00', 7, 12),
(20, '2026-09-12 20:30:00', 4, 12),
(21, '2026-11-21 14:00:00', 9, 12),
(22, '2026-05-25 18:00:00', 8, 13),
(23, '2026-07-17 19:00:00', 1, 13),
(24, '2026-08-22 21:00:00', 6, 13),
(25, '2026-10-30 20:00:00', 3, 13),
(26, '2026-12-12 15:00:00', 10, 13),
(27, '2026-06-06 17:00:00', 2, 14),
(28, '2026-08-15 14:00:00', 9, 14),
(29, '2026-09-27 20:00:00', 5, 14),
(30, '2026-11-14 19:30:00', 4, 14);

-- --------------------------------------------------------

--
-- Table structure for table `favourite_events`
--

CREATE TABLE `favourite_events` (
  `id` int(11) NOT NULL,
  `attendee_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite_events`
--

INSERT INTO `favourite_events` (`id`, `attendee_id`, `event_id`, `created_at`) VALUES
(1, 4, 7, '2026-05-01 20:34:36'),
(2, 4, 1, '2026-05-01 20:44:04'),
(3, 4, 2, '2026-05-01 20:57:41'),
(4, 4, 22, '2026-05-02 12:39:07'),
(5, 4, 19, '2026-05-02 12:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `favourite_organizers`
--

CREATE TABLE `favourite_organizers` (
  `id` int(11) NOT NULL,
  `attendee_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite_organizers`
--

INSERT INTO `favourite_organizers` (`id`, `attendee_id`, `organizer_id`, `created_at`) VALUES
(3, 4, 6, '2026-05-01 20:13:57'),
(4, 4, 10, '2026-05-01 20:14:20'),
(5, 4, 8, '2026-05-01 20:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `category`, `description`) VALUES
(1, 'EA Sports FC', 'Sports', 'Get ready for the next evolution in football gaming with EA Sports FC 25!\r\n\r\nExperience the thrill of the pitch with realistic gameplay, stunning graphics, and an expansive roster of teams and players. \r\n\r\nWhether you\'re a seasoned pro or a newcomer, this event offers an immersive football experience like never before. Join us for competitive matches, community challenges, and more!'),
(2, 'Batman: Arkham', 'Adventure', 'The Batman: Arkham series redefined superhero gaming with fluid Freeflow combat, deep detective mechanics, and a rich atmospheric Gotham City. Step into the Dark Knight\'s boots, face iconic villains, and prove you are the world\'s greatest detective.'),
(3, 'Apex Legends', 'Strategy', 'Apex Legends is a fast-paced battle royale hero shooter where squads of three compete to be the last team standing. Each Legend brings unique abilities that reward teamwork and communication. Mastering movement, gunplay, and ability synergy is the key to victory.'),
(4, 'NBA 2K', 'Sports', 'The NBA 2K series brings pro basketball to life with true-to-life player movement, in-depth MyCareer story modes, and the addictive MyTeam card-collection experience. Build your dream roster, dominate the park, or lead your team to a championship.'),
(5, 'Call of Duty', 'Strategy', 'Call of Duty is the defining first-person shooter franchise, spanning iconic World War II campaigns to modern and futuristic warfare. With a legendary multiplayer mode, gripping single-player campaigns, and the massive Warzone battle royale, there is something for every FPS fan.'),
(6, 'Fortnite', 'Strategy', 'Fortnite is the cultural phenomenon battle royale where 100 players drop onto an island and fight to be the last one standing — with building mechanics that add a unique strategic layer. Constant updates, live events, and crossover collaborations keep the experience ever-evolving.'),
(7, 'Grand Theft Auto', 'Adventure', 'Grand Theft Auto is Rockstar\'s legendary open-world crime sandbox. Explore sprawling fictional cities, follow cinematic story campaigns, and cause mayhem at will. GTA Online expands the experience into a living multiplayer world with heists, races, and endless criminal enterprise.'),
(8, 'Mortal Kombat', 'Strategy', 'Mortal Kombat is the iconic fighting game franchise known for its brutal combat, visceral fatality finishing moves, and a surprisingly deep lore spanning realms and timelines. Master each fighter\'s move set and dominate opponents in 1v1 battles or online ranked play.'),
(9, 'The Legend of Zelda', 'Adventure', 'The Legend of Zelda is Nintendo\'s iconic action-adventure series following hero Link on his quest to save Princess Zelda. From puzzle-filled dungeons to the boundless open worlds of Breath of the Wild and Tears of the Kingdom, Zelda defines the adventure genre.'),
(10, 'Forza', 'Sports', 'The Forza franchise covers two distinct racing experiences — Forza Motorsport\'s precision circuit racing and Forza Horizon\'s open-world festival driving. With hundreds of meticulously detailed cars and stunning environments, Forza is the benchmark for racing games on any platform.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('admin','organiser','attendee') NOT NULL DEFAULT 'attendee',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_visited` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `first_name`, `last_name`, `company`, `email`, `password`, `created_at`, `last_visited`, `remember_token`) VALUES
(1, 'admin', 'Emmanuel', 'Ayobanjo', 'ArenaSync', 'emmanuel@arenasync.com', '$2y$10$S/nbjvz72HAcYlL/rJj6/.eZ2OjRcDLRIBh9eZKfQPu4Tav2x2Tw2', '2026-04-25 11:24:06', NULL, NULL),
(2, 'admin', 'Ahmad', 'Assante', 'ArenaSync', 'ahmad@arenasync.com', '$2y$10$qllCcHkgyXmb1DesDv3XT.x22U/Pk9VIed2KOeIxTLQH7TFHWBgOe', '2026-04-25 11:24:06', NULL, NULL),
(3, 'admin', 'Miguel', 'Cofre', 'ArenaSync', 'miguel@arenasync.com', '$2y$10$VO84UEXCHcnE7VUAAsjJ1.5DS0hwAoirpFEn1fojrAAOUO7LQPJki', '2026-04-25 11:24:06', NULL, NULL),
(4, 'attendee', 'Emmanuel', 'Ayobanjo', NULL, 'ayobanjoemmanuel1@gmail.com', '$2y$10$Y5fYJl0G.dyzfCKRhZKFDeZ.W2vidSthpM69ubY/c3ysfeuFLd6Ga', '2026-04-25 12:42:55', '2026-05-02 12:58:07', '9c5a10800b3e76c949b1549718dca4236540921d6c34a6e30b0bdc760010fec1'),
(5, 'attendee', 'Deliwe', 'Mufute', NULL, 'deliwenufute@gmail.com', '$2y$10$HaTA396thdEw7We0Nvbw1..f.vcsLMeiYsG5Lh406FhSK9/LacvG.', '2026-04-25 12:45:34', NULL, NULL),
(6, 'organiser', NULL, NULL, 'Alphabet', 'alphabet@company.org', '$2y$10$eqCyx5iCGB7AeJer2AUn2uTTODZtQQhDO62v8bkQWwdlpNrYU8T.K', '2026-04-25 14:20:45', NULL, NULL),
(8, 'organiser', NULL, NULL, 'Epic Games', 'epic.games@company.org', '$2y$10$aUfEr76tfIWbTHSEPhGfyepsVsjrq8RWWEuPIEu78UYtCJc.9ct8G', '2026-04-25 14:31:59', NULL, NULL),
(9, 'organiser', NULL, NULL, 'ESL Gaming', 'esl.gaming@company.ie', '$2y$10$IgDDBIo1hEGILdVNOR3e2ea5X6kokLQBekpUHJ5NL8tTlsNJxugWq', '2026-04-25 14:40:09', NULL, NULL),
(10, 'organiser', NULL, NULL, 'DreamHack', 'dream.hack@company.ie', '$2y$10$IcxMMOk6RddX.hnyLuAHKOqgWrIag0r6INtKfaF2v.sp.PSyRR0p.', '2026-04-25 14:40:09', NULL, NULL),
(11, 'organiser', NULL, NULL, 'MLG Events', 'mlg.events@company.edu', '$2y$10$CaHDI8NofgrCQwMSrVmDNeHrHb0SmfgpkXdKZCoxipyfjJUicUPnW', '2026-04-25 14:40:09', NULL, NULL),
(12, 'organiser', NULL, NULL, 'Riot Esports', 'riot.esports@company.com', '$2y$10$vJ9h./PU9DZECPcXKmbNRufAblx1pHiJ6hwk1p.LHOaxzvJGH7HMG', '2026-04-25 14:40:09', NULL, NULL),
(13, 'organiser', NULL, NULL, 'Blizzard Gaming', 'blizzard.gaming@company.edu', '$2y$10$SZsu7eDjIvl64r8hvzK.hOK7h6yIIl6kUmD101qDmLtKyvr/Wk6kG', '2026-04-25 14:40:09', NULL, NULL),
(14, 'organiser', NULL, NULL, 'PGL Esports', 'pgl.esports@company.ie', '$2y$10$GHrKSoFhELl1ATMaTsnWOeIM.oW/aSoDN/Q1UxN.wKYL817LKemQi', '2026-04-25 14:40:09', NULL, NULL),
(15, 'attendee', 'Deliwe', 'Ayobanjo', NULL, 'deliweayobanjo@gmail.com', '$2y$10$mY4AThiBYGooJcf6wMmL.ezGYl30ppEOQhn2tAJL/VoP6utTbb/Mq', '2026-04-27 13:29:13', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`user_id`,`event_id`),
  ADD KEY `fk_booking_event` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_event_game` (`game_id`),
  ADD KEY `fk_event_organiser` (`organiser_id`);

--
-- Indexes for table `favourite_events`
--
ALTER TABLE `favourite_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav_event` (`attendee_id`,`event_id`);

--
-- Indexes for table `favourite_organizers`
--
ALTER TABLE `favourite_organizers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`attendee_id`,`organizer_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `favourite_events`
--
ALTER TABLE `favourite_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favourite_organizers`
--
ALTER TABLE `favourite_organizers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_event_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_organiser` FOREIGN KEY (`organiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
