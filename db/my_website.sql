-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 22 avr. 2025 à 00:04
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `my_website`
--

-- --------------------------------------------------------

--
-- Structure de la table `application`
--

CREATE TABLE `application` (
  `id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `website` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `company`
--

INSERT INTO `company` (`id`, `name`, `location`, `website`, `description`, `domain_id`, `created_at`, `updated_at`) VALUES
(5, 'telecom', 'Tiaret, Algeria', 'https://www.deepseek.com/', 'hi', NULL, '2025-04-20 23:23:57', '2025-04-20 23:23:57'),
(6, 'telecom', 'Tiaret, Algeria', 'https://www.deepseek.com/', 'kk', NULL, '2025-04-20 23:28:59', '2025-04-20 23:28:59'),
(7, 'solangaz', 'Tiaret, Algeria', 'https://www.deepseek.com/', '55', NULL, '2025-04-21 20:45:14', '2025-04-21 20:45:14');

-- --------------------------------------------------------

--
-- Structure de la table `domain`
--

CREATE TABLE `domain` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `level` enum('BAC','Licence','Master','Doctorat','Ingeniorat') DEFAULT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `univ_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `education`
--

INSERT INTO `education` (`id`, `level`, `speciality`, `univ_name`, `start_date`, `end_date`, `user_id`, `created_at`, `updated_at`) VALUES
(12, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-18 10:44:44', '2025-04-18 10:44:44'),
(13, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-18 14:04:54', '2025-04-18 14:04:54'),
(14, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-19 11:09:04', '2025-04-19 11:09:04'),
(15, 'Licence', 'software enginier', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-19 19:22:45', '2025-04-19 19:22:45'),
(16, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 10:21:01', '2025-04-20 10:21:01'),
(17, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 10:34:59', '2025-04-20 10:34:59'),
(18, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 17:25:21', '2025-04-20 17:25:21'),
(19, 'Licence', 'nami', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 18:53:20', '2025-04-20 18:53:20'),
(20, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 18:54:01', '2025-04-20 18:54:01'),
(21, 'Licence', 'master', 'ibn khaldoun university', '2022-09-01', '2023-06-15', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23');

-- --------------------------------------------------------

--
-- Structure de la table `experience`
--

CREATE TABLE `experience` (
  `id` int(11) NOT NULL,
  `job_name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `experience`
--

INSERT INTO `experience` (`id`, `job_name`, `company_name`, `start_date`, `end_date`, `description`, `user_id`, `created_at`, `updated_at`) VALUES
(10, 'software enginier', 'ibn khaldoun', '2024-10-31', '2025-10-31', 'nothing', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23');

-- --------------------------------------------------------

--
-- Structure de la table `job`
--

CREATE TABLE `job` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `type_contract` varchar(255) DEFAULT NULL,
  `salary` double DEFAULT NULL,
  `status` enum('pending','approved','rejected','') DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `recruiter_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `job`
--

INSERT INTO `job` (`id`, `title`, `mission`, `type_contract`, `salary`, `status`, `expiration_date`, `category_id`, `recruiter_id`, `created_at`, `updated_at`) VALUES
(1, 'software enginier', 'gg', 'Part-time', 122000, 'pending', '2025-05-21', NULL, 2, '2025-04-20 22:21:45', '2025-04-21 21:53:11'),
(4, 'software enginier', 'wlw', 'Contract', 200000, 'approved', '2025-05-21', NULL, 2, '2025-04-20 23:28:59', '2025-04-21 21:32:32'),
(5, 'software enginier', 'kk', 'Full-time', 200000, 'approved', '2025-05-21', NULL, 2, '2025-04-21 20:45:14', '2025-04-21 21:31:29');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recruiter_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('cv_submission','cv_approval','cv_rejection','job_approval','job_rejection') DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `recruiter_id`, `admin_id`, `message`, `type`, `related_id`, `is_read`, `created_at`) VALUES
(12, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-18 10:44:44'),
(13, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-18 10:44:44'),
(14, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-18 14:04:54'),
(15, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-18 14:04:54'),
(16, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-19 11:09:04'),
(17, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-19 11:09:04'),
(18, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-19 19:22:45'),
(19, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-19 19:22:45'),
(20, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 10:21:01'),
(21, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 10:21:01'),
(22, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 10:34:59'),
(23, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 10:34:59'),
(24, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 17:25:21'),
(25, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 17:25:21'),
(26, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 18:53:20'),
(27, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 18:53:20'),
(28, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 18:54:01'),
(29, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 18:54:01'),
(30, NULL, 0, 2, 'New CV uploaded by candidat first needs approval', 'cv_submission', 3, 0, '2025-04-20 19:04:23'),
(31, 3, 0, NULL, 'Your CV has been submitted for admin approval', 'cv_submission', 3, 0, '2025-04-20 19:04:23'),
(32, NULL, 0, 2, 'New job posted by  needs approval', '', 1, 0, '2025-04-20 22:21:45'),
(33, NULL, 0, 2, 'New job posted by  needs approval', 'job_approval', 2, 0, '2025-04-20 23:17:32'),
(34, NULL, 0, 2, 'New job posted by  needs approval', 'job_approval', 3, 0, '2025-04-20 23:23:57'),
(35, NULL, 0, 2, 'New job posted by  needs approval', 'job_approval', 4, 0, '2025-04-20 23:28:59'),
(36, NULL, 0, 2, 'New job posted by  needs approval', 'job_approval', 5, 0, '2025-04-21 20:45:14'),
(37, 2, 0, NULL, 'Your job \'software enginier\' has been approved', 'job_approval', 5, 0, '2025-04-21 21:31:29'),
(38, 2, 0, 0, 'Your job \'software enginier\' has been approved', 'job_approval', 5, 0, '2025-04-21 21:31:44'),
(39, 2, 0, 0, 'Your job \'software enginier\' has been approved', 'job_approval', 4, 0, '2025-04-21 21:32:32'),
(40, 2, 0, 0, 'Your job \'software enginier\' has been approved', 'job_approval', 4, 0, '2025-04-21 21:33:29');

-- --------------------------------------------------------

--
-- Structure de la table `recruiter`
--

CREATE TABLE `recruiter` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` text DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recruiter`
--

INSERT INTO `recruiter` (`id`, `first_name`, `last_name`, `email`, `password`, `address`, `profile_picture`, `company_id`, `created_at`, `updated_at`) VALUES
(2, 'recruiter', 'first', 'recruiterfirst@gmail.com', '$2y$10$bSEoRN/RxkBBXBOPwDwlUeND1a3luSrgKatkvmC725zxV7VL.XuT6', 'Tiaret zaroura', 'uploads/profile_pictures/6806c01e4a49a_87d22a0e79a80260407649ffb5862452.jpg', 7, '2025-04-18 10:17:30', '2025-04-21 22:01:02');

-- --------------------------------------------------------

--
-- Structure de la table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `skills`
--

INSERT INTO `skills` (`id`, `content`, `user_id`, `created_at`, `updated_at`) VALUES
(79, '[{\"value\":\"JavaScript\"}', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23'),
(80, '{\"value\":\"CSS\"}', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23'),
(81, '{\"value\":\"cc\"}', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23'),
(82, '{\"value\":\"React\"}', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23'),
(83, '{\"value\":\"C++\"}]', 3, '2025-04-20 19:04:23', '2025-04-20 19:04:23');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `sexe` enum('man','woman') DEFAULT NULL,
  `about` text DEFAULT NULL,
  `type` enum('candidat','admin') DEFAULT 'candidat',
  `cv` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `profile_picture` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `password`, `email`, `address`, `phone`, `sexe`, `about`, `type`, `cv`, `status`, `profile_picture`, `created_at`, `updated_at`) VALUES
(2, 'ELHABASH', 'FARES', '$2y$10$m0sC5i5pkjy5fCW.9j/7tepJO3EWWfhCTCtJfxgXpeLxG6zXogIKy', 'admin@gmail.com', 'Tiaret zaroura', '0552595513', 'man', '', 'admin', NULL, NULL, 'uploads/profile_pictures/680227566fb0a_2023_05_30_20_38_IMG_4151.JPG', '2025-04-18 01:20:04', '2025-04-19 11:27:39'),
(3, 'candidat', 'first', '$2y$10$gE/ueYLe0TfbILrScvCpq.ukHtYChSXRXlEvNDjVg1F3Gso53RMxi', 'candidatfirst@gmail.com', 'Adrar', '0552595513', 'man', 'i am a candidat', 'candidat', 'uploads/cvs/68022d1c778dd.pdf', 'active', 'uploads/profile_pictures/68022a4b24f28_2023_05_11_15_08_IMG_3538.JPG', '2025-04-18 10:29:08', '2025-04-20 19:04:46');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`);

--
-- Index pour la table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain_id` (`domain_id`);

--
-- Index pour la table `domain`
--
ALTER TABLE `domain`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `recruter_id` (`recruiter_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Index pour la table `recruiter`
--
ALTER TABLE `recruiter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- Index pour la table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `application`
--
ALTER TABLE `application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `domain`
--
ALTER TABLE `domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `experience`
--
ALTER TABLE `experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `job`
--
ALTER TABLE `job`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `recruiter`
--
ALTER TABLE `recruiter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `application_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `application_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job` (`id`);

--
-- Contraintes pour la table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`);

--
-- Contraintes pour la table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`);

--
-- Contraintes pour la table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `experience_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `job`
--
ALTER TABLE `job`
  ADD CONSTRAINT `job_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `job_ibfk_2` FOREIGN KEY (`recruiter_id`) REFERENCES `recruiter` (`id`);

--
-- Contraintes pour la table `recruiter`
--
ALTER TABLE `recruiter`
  ADD CONSTRAINT `recruiter_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`);

--
-- Contraintes pour la table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
