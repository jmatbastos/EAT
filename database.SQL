-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 04, 2026 at 05:25 PM
-- Server version: 5.1.73-community
-- PHP Version: 7.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `caies`
--

-- --------------------------------------------------------

--
-- Table structure for table `accesslogs`
--

CREATE TABLE `accesslogs` (
  `accesslogs_id` int(16) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `users_id` int(16) NOT NULL,
  `event` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `answerstoquestions`
--

CREATE TABLE `answerstoquestions` (
  `answers_id` int(16) NOT NULL,
  `usermadeexams_id` int(16) NOT NULL,
  `questions_id` int(16) NOT NULL,
  `answer` mediumtext,
  `answertypes_id` int(2) NOT NULL,
  `grade` int(2) DEFAULT NULL,
  `comments` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `answer_attained_curricular_goals`
--

CREATE TABLE `answer_attained_curricular_goals` (
  `answers_id` int(16) NOT NULL,
  `curriculargoals_id` int(16) NOT NULL,
  `percentage` int(11) NOT NULL COMMENT '100% - attained per complete the goal\n0% did not attain the goal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cognitivegoals`
--

CREATE TABLE `cognitivegoals` (
  `cognitivegoals_id` int(16) NOT NULL,
  `description` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `complexities`
--

CREATE TABLE `complexities` (
  `complexities_id` int(16) NOT NULL,
  `grade` int(2) NOT NULL DEFAULT '1',
  `description` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Complexity of the question in a scale 1 to 3 (1 - easy, 2 - ';

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `courses_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `languages_id` varchar(5) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `course_has_curricularunits`
--

CREATE TABLE `course_has_curricularunits` (
  `courses_id` int(11) NOT NULL,
  `curricularunits_id` int(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curricularchapters`
--

CREATE TABLE `curricularchapters` (
  `curricularchapters_id` int(16) NOT NULL,
  `description` varchar(256) NOT NULL,
  `curricularunits_id` int(16) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curriculargoals`
--

CREATE TABLE `curriculargoals` (
  `curriculargoals_id` int(16) NOT NULL,
  `description` text NOT NULL,
  `curricularsections_id` int(16) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curricularsections`
--

CREATE TABLE `curricularsections` (
  `curricularsections_id` int(16) NOT NULL,
  `description` varchar(256) NOT NULL,
  `curricularchapters_id` int(16) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curricularskills`
--

CREATE TABLE `curricularskills` (
  `curricularskills_id` int(16) NOT NULL,
  `description` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `skill` varchar(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curricularunits`
--

CREATE TABLE `curricularunits` (
  `curricularunits_id` int(16) NOT NULL,
  `name` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `year` int(16) NOT NULL,
  `languages_id` int(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `curricularunit_has_reviewers`
--

CREATE TABLE `curricularunit_has_reviewers` (
  `curricularunit_has_reviewers_id` int(11) NOT NULL,
  `curricularunits_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exams_id` int(16) NOT NULL,
  `curricularunits_id` int(3) NOT NULL,
  `users_id` int(16) NOT NULL,
  `languages_id` int(2) DEFAULT '2',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exams_name` varchar(256) DEFAULT NULL,
  `exams_introduction` mediumtext,
  `exams_questions` int(2) NOT NULL,
  `resolution_time` int(3) DEFAULT NULL COMMENT 'minutes',
  `exams_type` int(2) DEFAULT '1',
  `visibility` char(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `exams_questions_total`
-- (See below for the actual view)
--
CREATE TABLE `exams_questions_total` (
`exams_id` int(16)
,`questions_total` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `exam_has_questions`
--

CREATE TABLE `exam_has_questions` (
  `exams_id` int(16) NOT NULL,
  `questions_id` int(16) NOT NULL,
  `marks` int(2) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grades_id` int(16) NOT NULL,
  `grade` int(16) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `languages_id` int(2) NOT NULL,
  `code_main` varchar(2) NOT NULL COMMENT 'pt',
  `name` varchar(45) DEFAULT NULL COMMENT 'Portuguese (Br)\n',
  `sublanguage_of` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permissions_id` int(11) NOT NULL,
  `permission` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `questionmodes`
--

CREATE TABLE `questionmodes` (
  `questionmodes_id` int(11) NOT NULL,
  `mode` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `questions_id` int(16) NOT NULL,
  `languages_id` int(2) DEFAULT NULL,
  `curricularunits_id` int(16) DEFAULT NULL,
  `curricularchapters_id` int(16) DEFAULT NULL,
  `curricularsections_id` int(16) DEFAULT NULL,
  `questiontypes_id` int(11) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `complexities_id` int(16) DEFAULT NULL,
  `resolution_time` int(16) DEFAULT NULL COMMENT 'minutes',
  `correct_answer` mediumtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(16) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `questionmodes_id` int(11) DEFAULT NULL,
  `validated` tinyint(1) DEFAULT '0',
  `sources_id` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `questiontypes`
--

CREATE TABLE `questiontypes` (
  `questiontypes_id` int(11) NOT NULL,
  `type` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question_has_cognitivegoals`
--

CREATE TABLE `question_has_cognitivegoals` (
  `questions_id` int(16) NOT NULL,
  `cognitivegoals_id` int(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question_has_curricularskills`
--

CREATE TABLE `question_has_curricularskills` (
  `questions_id` int(16) NOT NULL,
  `curricularskills_id` int(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question_has_questiontypes`
--

CREATE TABLE `question_has_questiontypes` (
  `questions_id` int(16) NOT NULL,
  `questiontypes_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question_has_userprofiles`
--

CREATE TABLE `question_has_userprofiles` (
  `questions_id` int(16) NOT NULL,
  `userprofiles_id` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE `sources` (
  `sources_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `href` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usermadeexams`
--

CREATE TABLE `usermadeexams` (
  `usermadeexams_id` int(16) NOT NULL,
  `exams_id` int(16) NOT NULL,
  `users_id` int(16) NOT NULL,
  `grade` float DEFAULT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finish_date` timestamp NULL DEFAULT NULL,
  `timer` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `usermadeexams_total`
-- (See below for the actual view)
--
CREATE TABLE `usermadeexams_total` (
`exams_id` int(16)
,`attempts_total` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `userprofiles`
--

CREATE TABLE `userprofiles` (
  `userprofiles_id` varchar(4) NOT NULL,
  `profile` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(16) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `password_digest` varchar(255) DEFAULT NULL,
  `remember_digest` varchar(255) DEFAULT NULL,
  `activation_digest` varchar(255) DEFAULT NULL,
  `activated` tinyint(1) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `reset_digest` varchar(255) DEFAULT NULL,
  `reset_sent_at` datetime DEFAULT NULL,
  `roles` longtext COMMENT '(DC2Type:json)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usertypes`
--

CREATE TABLE `usertypes` (
  `usertypes_id` int(16) NOT NULL,
  `type` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usertype_has_permissions`
--

CREATE TABLE `usertype_has_permissions` (
  `usertypes_id` int(11) NOT NULL,
  `permissions_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usertype_translations`
--

CREATE TABLE `usertype_translations` (
  `name` varchar(45) DEFAULT NULL,
  `descriprion` varchar(45) DEFAULT NULL,
  `languages_id` varchar(5) NOT NULL,
  `usertypes_id` int(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_has_curricularunits`
--

CREATE TABLE `user_has_curricularunits` (
  `user_has_curricularunits_id` int(16) NOT NULL,
  `users_id` int(16) NOT NULL,
  `curricularunits_id` int(16) NOT NULL,
  `enrolled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `exams_questions_total`
--
DROP TABLE IF EXISTS `exams_questions_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`caies`@`%` SQL SECURITY DEFINER VIEW `exams_questions_total`  AS  select `exam_has_questions`.`exams_id` AS `exams_id`,count(`exam_has_questions`.`questions_id`) AS `questions_total` from `exam_has_questions` group by `exam_has_questions`.`exams_id` ;

-- --------------------------------------------------------

--
-- Structure for view `usermadeexams_total`
--
DROP TABLE IF EXISTS `usermadeexams_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`caies`@`%` SQL SECURITY DEFINER VIEW `usermadeexams_total`  AS  select `usermadeexams`.`exams_id` AS `exams_id`,count(`usermadeexams`.`exams_id`) AS `attempts_total` from `usermadeexams` group by `usermadeexams`.`exams_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accesslogs`
--
ALTER TABLE `accesslogs`
  ADD PRIMARY KEY (`accesslogs_id`),
  ADD KEY `fk_accesslogs_users` (`users_id`);

--
-- Indexes for table `answerstoquestions`
--
ALTER TABLE `answerstoquestions`
  ADD PRIMARY KEY (`answers_id`),
  ADD UNIQUE KEY `usermadeexams_id` (`usermadeexams_id`,`questions_id`),
  ADD KEY `questions_id` (`questions_id`),
  ADD KEY `answertypes_id` (`answertypes_id`);

--
-- Indexes for table `answer_attained_curricular_goals`
--
ALTER TABLE `answer_attained_curricular_goals`
  ADD KEY `fk_answers` (`answers_id`),
  ADD KEY `fk_curriculargoals` (`curriculargoals_id`);

--
-- Indexes for table `cognitivegoals`
--
ALTER TABLE `cognitivegoals`
  ADD PRIMARY KEY (`cognitivegoals_id`);

--
-- Indexes for table `complexities`
--
ALTER TABLE `complexities`
  ADD PRIMARY KEY (`complexities_id`),
  ADD KEY `fk_complexities_users` (`users_id`) USING BTREE;

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`courses_id`),
  ADD KEY `fk_Course_Language1_idx` (`languages_id`),
  ADD KEY `fk_courses_users` (`users_id`);

--
-- Indexes for table `course_has_curricularunits`
--
ALTER TABLE `course_has_curricularunits`
  ADD KEY `fk_Course_has_CurricularUnit_Course1_idx` (`courses_id`),
  ADD KEY `fk_Course_has_CurricularUnit_CurricularUnit1_idx` (`curricularunits_id`);

--
-- Indexes for table `curricularchapters`
--
ALTER TABLE `curricularchapters`
  ADD PRIMARY KEY (`curricularchapters_id`),
  ADD KEY `curricularunits_id` (`curricularunits_id`),
  ADD KEY `fk_curricularchapters_users` (`users_id`);

--
-- Indexes for table `curriculargoals`
--
ALTER TABLE `curriculargoals`
  ADD PRIMARY KEY (`curriculargoals_id`),
  ADD KEY `fk_curriculargoals_users_id` (`users_id`),
  ADD KEY `fk_curriculargoals_curricularsections_id` (`curricularsections_id`);

--
-- Indexes for table `curricularsections`
--
ALTER TABLE `curricularsections`
  ADD PRIMARY KEY (`curricularsections_id`),
  ADD KEY `fk_curricularsections_users` (`users_id`),
  ADD KEY `fk_curricularsections_curricularchapters_id` (`curricularchapters_id`);

--
-- Indexes for table `curricularskills`
--
ALTER TABLE `curricularskills`
  ADD PRIMARY KEY (`curricularskills_id`),
  ADD KEY `fk_curricularskills_users` (`users_id`) USING BTREE;

--
-- Indexes for table `curricularunits`
--
ALTER TABLE `curricularunits`
  ADD PRIMARY KEY (`curricularunits_id`),
  ADD KEY `fk_curricularunits_users` (`users_id`),
  ADD KEY `fk_curicularunits_languages` (`languages_id`) USING BTREE;

--
-- Indexes for table `curricularunit_has_reviewers`
--
ALTER TABLE `curricularunit_has_reviewers`
  ADD PRIMARY KEY (`curricularunit_has_reviewers_id`),
  ADD KEY `curricularunits_id` (`curricularunits_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exams_id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `curricularunits_id` (`curricularunits_id`),
  ADD KEY `languages_id` (`languages_id`);

--
-- Indexes for table `exam_has_questions`
--
ALTER TABLE `exam_has_questions`
  ADD UNIQUE KEY `exams_id` (`exams_id`,`questions_id`),
  ADD KEY `fk_exams` (`exams_id`),
  ADD KEY `fk_questions` (`questions_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grades_id`),
  ADD KEY `fk_grades_users` (`users_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`languages_id`),
  ADD KEY `fk_Language_Language1_idx` (`sublanguage_of`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permissions_id`);

--
-- Indexes for table `questionmodes`
--
ALTER TABLE `questionmodes`
  ADD PRIMARY KEY (`questionmodes_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`questions_id`),
  ADD KEY `fk_languages_id` (`languages_id`),
  ADD KEY `fk_curricularunits_id` (`curricularunits_id`),
  ADD KEY `fk_curricularchapters_id` (`curricularchapters_id`),
  ADD KEY `fk_curricularsections_id` (`curricularsections_id`),
  ADD KEY `fk_complexities_id` (`complexities_id`),
  ADD KEY `fk_questiontypes_id` (`questiontypes_id`),
  ADD KEY `fk_users_id` (`users_id`),
  ADD KEY `questionmodes_id` (`questionmodes_id`),
  ADD KEY `fk_sources_id` (`sources_id`);

--
-- Indexes for table `questiontypes`
--
ALTER TABLE `questiontypes`
  ADD PRIMARY KEY (`questiontypes_id`);

--
-- Indexes for table `question_has_cognitivegoals`
--
ALTER TABLE `question_has_cognitivegoals`
  ADD UNIQUE KEY `questions_id` (`questions_id`,`cognitivegoals_id`),
  ADD KEY `fk_questions_cognitivegoals` (`questions_id`),
  ADD KEY `fk_cognitivegoals2` (`cognitivegoals_id`);

--
-- Indexes for table `question_has_curricularskills`
--
ALTER TABLE `question_has_curricularskills`
  ADD UNIQUE KEY `questions_id` (`questions_id`,`curricularskills_id`),
  ADD KEY `curricularskills_id` (`curricularskills_id`);

--
-- Indexes for table `question_has_questiontypes`
--
ALTER TABLE `question_has_questiontypes`
  ADD KEY `fk_questiontypes_idx` (`questiontypes_id`),
  ADD KEY `fk_questions2` (`questions_id`);

--
-- Indexes for table `question_has_userprofiles`
--
ALTER TABLE `question_has_userprofiles`
  ADD KEY `fk_questions_userprofiles` (`questions_id`),
  ADD KEY `fk_userprofiles2` (`userprofiles_id`);

--
-- Indexes for table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`sources_id`),
  ADD UNIQUE KEY `description` (`description`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `usermadeexams`
--
ALTER TABLE `usermadeexams`
  ADD PRIMARY KEY (`usermadeexams_id`),
  ADD KEY `exams_id` (`exams_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `userprofiles`
--
ALTER TABLE `userprofiles`
  ADD PRIMARY KEY (`userprofiles_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_ibfk_1` (`created_by`);

--
-- Indexes for table `usertypes`
--
ALTER TABLE `usertypes`
  ADD PRIMARY KEY (`usertypes_id`);

--
-- Indexes for table `usertype_has_permissions`
--
ALTER TABLE `usertype_has_permissions`
  ADD KEY `fk_permissions_idx` (`permissions_id`),
  ADD KEY `fk_usertypes` (`usertypes_id`);

--
-- Indexes for table `usertype_translations`
--
ALTER TABLE `usertype_translations`
  ADD KEY `fk_UserTypeTranslation_Language1_idx` (`languages_id`),
  ADD KEY `fk_UserTypeTranslation_UserType1_idx` (`usertypes_id`);

--
-- Indexes for table `user_has_curricularunits`
--
ALTER TABLE `user_has_curricularunits`
  ADD PRIMARY KEY (`user_has_curricularunits_id`),
  ADD UNIQUE KEY `users_id` (`users_id`,`curricularunits_id`),
  ADD KEY `curricularunits_id` (`curricularunits_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accesslogs`
--
ALTER TABLE `accesslogs`
  MODIFY `accesslogs_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `answerstoquestions`
--
ALTER TABLE `answerstoquestions`
  MODIFY `answers_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cognitivegoals`
--
ALTER TABLE `cognitivegoals`
  MODIFY `cognitivegoals_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complexities`
--
ALTER TABLE `complexities`
  MODIFY `complexities_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `courses_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricularchapters`
--
ALTER TABLE `curricularchapters`
  MODIFY `curricularchapters_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculargoals`
--
ALTER TABLE `curriculargoals`
  MODIFY `curriculargoals_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricularsections`
--
ALTER TABLE `curricularsections`
  MODIFY `curricularsections_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricularskills`
--
ALTER TABLE `curricularskills`
  MODIFY `curricularskills_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricularunits`
--
ALTER TABLE `curricularunits`
  MODIFY `curricularunits_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curricularunit_has_reviewers`
--
ALTER TABLE `curricularunit_has_reviewers`
  MODIFY `curricularunit_has_reviewers_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exams_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grades_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `languages_id` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permissions_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questionmodes`
--
ALTER TABLE `questionmodes`
  MODIFY `questionmodes_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `questions_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questiontypes`
--
ALTER TABLE `questiontypes`
  MODIFY `questiontypes_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sources`
--
ALTER TABLE `sources`
  MODIFY `sources_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usermadeexams`
--
ALTER TABLE `usermadeexams`
  MODIFY `usermadeexams_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usertypes`
--
ALTER TABLE `usertypes`
  MODIFY `usertypes_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_has_curricularunits`
--
ALTER TABLE `user_has_curricularunits`
  MODIFY `user_has_curricularunits_id` int(16) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accesslogs`
--
ALTER TABLE `accesslogs`
  ADD CONSTRAINT `accesslogs_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `answerstoquestions`
--
ALTER TABLE `answerstoquestions`
  ADD CONSTRAINT `answerstoquestions_ibfk_1` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `answerstoquestions_ibfk_2` FOREIGN KEY (`usermadeexams_id`) REFERENCES `usermadeexams` (`usermadeexams_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `answerstoquestions_ibfk_3` FOREIGN KEY (`answertypes_id`) REFERENCES `questiontypes` (`questiontypes_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `answer_attained_curricular_goals`
--
ALTER TABLE `answer_attained_curricular_goals`
  ADD CONSTRAINT `fk_answers` FOREIGN KEY (`answers_id`) REFERENCES `answers` (`answers_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_curriculargoals` FOREIGN KEY (`curriculargoals_id`) REFERENCES `curricularskills` (`curricularskills_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `complexities`
--
ALTER TABLE `complexities`
  ADD CONSTRAINT `fk_complexities_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_users` FOREIGN KEY (`users_id`) REFERENCES `users_org` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `course_has_curricularunits`
--
ALTER TABLE `course_has_curricularunits`
  ADD CONSTRAINT `fk_Course_has_CurricularUnit_Course1_idx` FOREIGN KEY (`courses_id`) REFERENCES `courses` (`courses_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Course_has_CurricularUnit_CurricularUnit1_idx` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curricularchapters`
--
ALTER TABLE `curricularchapters`
  ADD CONSTRAINT `curricularchapters_ibfk_1` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curricularchapters_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curriculargoals`
--
ALTER TABLE `curriculargoals`
  ADD CONSTRAINT `fk_curriculargoals_curricularsections_id` FOREIGN KEY (`curricularsections_id`) REFERENCES `curricularsections` (`curricularsections_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curriculargoals_users_id` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curricularsections`
--
ALTER TABLE `curricularsections`
  ADD CONSTRAINT `fk_curricularsections_curricularchapters_id` FOREIGN KEY (`curricularchapters_id`) REFERENCES `curricularchapters` (`curricularchapters_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curricularsections_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curricularskills`
--
ALTER TABLE `curricularskills`
  ADD CONSTRAINT `fk_curricularskills_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curricularunits`
--
ALTER TABLE `curricularunits`
  ADD CONSTRAINT `fk_curricularunits_languages` FOREIGN KEY (`languages_id`) REFERENCES `languages` (`languages_id`),
  ADD CONSTRAINT `fk_curricularunits_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `curricularunit_has_reviewers`
--
ALTER TABLE `curricularunit_has_reviewers`
  ADD CONSTRAINT `reviewers_ibfk_1` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`),
  ADD CONSTRAINT `reviewers_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`),
  ADD CONSTRAINT `exams_ibfk_3` FOREIGN KEY (`languages_id`) REFERENCES `languages` (`languages_id`);

--
-- Constraints for table `exam_has_questions`
--
ALTER TABLE `exam_has_questions`
  ADD CONSTRAINT `fk_exams` FOREIGN KEY (`exams_id`) REFERENCES `exams` (`exams_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_questions` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_users` FOREIGN KEY (`users_id`) REFERENCES `users_org` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_complexities_id` FOREIGN KEY (`complexities_id`) REFERENCES `complexities` (`complexities_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_curricularchapters_id` FOREIGN KEY (`curricularchapters_id`) REFERENCES `curricularchapters` (`curricularchapters_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_curricularsections_id` FOREIGN KEY (`curricularsections_id`) REFERENCES `curricularsections` (`curricularsections_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_curricularunits_id` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_languages_id` FOREIGN KEY (`languages_id`) REFERENCES `languages` (`languages_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_questiontypes_id` FOREIGN KEY (`questiontypes_id`) REFERENCES `questiontypes` (`questiontypes_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sources_id` FOREIGN KEY (`sources_id`) REFERENCES `sources` (`sources_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_users_id` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`questionmodes_id`) REFERENCES `questionmodes` (`questionmodes_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `question_has_cognitivegoals`
--
ALTER TABLE `question_has_cognitivegoals`
  ADD CONSTRAINT `fk_cognitivegoals2` FOREIGN KEY (`cognitivegoals_id`) REFERENCES `cognitivegoals` (`cognitivegoals_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_questions_cognitivegoals` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `question_has_curricularskills`
--
ALTER TABLE `question_has_curricularskills`
  ADD CONSTRAINT `question_has_curricularskills_ibfk_1` FOREIGN KEY (`curricularskills_id`) REFERENCES `curricularskills` (`curricularskills_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `question_has_curricularskills_ibfk_2` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `question_has_questiontypes`
--
ALTER TABLE `question_has_questiontypes`
  ADD CONSTRAINT `fk_questions2` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_questiontypes` FOREIGN KEY (`questiontypes_id`) REFERENCES `questiontypes` (`questiontypes_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `question_has_userprofiles`
--
ALTER TABLE `question_has_userprofiles`
  ADD CONSTRAINT `fk_questions_userprofiles` FOREIGN KEY (`questions_id`) REFERENCES `questions` (`questions_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_userprofiles2` FOREIGN KEY (`userprofiles_id`) REFERENCES `userprofiles` (`userprofiles_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `reset_password_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `sources`
--
ALTER TABLE `sources`
  ADD CONSTRAINT `sources_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `usermadeexams`
--
ALTER TABLE `usermadeexams`
  ADD CONSTRAINT `usermadeexams_ibfk_1` FOREIGN KEY (`exams_id`) REFERENCES `exams` (`exams_id`),
  ADD CONSTRAINT `usermadeexams_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `usertype_has_permissions`
--
ALTER TABLE `usertype_has_permissions`
  ADD CONSTRAINT `fk_permissions` FOREIGN KEY (`permissions_id`) REFERENCES `permissions` (`permissions_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usertypes` FOREIGN KEY (`usertypes_id`) REFERENCES `usertypes` (`usertypes_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `usertype_translations`
--
ALTER TABLE `usertype_translations`
  ADD CONSTRAINT `fk_usertypes1` FOREIGN KEY (`usertypes_id`) REFERENCES `usertypes` (`usertypes_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_has_curricularunits`
--
ALTER TABLE `user_has_curricularunits`
  ADD CONSTRAINT `user_has_curricularunits_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_has_curricularunits_ibfk_2` FOREIGN KEY (`curricularunits_id`) REFERENCES `curricularunits` (`curricularunits_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
