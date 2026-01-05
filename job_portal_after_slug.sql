-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 03:08 PM
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
-- Database: `job_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(4, 'admin', '$2y$10$f/7G9r4QGGjydtbnoBv5nObt/P7LkEoJ4L22MSYpWEgKNhGc3aDjS', NULL, '2026-01-02 11:54:38'),
(5, 'laasya', '$2y$10$nsBsZ5mqpY15yj4LfTiw/.7dd3rAiJKIxLa1/jPcc23xRdnqV8RbO', '21jr1a4380@gmail.com', '2026-01-02 15:40:47'),
(6, 'siva', '$2y$10$4oUOAvL8KNvojFbxtL3JzOrK/9cezvb/CHvNRu793NLenpu9bD4eK', '', '2026-01-05 12:06:04');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `notification_number` varchar(100) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `total_vacancies` int(11) DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `recruitment_board` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `job_link` varchar(500) NOT NULL,
  `official_website` varchar(255) DEFAULT NULL,
  `work_mode` enum('Work from Home','On-site','Hybrid') DEFAULT 'On-site',
  `employment_type` enum('Full-time','Part-time','Internship') DEFAULT 'Full-time',
  `experience_level` enum('Freshers','0-2 years','2-5 years','5+ years') DEFAULT 'Freshers',
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `posted_date` date NOT NULL,
  `application_deadline` date DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vacancy_breakdown` text DEFAULT NULL COMMENT 'JSON format for position-wise breakdown',
  `min_qualification_id` int(11) DEFAULT NULL COMMENT 'FK to master_qualifications',
  `required_percentage` varchar(50) DEFAULT NULL,
  `age_limit_min` int(11) DEFAULT NULL,
  `age_limit_max` int(11) DEFAULT NULL,
  `age_relaxation` text DEFAULT NULL,
  `required_experience` varchar(100) DEFAULT NULL,
  `salary_min` decimal(10,2) DEFAULT NULL,
  `salary_max` decimal(10,2) DEFAULT NULL,
  `pay_scale` varchar(100) DEFAULT NULL,
  `allowances_details` text DEFAULT NULL,
  `selection_process` text DEFAULT NULL COMMENT 'Exam/Interview stages',
  `exam_pattern` text DEFAULT NULL COMMENT 'Exam structure details',
  `notification_date` date DEFAULT NULL,
  `last_date_to_apply` date DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  `eligibility_cutoff_date` date DEFAULT NULL,
  `application_fee_general` decimal(10,2) DEFAULT NULL,
  `application_fee_obc` decimal(10,2) DEFAULT NULL,
  `application_fee_sc_st` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(100) DEFAULT 'Online',
  `notification_pdf` varchar(255) DEFAULT NULL,
  `syllabus_pdf` varchar(255) DEFAULT NULL,
  `admit_card_link` varchar(255) DEFAULT NULL,
  `answer_key_link` varchar(255) DEFAULT NULL,
  `job_category_id` int(11) DEFAULT NULL COMMENT 'FK to master_job_categories',
  `work_mode_id` int(11) DEFAULT NULL COMMENT 'FK to master_work_modes',
  `employment_type_id` int(11) DEFAULT NULL COMMENT 'FK to master_employment_types',
  `experience_level_id` int(11) DEFAULT NULL COMMENT 'FK to master_experience_levels',
  `state_id` int(11) DEFAULT NULL COMMENT 'FK to master_states',
  `department_id` int(11) DEFAULT NULL COMMENT 'FK to master_departments',
  `important_instructions` text DEFAULT NULL,
  `how_to_apply_steps` text DEFAULT NULL,
  `short_info` text DEFAULT NULL COMMENT 'Brief summary for listing page',
  `view_count` int(11) DEFAULT 0,
  `apply_click_count` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `notification_number`, `title`, `slug`, `total_vacancies`, `company`, `recruitment_board`, `description`, `job_link`, `official_website`, `work_mode`, `employment_type`, `experience_level`, `location`, `category`, `posted_date`, `application_deadline`, `is_active`, `created_at`, `vacancy_breakdown`, `min_qualification_id`, `required_percentage`, `age_limit_min`, `age_limit_max`, `age_relaxation`, `required_experience`, `salary_min`, `salary_max`, `pay_scale`, `allowances_details`, `selection_process`, `exam_pattern`, `notification_date`, `last_date_to_apply`, `exam_date`, `result_date`, `eligibility_cutoff_date`, `application_fee_general`, `application_fee_obc`, `application_fee_sc_st`, `payment_mode`, `notification_pdf`, `syllabus_pdf`, `admit_card_link`, `answer_key_link`, `job_category_id`, `work_mode_id`, `employment_type_id`, `experience_level_id`, `state_id`, `department_id`, `important_instructions`, `how_to_apply_steps`, `short_info`, `view_count`, `apply_click_count`, `updated_at`) VALUES
