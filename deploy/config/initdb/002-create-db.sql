-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2022 at 11:33 AM
-- Server version: 8.0.31-0ubuntu0.20.04.1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anteNaToVris`
--
CREATE DATABASE IF NOT EXISTS `anteNaToVris` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `anteNaToVris`;

-- --------------------------------------------------------

--
-- Table structure for table `badge`
--

CREATE TABLE `badge` (
                         `id` int NOT NULL,
                         `title` text NOT NULL,
                         `description` text NOT NULL,
                         `imageURL` varchar(4096) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `challenge`
--

CREATE TABLE `challenge` (
                             `id` int NOT NULL,
                             `title` text NOT NULL,
                             `description` text NOT NULL,
                             `brochure` mediumblob,
                             `validFrom` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             `validTo` datetime NOT NULL,
                             `officialSolutionCode` mediumblob,
                             `officialSolutionResult` json NOT NULL,
                             `offersPoints` int NOT NULL,
                             `accessKey` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `challengeSet`
--

CREATE TABLE `challengeSet` (
                                `id` int NOT NULL,
                                `title` text NOT NULL,
                                `description` text NOT NULL,
                                `validFrom` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'superseeds challenge''s validFrom',
                                `validTo` datetime NOT NULL COMMENT 'superseeds challenge''s validTo',
                                `extraPointsForSetCompletion` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `challengeSet_challenge`
--

CREATE TABLE `challengeSet_challenge` (
                                          `challengeSet_id` int NOT NULL,
                                          `challenge_id` int NOT NULL,
                                          `orderOfChallenge` int NOT NULL,
                                          `accessKey` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `solution`
--

CREATE TABLE `solution` (
                            `id` int NOT NULL,
                            `challenge_id` int NOT NULL,
                            `challengeSet_id` int DEFAULT NULL,
                            `team_id` int NOT NULL,
                            `submissionDateTime` datetime NOT NULL,
                            `status` set('correct','wrong') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
                            `resultSubmitted` json NOT NULL,
                            `codeOfSolutionSubmitted` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
                        `id` int NOT NULL,
                        `title` text NOT NULL,
                        `dateOfCreation` datetime NOT NULL,
                        `logoImgURL` varchar(4096) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
                        `data` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `team_badge`
--

CREATE TABLE `team_badge` (
                              `team_id` int NOT NULL,
                              `badge_id` int NOT NULL,
                              `datetimeAwarded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `team_challenge`
--

CREATE TABLE `team_challenge` (
                                  `team_id` int NOT NULL,
                                  `challenge_id` int NOT NULL,
                                  `assignmentDatetime` datetime NOT NULL,
                                  `completionDateTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `team_challengeSet`
--

CREATE TABLE `team_challengeSet` (
                                     `team_id` int NOT NULL,
                                     `challengeSet_id` int NOT NULL,
                                     `assignmentDatetime` datetime NOT NULL,
                                     `completionDateTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badge`
--
ALTER TABLE `badge`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `challenge`
--
ALTER TABLE `challenge`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accessKey` (`accessKey`);

--
-- Indexes for table `challengeSet`
--
ALTER TABLE `challengeSet`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `challengeSet_challenge`
--
ALTER TABLE `challengeSet_challenge`
    ADD PRIMARY KEY (`challengeSet_id`,`challenge_id`),
  ADD UNIQUE KEY `accessKey` (`accessKey`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `solution`
--
ALTER TABLE `solution`
    ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `challenge_id` (`challenge_id`),
  ADD KEY `challengeSet_id` (`challengeSet_id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_badge`
--
ALTER TABLE `team_badge`
    ADD PRIMARY KEY (`team_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `team_challenge`
--
ALTER TABLE `team_challenge`
    ADD PRIMARY KEY (`team_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `team_challengeSet`
--
ALTER TABLE `team_challengeSet`
    ADD PRIMARY KEY (`team_id`,`challengeSet_id`),
  ADD KEY `challengeSet_id` (`challengeSet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badge`
--
ALTER TABLE `badge`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challenge`
--
ALTER TABLE `challenge`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challengeSet`
--
ALTER TABLE `challengeSet`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solution`
--
ALTER TABLE `solution`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `challengeSet_challenge`
--
ALTER TABLE `challengeSet_challenge`
    ADD CONSTRAINT `challengeSet_challenge_ibfk_1` FOREIGN KEY (`challengeSet_id`) REFERENCES `challengeSet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `challengeSet_challenge_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `solution`
--
ALTER TABLE `solution`
    ADD CONSTRAINT `solution_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `solution_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `solution_ibfk_3` FOREIGN KEY (`challengeSet_id`) REFERENCES `challengeSet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_badge`
--
ALTER TABLE `team_badge`
    ADD CONSTRAINT `team_badge_ibfk_1` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `team_badge_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_challenge`
--
ALTER TABLE `team_challenge`
    ADD CONSTRAINT `team_challenge_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `team_challenge_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_challengeSet`
--
ALTER TABLE `team_challengeSet`
    ADD CONSTRAINT `team_challengeSet_ibfk_1` FOREIGN KEY (`challengeSet_id`) REFERENCES `challengeSet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `team_challengeSet_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
