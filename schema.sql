-- Clean Schema for Amanda AI

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ia_amanda`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `task` text,
  `result` text,
  `executed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amanda_profile`
--

CREATE TABLE `amanda_profile` (
  `id` int NOT NULL DEFAULT '1',
  `name` varchar(50) DEFAULT 'Amanda',
  `age` int DEFAULT '24',
  `current_mood` enum('happy','focused','sad','worried') DEFAULT 'happy',
  `autonomy_level` int DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Default profile insert
--
INSERT INTO `amanda_profile` (`id`, `name`, `age`, `current_mood`, `autonomy_level`) VALUES (1, 'Amanda', 24, 'happy', 5);

-- --------------------------------------------------------

--
-- Table structure for table `amanda_skills`
--

CREATE TABLE `amanda_skills` (
  `id` int NOT NULL,
  `command_key` varchar(100) DEFAULT NULL,
  `execution_script` varchar(255) DEFAULT NULL,
  `environment_type` enum('ubuntu','virtualbox','web') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `current_context`
--

CREATE TABLE `current_context` (
  `context_key` varchar(50) NOT NULL,
  `context_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_base`
--

CREATE TABLE `knowledge_base` (
  `id` int NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `response` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `short_term_memory`
--

CREATE TABLE `short_term_memory` (
  `id` int NOT NULL,
  `user_command` text NOT NULL,
  `amanda_response` text NOT NULL,
  `intent` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for tables
--

ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `amanda_profile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `amanda_skills`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `current_context`
  ADD PRIMARY KEY (`context_key`);

ALTER TABLE `knowledge_base`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`);

ALTER TABLE `short_term_memory`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for tables
--

ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `amanda_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `knowledge_base`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `short_term_memory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;