(1, NULL, 'Software Employee', 'software-employee-at-stanny', NULL, 'stanny', NULL, '', 'https://docs.google.com/forms/d/e/1FAIpQLScZC8OuWE34o41AQMwjeVdte7kbqtsZhjxF-s43W8Xrc0hrpg/viewform?vc=0&amp;amp;amp;amp;amp;c=0&amp;amp;amp;amp;amp;w=1&amp;amp;amp;amp;amp;flr=0', NULL, 'Work from Home', 'Part-time', 'Freshers', 'Remote', 'IT', '2026-01-02', '2026-01-05', 1, '2026-01-02 15:28:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 28, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-01-05 13:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `jobs_backup`
--

CREATE TABLE `jobs_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) NOT NULL,
  `company` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `job_link` varchar(500) NOT NULL,
  `work_mode` enum('Work from Home','On-site','Hybrid') DEFAULT 'On-site',
  `employment_type` enum('Full-time','Part-time','Internship') DEFAULT 'Full-time',
  `experience_level` enum('Freshers','0-2 years','2-5 years','5+ years') DEFAULT 'Freshers',
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `posted_date` date NOT NULL,
  `application_deadline` date DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs_backup`
--

INSERT INTO `jobs_backup` (`id`, `title`, `company`, `description`, `job_link`, `work_mode`, `employment_type`, `experience_level`, `location`, `category`, `posted_date`, `application_deadline`, `is_active`, `created_at`) VALUES
(1, 'Software Employee', 'stanny', '', 'https://docs.google.com/forms/d/e/1FAIpQLScZC8OuWE34o41AQMwjeVdte7kbqtsZhjxF-s43W8Xrc0hrpg/viewform?vc=0&amp;amp;c=0&amp;amp;w=1&amp;amp;flr=0', 'Work from Home', 'Part-time', 'Freshers', 'Remote', 'IT', '2026-01-02', '2026-01-02', 1, '2026-01-02 15:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `job_documents`
--

CREATE TABLE `job_documents` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `document_type` enum('Notification','Syllabus','Admit Card','Answer Key','Result','Other') NOT NULL,
  `document_name` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL COMMENT 'Size in KB',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_important_dates`
--

CREATE TABLE `job_important_dates` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `event_name` varchar(200) NOT NULL,
  `event_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_vacancies_breakdown`
--

CREATE TABLE `job_vacancies_breakdown` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `position_name` varchar(200) NOT NULL,
  `scale` varchar(100) DEFAULT NULL,
  `vacancies` int(11) NOT NULL,
  `age_limit` varchar(50) DEFAULT NULL,
  `qualification` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_views`
--

CREATE TABLE `job_views` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_departments`
--

CREATE TABLE `master_departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(200) NOT NULL,
  `department_type` enum('Central','State','PSU','Private','Other') DEFAULT 'Other',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_departments`
--

INSERT INTO `master_departments` (`id`, `department_name`, `department_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Staff Selection Commission (SSC)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(2, 'Union Public Service Commission (UPSC)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(3, 'Railway Recruitment Board (RRB)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(4, 'Institute of Banking Personnel Selection (IBPS)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(5, 'State Bank of India (SBI)', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(6, 'Reserve Bank of India (RBI)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(7, 'Food Corporation of India (FCI)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(8, 'Indian Army', 'Central', 1, '2026-01-05 06:45:30', NULL),
(9, 'Indian Navy', 'Central', 1, '2026-01-05 06:45:30', NULL),
(10, 'Indian Air Force', 'Central', 1, '2026-01-05 06:45:30', NULL),
(11, 'Border Security Force (BSF)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(12, 'Central Reserve Police Force (CRPF)', 'Central', 1, '2026-01-05 06:45:30', NULL),
(13, 'Delhi Police', 'State', 1, '2026-01-05 06:45:30', NULL),
(14, 'AIIMS', 'Central', 1, '2026-01-05 06:45:30', NULL),
(15, 'ISRO', 'Central', 1, '2026-01-05 06:45:30', NULL),
(16, 'DRDO', 'Central', 1, '2026-01-05 06:45:30', NULL),
(17, 'NTPC', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(18, 'BHEL', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(19, 'ONGC', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(20, 'Indian Oil Corporation', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(21, 'Coal India Limited', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(22, 'Power Grid Corporation', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(23, 'BSNL', 'PSU', 1, '2026-01-05 06:45:30', NULL),
(24, 'India Post', 'Central', 1, '2026-01-05 06:45:30', NULL),
(25, 'Municipal Corporation', 'State', 1, '2026-01-05 06:45:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_employment_types`
--

CREATE TABLE `master_employment_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT '⏰',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_employment_types`
--

INSERT INTO `master_employment_types` (`id`, `type_name`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Full-time', '⏰', 1, 1, '2026-01-05 06:45:30', NULL),
(2, 'Part-time', '🕐', 2, 1, '2026-01-05 06:45:30', NULL),
(3, 'Internship', '🎓', 3, 1, '2026-01-05 06:45:30', NULL),
(4, 'Contract', '📄', 4, 1, '2026-01-05 06:45:30', NULL),
(5, 'Temporary', '⏳', 5, 1, '2026-01-05 06:45:30', NULL),
(6, 'Permanent', '✅', 6, 1, '2026-01-05 06:45:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_experience_levels`
--

CREATE TABLE `master_experience_levels` (
  `id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT '?',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_experience_levels`
--

INSERT INTO `master_experience_levels` (`id`, `level_name`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Freshers', '🎓', 1, 1, '2026-01-05 06:45:30', NULL),
(2, '0-1 years', '📊', 2, 1, '2026-01-05 06:45:30', NULL),
(3, '1-2 years', '📈', 3, 1, '2026-01-05 06:45:30', NULL),
(4, '2-5 years', '💼', 4, 1, '2026-01-05 06:45:30', NULL),
(5, '5-10 years', '🏆', 5, 1, '2026-01-05 06:45:30', NULL),
(6, '10+ years', '👔', 6, 1, '2026-01-05 06:45:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_job_categories`
--

CREATE TABLE `master_job_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT '?',
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_job_categories`
--

INSERT INTO `master_job_categories` (`id`, `category_name`, `category_slug`, `icon`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Government', 'government', '🏛️', '', 1, 1, '2026-01-05 06:45:30', '2026-01-05 11:46:23'),
(2, 'Private Sector', 'private-sector', '🏢', NULL, 2, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(3, 'Public Sector Undertaking (PSU)', 'psu', '🏭', NULL, 3, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(4, 'Banking', 'banking', '🏦', NULL, 4, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(5, 'Defense', 'defense', '🛡️', NULL, 5, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(6, 'Railway', 'railway', '🚂', NULL, 6, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(7, 'Teaching', 'teaching', '👨‍🏫', NULL, 7, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(8, 'Medical & Healthcare', 'medical', '🏥', NULL, 8, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(9, 'Police & Security', 'police', '👮', NULL, 9, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(10, 'Engineering', 'engineering', '⚙️', NULL, 10, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(11, 'IT & Software', 'it-software', '💻', NULL, 11, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(12, 'SSC (Staff Selection Commission)', 'ssc', '📝', NULL, 12, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(13, 'UPSC', 'upsc', '🎓', NULL, 13, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(14, 'State PSC', 'state-psc', '📋', NULL, 14, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(15, 'Judiciary', 'judiciary', '⚖️', NULL, 15, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(16, 'Insurance', 'insurance', '🛡️', NULL, 16, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(17, 'Postal Services', 'postal', '📮', NULL, 17, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(18, 'Telecom', 'telecom', '📡', NULL, 18, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(19, 'Power & Energy', 'power-energy', '⚡', NULL, 19, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(20, 'Oil & Gas', 'oil-gas', '🛢️', NULL, 20, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(21, 'Aviation', 'aviation', '✈️', NULL, 21, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(22, 'Research & Development', 'research', '🔬', NULL, 22, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(23, 'Agriculture', 'agriculture', '🌾', NULL, 23, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(24, 'Forest & Wildlife', 'forest', '🌳', NULL, 24, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(25, 'Municipal Corporation', 'municipal', '🏛️', NULL, 25, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(26, 'Internship', 'internship', '🎯', NULL, 26, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(27, 'Other', 'other', '📁', NULL, 27, 1, '2026-01-05 06:45:30', '2026-01-05 06:45:30'),
(28, 'Software Job', 'software-job', '👨‍💻', '', 1, 1, '2026-01-05 12:02:13', '2026-01-05 12:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `master_qualifications`
--

CREATE TABLE `master_qualifications` (
  `id` int(11) NOT NULL,
  `qualification_name` varchar(100) NOT NULL,
  `qualification_level` enum('10th','12th','Diploma','Graduate','Post Graduate','Doctorate','Other') DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_qualifications`
--

INSERT INTO `master_qualifications` (`id`, `qualification_name`, `qualification_level`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '10th Pass', '10th', 1, 1, '2026-01-05 06:45:30', NULL),
(2, '12th Pass', '12th', 2, 1, '2026-01-05 06:45:30', NULL),
(3, 'ITI', 'Diploma', 3, 1, '2026-01-05 06:45:30', NULL),
(4, 'Diploma', 'Diploma', 4, 1, '2026-01-05 06:45:30', NULL),
(5, 'B.Tech/B.E.', 'Graduate', 5, 1, '2026-01-05 06:45:30', NULL),
(6, 'B.Sc', 'Graduate', 6, 1, '2026-01-05 06:45:30', NULL),
(7, 'B.Com', 'Graduate', 7, 1, '2026-01-05 06:45:30', NULL),
(8, 'B.A.', 'Graduate', 8, 1, '2026-01-05 06:45:30', NULL),
(9, 'BBA', 'Graduate', 9, 1, '2026-01-05 06:45:30', NULL),
(10, 'BCA', 'Graduate', 10, 1, '2026-01-05 06:45:30', NULL),
(11, 'Bachelor of Pharmacy', 'Graduate', 11, 1, '2026-01-05 06:45:30', NULL),
(12, 'Bachelor of Nursing', 'Graduate', 12, 1, '2026-01-05 06:45:30', NULL),
(13, 'MBBS', 'Graduate', 13, 1, '2026-01-05 06:45:30', NULL),
(14, 'B.Ed', 'Graduate', 14, 1, '2026-01-05 06:45:30', NULL),
(15, 'LLB', 'Graduate', 15, 1, '2026-01-05 06:45:30', NULL),
(16, 'M.Tech/M.E.', 'Post Graduate', 16, 1, '2026-01-05 06:45:30', NULL),
(17, 'M.Sc', 'Post Graduate', 17, 1, '2026-01-05 06:45:30', NULL),
(18, 'M.Com', 'Post Graduate', 18, 1, '2026-01-05 06:45:30', NULL),
(19, 'M.A.', 'Post Graduate', 19, 1, '2026-01-05 06:45:30', NULL),
(20, 'MBA', 'Post Graduate', 20, 1, '2026-01-05 06:45:30', NULL),
(21, 'MCA', 'Post Graduate', 21, 1, '2026-01-05 06:45:30', NULL),
(22, 'M.Pharmacy', 'Post Graduate', 22, 1, '2026-01-05 06:45:30', NULL),
(23, 'M.D.', 'Post Graduate', 23, 1, '2026-01-05 06:45:30', NULL),
(24, 'M.S.', 'Post Graduate', 24, 1, '2026-01-05 06:45:30', NULL),
(25, 'LLM', 'Post Graduate', 25, 1, '2026-01-05 06:45:30', NULL),
(26, 'Ph.D.', 'Doctorate', 26, 1, '2026-01-05 06:45:30', NULL),
(27, 'Any Graduate', 'Graduate', 27, 1, '2026-01-05 06:45:30', NULL),
(28, 'Any Post Graduate', 'Post Graduate', 28, 1, '2026-01-05 06:45:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_states`
--

CREATE TABLE `master_states` (
  `id` int(11) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_states`
--

INSERT INTO `master_states` (`id`, `state_name`, `state_code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Andhra Pradesh', 'AP', 1, '2026-01-05 06:45:30', NULL),
(2, 'Arunachal Pradesh', 'AR', 1, '2026-01-05 06:45:30', NULL),
(3, 'Assam', 'AS', 1, '2026-01-05 06:45:30', NULL),
(4, 'Bihar', 'BR', 1, '2026-01-05 06:45:30', NULL),
(5, 'Chhattisgarh', 'CG', 1, '2026-01-05 06:45:30', NULL),
(6, 'Goa', 'GA', 1, '2026-01-05 06:45:30', NULL),
(7, 'Gujarat', 'GJ', 1, '2026-01-05 06:45:30', NULL),
(8, 'Haryana', 'HR', 1, '2026-01-05 06:45:30', NULL),
(9, 'Himachal Pradesh', 'HP', 1, '2026-01-05 06:45:30', NULL),
(10, 'Jharkhand', 'JH', 1, '2026-01-05 06:45:30', NULL),
(11, 'Karnataka', 'KA', 1, '2026-01-05 06:45:30', NULL),
(12, 'Kerala', 'KL', 1, '2026-01-05 06:45:30', NULL),
(13, 'Madhya Pradesh', 'MP', 1, '2026-01-05 06:45:30', NULL),
(14, 'Maharashtra', 'MH', 1, '2026-01-05 06:45:30', NULL),
(15, 'Manipur', 'MN', 1, '2026-01-05 06:45:30', NULL),
(16, 'Meghalaya', 'ML', 1, '2026-01-05 06:45:30', NULL),
(17, 'Mizoram', 'MZ', 1, '2026-01-05 06:45:30', NULL),
(18, 'Nagaland', 'NL', 1, '2026-01-05 06:45:30', NULL),
(19, 'Odisha', 'OD', 1, '2026-01-05 06:45:30', NULL),
(20, 'Punjab', 'PB', 1, '2026-01-05 06:45:30', NULL),
(21, 'Rajasthan', 'RJ', 1, '2026-01-05 06:45:30', NULL),
(22, 'Sikkim', 'SK', 1, '2026-01-05 06:45:30', NULL),
(23, 'Tamil Nadu', 'TN', 1, '2026-01-05 06:45:30', NULL),
(24, 'Telangana', 'TS', 1, '2026-01-05 06:45:30', NULL),
(25, 'Tripura', 'TR', 1, '2026-01-05 06:45:30', NULL),
(26, 'Uttar Pradesh', 'UP', 1, '2026-01-05 06:45:30', NULL),
(27, 'Uttarakhand', 'UK', 1, '2026-01-05 06:45:30', NULL),
(28, 'West Bengal', 'WB', 1, '2026-01-05 06:45:30', NULL),
(29, 'Delhi', 'DL', 1, '2026-01-05 06:45:30', NULL),
(30, 'Jammu & Kashmir', 'JK', 1, '2026-01-05 06:45:30', NULL),
(31, 'Ladakh', 'LA', 1, '2026-01-05 06:45:30', NULL),
(32, 'Puducherry', 'PY', 1, '2026-01-05 06:45:30', NULL),
(33, 'Chandigarh', 'CH', 1, '2026-01-05 06:45:30', NULL),
(34, 'Andaman & Nicobar Islands', 'AN', 1, '2026-01-05 06:45:30', NULL),
(35, 'Dadra & Nagar Haveli and Daman & Diu', 'DD', 1, '2026-01-05 06:45:30', NULL),
(36, 'Lakshadweep', 'LD', 1, '2026-01-05 06:45:30', NULL),
(37, 'All India', 'ALL', 1, '2026-01-05 06:45:30', NULL),
(38, 'Remote', 'REMOTE', 1, '2026-01-05 06:45:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `master_work_modes`
--

CREATE TABLE `master_work_modes` (
  `id` int(11) NOT NULL,
  `mode_name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT '?',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_work_modes`
--

INSERT INTO `master_work_modes` (`id`, `mode_name`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Work from Home', '🏠', 1, 1, '2026-01-05 06:45:30', '2026-01-05 11:46:43'),
(2, 'On-site', '🏢', 2, 1, '2026-01-05 06:45:30', NULL),
(3, 'Hybrid', '🔄', 3, 1, '2026-01-05 06:45:30', NULL),
(4, 'Field Work', '🚗', 4, 1, '2026-01-05 06:45:30', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_active_posted` (`is_active`,`posted_date`),
  ADD KEY `idx_category` (`category`,`is_active`),
  ADD KEY `idx_work_mode` (`work_mode`,`is_active`),
  ADD KEY `idx_job_category` (`job_category_id`),
  ADD KEY `idx_employment_type_id` (`employment_type_id`),
  ADD KEY `idx_experience_level_id` (`experience_level_id`),
  ADD KEY `idx_state_id` (`state_id`),
  ADD KEY `idx_work_mode_id` (`work_mode_id`),
  ADD KEY `idx_view_count` (`view_count`),
  ADD KEY `idx_posted_date` (`posted_date`),
  ADD KEY `idx_deadline` (`application_deadline`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `job_documents`
--
ALTER TABLE `job_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`);

--
-- Indexes for table `job_important_dates`
--
ALTER TABLE `job_important_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`);

--
-- Indexes for table `job_vacancies_breakdown`
--
ALTER TABLE `job_vacancies_breakdown`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`);

--
-- Indexes for table `job_views`
--
ALTER TABLE `job_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- Indexes for table `master_departments`
--
ALTER TABLE `master_departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `master_employment_types`
--
ALTER TABLE `master_employment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `master_experience_levels`
--
ALTER TABLE `master_experience_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_name` (`level_name`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `master_job_categories`
--
ALTER TABLE `master_job_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD UNIQUE KEY `category_slug` (`category_slug`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `master_qualifications`
--
ALTER TABLE `master_qualifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qualification_name` (`qualification_name`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `master_states`
--
ALTER TABLE `master_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `state_name` (`state_name`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `master_work_modes`
--
ALTER TABLE `master_work_modes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mode_name` (`mode_name`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `job_documents`
--
ALTER TABLE `job_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_important_dates`
--
ALTER TABLE `job_important_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_vacancies_breakdown`
--
ALTER TABLE `job_vacancies_breakdown`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_views`
--
ALTER TABLE `job_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_departments`
--
ALTER TABLE `master_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `master_employment_types`
--
ALTER TABLE `master_employment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `master_experience_levels`
--
ALTER TABLE `master_experience_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `master_job_categories`
--
ALTER TABLE `master_job_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `master_qualifications`
--
ALTER TABLE `master_qualifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `master_states`
--
ALTER TABLE `master_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `master_work_modes`
--
ALTER TABLE `master_work_modes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_documents`
--
ALTER TABLE `job_documents`
  ADD CONSTRAINT `job_documents_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_important_dates`
--
ALTER TABLE `job_important_dates`
  ADD CONSTRAINT `job_important_dates_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_vacancies_breakdown`
--
ALTER TABLE `job_vacancies_breakdown`
  ADD CONSTRAINT `job_vacancies_breakdown_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
