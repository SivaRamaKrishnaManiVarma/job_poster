-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 06:49 PM
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
(4, 'admin', '$2y$10$0RpNQus5GYMyFzZuEpxLB.zaqY9IE28bI7cascPTOKHQ08irOFyaS', NULL, '2026-01-02 11:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`id`, `username`, `email`, `password`, `company_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'employer', '', '$2y$10$fmffnjfGZJuIgB/kNzjyxONmaSPoBWkNHqTBam4JJ3BOLo3Ni8.5G', 'Hindu Pvt Ltd', 1, '2026-03-18 17:38:57', '2026-03-18 17:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) DEFAULT NULL,
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

INSERT INTO `jobs` (`id`, `employer_id`, `notification_number`, `title`, `slug`, `total_vacancies`, `company`, `recruitment_board`, `description`, `job_link`, `official_website`, `work_mode`, `employment_type`, `experience_level`, `location`, `category`, `posted_date`, `application_deadline`, `is_active`, `created_at`, `vacancy_breakdown`, `min_qualification_id`, `required_percentage`, `age_limit_min`, `age_limit_max`, `age_relaxation`, `required_experience`, `salary_min`, `salary_max`, `pay_scale`, `allowances_details`, `selection_process`, `exam_pattern`, `notification_date`, `last_date_to_apply`, `exam_date`, `result_date`, `eligibility_cutoff_date`, `application_fee_general`, `application_fee_obc`, `application_fee_sc_st`, `payment_mode`, `notification_pdf`, `syllabus_pdf`, `admit_card_link`, `answer_key_link`, `job_category_id`, `work_mode_id`, `employment_type_id`, `experience_level_id`, `state_id`, `department_id`, `important_instructions`, `how_to_apply_steps`, `short_info`, `view_count`, `apply_click_count`, `updated_at`) VALUES
(3, NULL, NULL, 'RRB Group D Level-1 Recruitment 2026', 'sbi-specialist-cadre-officers-2026', 22000, 'Railway Recruitment Board', NULL, 'Railway Recruitment Board conducts recruitment for Group D Level-1 posts across all Indian Railways. This includes Track Maintainer, Helper, Porter, and other technical and non-technical positions.', 'https://rrbonlinereg.in', 'https://rrbonlinereg.in', 'On-site', 'Full-time', 'Freshers', 'All India - Various Railway Zones', NULL, '2026-01-07', '2026-02-20', 1, '2026-01-09 04:59:24', NULL, 1, NULL, 18, 33, NULL, NULL, 18000.00, NULL, NULL, NULL, NULL, NULL, '2026-01-07', '2026-02-20', '2026-04-15', '2026-06-30', NULL, 500.00, 250.00, 0.00, 'Online', NULL, NULL, NULL, NULL, 6, 2, 6, 1, 37, 3, NULL, NULL, NULL, 2, 0, '2026-02-17 10:30:31'),
(4, NULL, NULL, 'SBI Specialist Cadre Officers 2026', 'ncert-various-non-academic-posts-2026', 1146, 'State Bank of India', NULL, 'State Bank of India invites applications for Specialist Cadre Officer posts in various domains including IT, Risk Management, Economist, Chartered Accountant, Company Secretary and other specialized roles.', 'https://sbi.co.in/careers', 'https://www.sbi.co.in', 'On-site', 'Full-time', 'Freshers', 'Pan India', NULL, '2026-01-07', '2026-01-10', 1, '2026-01-09 04:59:24', NULL, 27, NULL, 21, 35, NULL, NULL, 50000.00, NULL, NULL, NULL, NULL, NULL, '2026-01-07', '2026-01-10', '2026-02-15', '2026-03-30', NULL, 750.00, 100.00, 0.00, 'Online', NULL, NULL, NULL, NULL, 4, 2, 6, 4, 37, 5, NULL, NULL, NULL, 0, 0, '2026-01-09 05:03:19'),
(5, NULL, NULL, 'NCERT Various Non-Academic Posts 2026', 'indian-army-ssc-technical-men-2026', 173, 'National Council of Educational Research and Training', NULL, 'NCERT invites applications for various Non-Academic positions including Junior Engineer, Assistant, Accountant, Library Attendant, and other administrative and technical posts.', 'https://ncert.nic.in/career', 'https://ncert.nic.in', 'On-site', 'Full-time', 'Freshers', 'New Delhi', NULL, '2026-01-07', '2026-02-16', 1, '2026-01-09 04:59:24', NULL, 27, NULL, 18, 35, NULL, NULL, 35000.00, NULL, NULL, NULL, NULL, NULL, '2026-01-07', '2026-02-16', '2026-03-20', '2026-04-30', NULL, 100.00, 100.00, 0.00, 'Online', NULL, NULL, NULL, NULL, 1, 2, 6, 1, 29, NULL, NULL, NULL, NULL, 3, 0, '2026-01-13 05:24:19'),
(6, NULL, NULL, 'Indian Army SSC Technical 67th Course Men - 2026', 'nalco-graduate-engineer-trainee-2026', 350, 'Indian Army', NULL, 'Indian Army invites applications from unmarried male Engineering graduates for Short Service Commission in Technical Entry Scheme. Selected candidates will be commissioned as Lieutenant in the Indian Army.', 'https://joinindianarmy.nic.in', 'https://joinindianarmy.nic.in', 'On-site', 'Full-time', 'Freshers', 'Various Army Centers', NULL, '2026-01-07', '2026-02-05', 1, '2026-01-09 04:59:24', NULL, 5, NULL, 20, 27, NULL, NULL, 56100.00, NULL, NULL, NULL, NULL, NULL, '2026-01-07', '2026-02-05', '2026-04-15', '2026-05-30', NULL, 0.00, 0.00, 0.00, 'Free', NULL, NULL, NULL, NULL, 5, 2, 4, 1, 37, 8, NULL, NULL, NULL, 28, 0, '2026-01-13 05:24:32'),
(7, NULL, NULL, 'NALCO Graduate Engineer Trainee 2026', 'iocl-apprentice-recruitment-2026', 110, 'National Aluminium Company Limited', NULL, 'NALCO recruits Graduate Engineer Trainees in disciplines like Mechanical, Electrical, Electronics, Chemical, Metallurgy, Mining, and Civil Engineering for operations across Odisha and other locations.', 'https://nalcoindia.com/careers', 'https://www.nalcoindia.com', 'On-site', 'Full-time', 'Freshers', 'Bhubaneswar, Angul, Damanjodi', NULL, '2025-12-30', '2026-01-22', 1, '2026-01-09 04:59:24', NULL, 5, NULL, 18, 30, NULL, NULL, 50000.00, NULL, NULL, NULL, NULL, NULL, '2025-12-30', '2026-01-22', '2026-03-10', '2026-04-15', NULL, 1000.00, 500.00, 0.00, 'Online', NULL, NULL, NULL, NULL, 3, 2, 1, 1, 19, NULL, NULL, NULL, NULL, 8, 0, '2026-01-09 07:19:22'),
(8, NULL, NULL, 'IOCL Apprentice Recruitment 2026', 'ssc-grade-c-stenographer-2026', 501, 'Indian Oil Corporation Limited', NULL, 'Indian Oil Corporation Limited recruits Trade, Technician and Graduate Apprentices across marketing divisions in Northern and Eastern regions for one year apprenticeship training.', 'https://iocl.com/apprentice', 'https://www.iocl.com', 'On-site', 'Full-time', 'Freshers', 'Northern and Eastern Regions', NULL, '2026-01-07', '2026-01-12', 1, '2026-01-09 04:59:24', NULL, 27, NULL, 18, 24, NULL, NULL, 9000.00, NULL, NULL, NULL, NULL, NULL, '2026-01-07', '2026-01-12', NULL, '2026-02-28', NULL, 0.00, 0.00, 0.00, 'Free', NULL, NULL, NULL, NULL, 20, 2, 3, 1, 37, NULL, NULL, NULL, NULL, 11, 0, '2026-01-09 09:14:08'),
(9, NULL, NULL, 'SSC Grade C Stenographer 2026', 'bsf-constable-recruitment-2026', 326, 'Staff Selection Commission', NULL, 'Staff Selection Commission conducts recruitment for Grade C Stenographer posts in various Central Government Ministries and Departments. Candidates should have stenography skills.', 'https://ssc.nic.in', 'https://ssc.nic.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2025-12-23', '2026-01-11', 1, '2026-01-09 04:59:24', NULL, 2, NULL, 18, 30, NULL, NULL, 44900.00, NULL, NULL, NULL, NULL, NULL, '2025-12-23', '2026-01-11', '2026-03-15', '2026-04-30', NULL, 100.00, 100.00, 0.00, 'Online', NULL, NULL, NULL, NULL, 12, 2, 6, 1, 37, 1, NULL, NULL, NULL, 0, 0, '2026-01-09 05:03:19'),
(10, NULL, NULL, 'BSF Constable Recruitment 2026', NULL, 549, 'Border Security Force', NULL, 'Border Security Force invites applications for Constable positions. Selected candidates will serve on India borders protecting national security. Physical fitness and medical standards apply.', 'https://bsf.nic.in', 'https://www.bsf.nic.in', 'On-site', 'Full-time', 'Freshers', 'Various BSF Locations', NULL, '2025-12-22', '2026-01-15', 1, '2026-01-09 04:59:24', NULL, 1, NULL, 18, 23, NULL, NULL, 21700.00, NULL, NULL, NULL, NULL, NULL, '2025-12-22', '2026-01-15', '2026-03-01', '2026-04-15', NULL, 0.00, 0.00, 0.00, 'Free', NULL, NULL, NULL, NULL, 9, 2, 6, 1, 37, 11, NULL, NULL, NULL, 0, 0, '2026-01-09 04:59:24'),
(11, NULL, NULL, 'Junior Clerk Government Office 2026', 'junior-clerk-govt-2026', 500, 'Ministry of Home Affairs', NULL, 'Recruitment for Junior Clerk positions in various government offices across India.', 'https://example.com/apply', 'https://mha.gov.in', 'On-site', 'Full-time', 'Freshers', 'New Delhi', NULL, '2026-02-15', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 25000.00, 35000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 1, 2, 1, 1, 29, NULL, NULL, NULL, 'Junior Clerk positions with good benefits and job security', 1, 0, '2026-03-09 17:04:00'),
(12, NULL, NULL, 'Accountant - Central Government', 'accountant-central-govt-2026', 150, 'Ministry of Finance', NULL, 'Recruiting qualified accountants for finance department operations.', 'https://example.com/apply', 'https://finmin.nic.in', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-14', '2026-03-15', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 40000.00, 55000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 1, 2, 1, 4, 14, NULL, NULL, NULL, 'Handle government accounting and financial operations', 0, 0, '2026-02-17 10:49:19'),
(13, NULL, NULL, 'Data Entry Operator - Govt', 'data-entry-govt-2026', 300, 'Department of Statistics', NULL, 'Data entry operators needed for national census data processing.', 'https://example.com/apply', 'https://mospi.gov.in', 'On-site', 'Full-time', 'Freshers', 'Kolkata', NULL, '2026-02-16', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22000.00, 30000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 1, 2, 1, 1, 28, NULL, NULL, NULL, 'Process and manage census data digitally', 0, 0, '2026-02-17 10:49:19'),
(14, NULL, NULL, 'Bank PO - Probationary Officer', 'bank-po-2026', 800, 'Union Bank of India', NULL, 'Probationary Officers for branch operations and management roles.', 'https://example.com/apply', 'https://unionbankofindia.co.in', 'On-site', 'Full-time', 'Freshers', 'Pan India', NULL, '2026-02-10', '2026-03-10', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50000.00, 70000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 4, 2, 1, 2, 37, NULL, NULL, NULL, 'Lead banking operations at branch level', 0, 0, '2026-02-17 10:49:19'),
(15, NULL, NULL, 'Bank Clerk - Scale I', 'bank-clerk-2026', 1200, 'Punjab National Bank', NULL, 'Clerical positions for customer service and banking operations.', 'https://example.com/apply', 'https://pnbindia.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-12', '2026-03-18', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 30000.00, 42000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 4, 2, 1, 1, 37, NULL, NULL, NULL, 'Handle customer transactions and banking services', 0, 0, '2026-02-17 10:49:19'),
(16, NULL, NULL, 'Senior Manager - Credit Risk', 'senior-manager-credit-2026', 25, 'HDFC Bank', NULL, 'Experienced professionals for credit risk assessment and management.', 'https://example.com/apply', 'https://hdfcbank.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-17', '2026-03-30', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 120000.00, 180000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 4, 3, 1, 5, 14, NULL, NULL, NULL, 'Assess and mitigate credit risks for corporate clients', 2, 0, '2026-02-17 11:16:21'),
(17, NULL, NULL, 'Banking Relationship Manager', 'relationship-manager-bank-2026', 100, 'ICICI Bank', NULL, 'Build and maintain relationships with high-net-worth clients.', 'https://example.com/apply', 'https://icicibank.com', 'On-site', 'Full-time', 'Freshers', 'Bangalore', NULL, '2026-02-15', '2026-03-22', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 60000.00, 90000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 4, 2, 1, 4, 11, NULL, NULL, NULL, 'Manage premium banking clients and their investments', 0, 0, '2026-02-17 10:49:19'),
(18, NULL, NULL, 'Indian Navy Sailor SSR 2026', 'navy-sailor-ssr-2026', 2500, 'Indian Navy', NULL, 'Sailors (Senior Secondary Recruits) for various technical roles in Indian Navy.', 'https://joinindiannavy.gov.in', 'https://indiannavy.nic.in', 'On-site', 'Full-time', 'Freshers', 'Various Navy Centers', NULL, '2026-02-10', '2026-03-05', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35000.00, 45000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 5, 2, 1, 1, 37, NULL, NULL, NULL, 'Serve the nation in technical roles at sea', 0, 0, '2026-02-17 10:49:19'),
(19, NULL, NULL, 'Indian Air Force Group X', 'iaf-group-x-2026', 1800, 'Indian Air Force', NULL, 'Technical trades in Indian Air Force for 12th pass candidates.', 'https://careerindianairforce.cdac.in', 'https://indianairforce.nic.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-08', '2026-03-12', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 38000.00, 48000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 5, 2, 1, 1, 37, NULL, NULL, NULL, 'Technical roles in aircraft maintenance and operations', 0, 0, '2026-02-17 10:49:19'),
(20, NULL, NULL, 'Coast Guard Navik 2026', 'coast-guard-navik-2026', 350, 'Indian Coast Guard', NULL, 'Navik (Domestic Branch) recruitment for maritime security.', 'https://joincoastguard.gov.in', 'https://indiancoastguard.gov.in', 'On-site', 'Full-time', 'Freshers', 'Coastal States', NULL, '2026-02-12', '2026-03-15', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 32000.00, 42000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 5, 2, 1, 1, 37, NULL, NULL, NULL, 'Protect Indian maritime borders and conduct rescue operations', 0, 0, '2026-02-17 10:49:19'),
(21, NULL, NULL, 'Railway Assistant Loco Pilot', 'railway-alp-2026', 5000, 'Indian Railways', NULL, 'Assistant Loco Pilots for train operations across Indian Railways zones.', 'https://rrbonlinereg.in', 'https://indianrailways.gov.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-11', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35000.00, 50000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 6, 2, 1, 2, 37, NULL, NULL, NULL, 'Operate and assist in train driving operations', 0, 0, '2026-02-17 10:49:19'),
(22, NULL, NULL, 'Railway Technician Grade 3', 'railway-technician-2026', 8000, 'Railway Recruitment Board', NULL, 'Technicians for maintenance of railway infrastructure and rolling stock.', 'https://rrbonlinereg.in', 'https://indianrailways.gov.in', 'On-site', 'Full-time', 'Freshers', 'Various Railway Zones', NULL, '2026-02-13', '2026-03-18', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 28000.00, 40000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 6, 2, 1, 1, 37, NULL, NULL, NULL, 'Maintain railway tracks, signals, and electrical systems', 0, 0, '2026-02-17 10:49:19'),
(23, NULL, NULL, 'Station Master - Indian Railways', 'station-master-2026', 200, 'Indian Railways', NULL, 'Station Masters for railway station management and operations.', 'https://rrbonlinereg.in', 'https://indianrailways.gov.in', 'On-site', 'Full-time', 'Freshers', 'Multiple Locations', NULL, '2026-02-14', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 45000.00, 65000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 6, 2, 1, 4, 37, NULL, NULL, NULL, 'Manage railway station operations and passenger services', 0, 0, '2026-02-17 10:49:19'),
(24, NULL, NULL, 'Full Stack Developer - MERN', 'full-stack-developer-mern-2026', 50, 'TCS - Tata Consultancy Services', NULL, 'Full stack developers proficient in MERN stack for enterprise projects.', 'https://careers.tcs.com', 'https://tcs.com', 'On-site', 'Full-time', 'Freshers', 'Hyderabad', NULL, '2026-02-16', '2026-03-30', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 60000.00, 100000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 1, 1, 4, 24, NULL, NULL, NULL, 'Build scalable web applications using MongoDB, Express, React, Node.js', 0, 0, '2026-02-17 10:49:19'),
(25, NULL, NULL, 'Python Developer - AI/ML', 'python-developer-ai-2026', 80, 'Infosys', NULL, 'Python developers with AI/ML expertise for data science projects.', 'https://careers.infosys.com', 'https://infosys.com', 'On-site', 'Full-time', 'Freshers', 'Bangalore', NULL, '2026-02-15', '2026-03-28', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80000.00, 130000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 3, 1, 4, 11, NULL, NULL, NULL, 'Develop AI solutions and machine learning models', 1, 0, '2026-02-18 18:48:06'),
(26, NULL, NULL, 'Java Backend Developer', 'java-backend-developer-2026', 100, 'Wipro Technologies', NULL, 'Java developers for backend microservices architecture.', 'https://careers.wipro.com', 'https://wipro.com', 'On-site', 'Full-time', 'Freshers', 'Pune', NULL, '2026-02-17', '2026-04-05', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 65000.00, 95000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 2, 1, 4, 14, NULL, NULL, NULL, 'Design and develop scalable backend systems using Spring Boot', 5, 0, '2026-02-19 05:50:36'),
(27, NULL, NULL, 'Frontend Developer - React', 'frontend-developer-react-2026', 60, 'HCL Technologies', NULL, 'React.js developers for modern web application development.', 'https://careers.hcltech.com', 'https://hcltech.com', 'On-site', 'Full-time', 'Freshers', 'Chennai', NULL, '2026-02-14', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 55000.00, 85000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 1, 1, 2, 23, NULL, NULL, NULL, 'Create responsive and dynamic user interfaces', 2, 0, '2026-02-18 18:48:10'),
(28, NULL, NULL, 'DevOps Engineer - AWS/Azure', 'devops-engineer-2026', 45, 'Tech Mahindra', NULL, 'DevOps engineers for cloud infrastructure and CI/CD automation.', 'https://careers.techmahindra.com', 'https://techmahindra.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-16', '2026-03-31', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 90000.00, 140000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 1, 1, 5, 14, NULL, NULL, NULL, 'Manage cloud infrastructure and deployment pipelines', 0, 0, '2026-02-17 10:49:19'),
(29, NULL, NULL, 'Data Scientist', 'data-scientist-2026', 35, 'Accenture', NULL, 'Data scientists for analytics and predictive modeling projects.', 'https://accenture.com/careers', 'https://accenture.com', 'On-site', 'Full-time', 'Freshers', 'Bangalore', NULL, '2026-02-15', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100000.00, 160000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 3, 1, 5, 11, NULL, NULL, NULL, 'Analyze complex data and build predictive models', 0, 0, '2026-02-17 10:49:19'),
(30, NULL, NULL, 'Mobile App Developer - Flutter', 'flutter-developer-2026', 40, 'Cognizant', NULL, 'Flutter developers for cross-platform mobile application development.', 'https://careers.cognizant.com', 'https://cognizant.com', 'On-site', 'Full-time', 'Freshers', 'Kolkata', NULL, '2026-02-17', '2026-04-02', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70000.00, 110000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 1, 1, 4, 28, NULL, NULL, NULL, 'Build beautiful cross-platform mobile apps', 5, 0, '2026-02-18 18:56:50'),
(31, NULL, NULL, 'Mechanical Engineer - Production', 'mechanical-engineer-production-2026', 120, 'Larsen & Toubro', NULL, 'Mechanical engineers for manufacturing and production planning.', 'https://lntcareers.com', 'https://larsentoubro.com', 'On-site', 'Full-time', 'Freshers', 'Coimbatore', NULL, '2026-02-12', '2026-03-22', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50000.00, 75000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 10, 2, 1, 4, 23, NULL, NULL, NULL, 'Oversee production processes and quality control', 0, 0, '2026-02-17 10:49:19'),
(32, NULL, NULL, 'Electrical Engineer - Power Systems', 'electrical-engineer-power-2026', 80, 'BHEL', NULL, 'Electrical engineers for power plant equipment and systems.', 'https://careers.bhel.com', 'https://bhel.com', 'On-site', 'Full-time', 'Freshers', 'Haridwar', NULL, '2026-02-14', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 55000.00, 80000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 10, 2, 1, 4, 27, NULL, NULL, NULL, 'Design and maintain power generation equipment', 0, 0, '2026-02-17 10:49:19'),
(33, NULL, NULL, 'Civil Engineer - Infrastructure', 'civil-engineer-infra-2026', 150, 'GMR Group', NULL, 'Civil engineers for airport and highway infrastructure projects.', 'https://gmrgroup.in/careers', 'https://gmrgroup.in', 'On-site', 'Full-time', 'Freshers', 'Delhi NCR', NULL, '2026-02-16', '2026-03-28', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 60000.00, 90000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 10, 2, 1, 5, 29, NULL, NULL, NULL, 'Plan and execute large-scale infrastructure projects', 0, 0, '2026-02-17 10:49:19'),
(34, NULL, NULL, 'NTPC Management Trainee', 'ntpc-management-trainee-2026', 300, 'NTPC Limited', NULL, 'Management trainees for power generation operations and management.', 'https://ntpccareers.net', 'https://ntpc.co.in', 'On-site', 'Full-time', 'Freshers', 'Multiple Locations', NULL, '2026-02-10', '2026-03-15', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 60000.00, 80000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 3, 2, 1, 1, 37, NULL, NULL, NULL, 'Join India largest power generation company', 0, 0, '2026-02-17 10:49:19'),
(35, NULL, NULL, 'ONGC Graduate Trainee', 'ongc-graduate-trainee-2026', 400, 'Oil and Natural Gas Corporation', NULL, 'Graduate trainees in petroleum engineering and geology.', 'https://ongcindia.com/careers', 'https://ongc.co.in', 'On-site', 'Full-time', 'Freshers', 'Dehradun', NULL, '2026-02-11', '2026-03-18', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 75000.00, 100000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 3, 2, 1, 1, 27, NULL, NULL, NULL, 'Explore and produce oil and natural gas resources', 0, 0, '2026-02-17 10:49:19'),
(36, NULL, NULL, 'BHEL Engineer Trainee', 'bhel-engineer-trainee-2026', 250, 'Bharat Heavy Electricals Limited', NULL, 'Engineer trainees for power equipment manufacturing.', 'https://careers.bhel.com', 'https://bhel.com', 'On-site', 'Full-time', 'Freshers', 'Bhopal', NULL, '2026-02-13', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 55000.00, 75000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 3, 2, 1, 1, 13, NULL, NULL, NULL, 'Design and manufacture power generation equipment', 0, 0, '2026-02-17 10:49:19'),
(37, NULL, NULL, 'Coal India Management Trainee', 'coal-india-mt-2026', 180, 'Coal India Limited', NULL, 'Management trainees for coal mining and operations.', 'https://coalindia.in/careers', 'https://coalindia.in', 'On-site', 'Full-time', 'Freshers', 'Dhanbad', NULL, '2026-02-14', '2026-03-22', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 65000.00, 85000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 3, 2, 1, 2, 10, NULL, NULL, NULL, 'Manage coal production and mining operations', 0, 0, '2026-02-17 10:49:19'),
(38, NULL, NULL, 'Staff Nurse - Government Hospital', 'staff-nurse-govt-2026', 500, 'AIIMS New Delhi', NULL, 'Staff nurses for patient care in premier medical institute.', 'https://aiimsexams.ac.in', 'https://aiims.edu', 'On-site', 'Full-time', 'Freshers', 'New Delhi', NULL, '2026-02-15', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 45000.00, 60000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 8, 2, 1, 2, 29, NULL, NULL, NULL, 'Provide quality nursing care to patients', 0, 0, '2026-02-17 10:49:19'),
(39, NULL, NULL, 'Medical Officer - Primary Health', 'medical-officer-2026', 200, 'State Health Department', NULL, 'MBBS doctors for primary health centers in rural areas.', 'https://healthdept.gov.in', 'https://healthdept.gov.in', 'On-site', 'Full-time', 'Freshers', 'Rural Uttar Pradesh', NULL, '2026-02-12', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70000.00, 95000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 8, 2, 1, 2, 26, NULL, NULL, NULL, 'Serve rural communities with primary healthcare', 0, 0, '2026-02-17 10:49:19'),
(40, NULL, NULL, 'Lab Technician - Pathology', 'lab-technician-pathology-2026', 150, 'Apollo Hospitals', NULL, 'Pathology lab technicians for diagnostic services.', 'https://apollohospitals.com/careers', 'https://apollohospitals.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-16', '2026-03-30', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35000.00, 50000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 8, 2, 1, 2, 14, NULL, NULL, NULL, 'Conduct laboratory tests and maintain quality standards', 0, 0, '2026-02-17 10:49:19'),
(41, NULL, NULL, 'PGT Computer Science Teacher', 'pgt-computer-teacher-2026', 80, 'Kendriya Vidyalaya Sangathan', NULL, 'Post Graduate Teachers for Computer Science in KV schools.', 'https://kvsangathan.nic.in', 'https://kvsangathan.nic.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-10', '2026-03-15', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 55000.00, 75000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 7, 2, 1, 2, 37, NULL, NULL, NULL, 'Teach Computer Science to senior secondary students', 0, 0, '2026-02-17 10:49:19'),
(42, NULL, NULL, 'TGT Mathematics Teacher', 'tgt-math-teacher-2026', 120, 'Navodaya Vidyalaya Samiti', NULL, 'Trained Graduate Teachers for Mathematics in Navodaya schools.', 'https://navodaya.gov.in', 'https://navodaya.gov.in', 'On-site', 'Full-time', 'Freshers', 'Multiple States', NULL, '2026-02-12', '2026-03-18', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 48000.00, 65000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 7, 2, 1, 2, 37, NULL, NULL, NULL, 'Teach Mathematics to middle and high school students', 0, 0, '2026-02-17 10:49:19'),
(43, NULL, NULL, 'Assistant Professor - Engineering', 'asst-professor-engg-2026', 50, 'IIT Madras', NULL, 'Assistant Professors for various engineering departments.', 'https://iitm.ac.in/jobs', 'https://iitm.ac.in', 'On-site', 'Full-time', 'Freshers', 'Chennai', NULL, '2026-02-14', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100000.00, 140000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 7, 2, 1, 5, 23, NULL, NULL, NULL, 'Teach and conduct research in engineering disciplines', 0, 0, '2026-02-17 10:49:19'),
(44, NULL, NULL, 'Sub Inspector - State Police', 'sub-inspector-police-2026', 800, 'Maharashtra Police', NULL, 'Sub Inspectors for law enforcement and crime prevention.', 'https://mahapolice.gov.in', 'https://mahapolice.gov.in', 'On-site', 'Full-time', 'Freshers', 'Maharashtra', NULL, '2026-02-11', '2026-03-10', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 42000.00, 58000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 9, 2, 1, 2, 14, NULL, NULL, NULL, 'Maintain law and order in assigned jurisdiction', 0, 0, '2026-02-17 10:49:19'),
(45, NULL, NULL, 'CRPF Constable - General Duty', 'crpf-constable-2026', 3000, 'Central Reserve Police Force', NULL, 'Constables for paramilitary duties across India.', 'https://crpf.gov.in', 'https://crpf.gov.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-13', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35000.00, 45000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 9, 2, 1, 1, 37, NULL, NULL, NULL, 'Serve in paramilitary force for internal security', 0, 0, '2026-02-17 10:49:19'),
(46, NULL, NULL, 'SSC CHSL - LDC/DEO', 'ssc-chsl-2026', 4000, 'Staff Selection Commission', NULL, 'Combined Higher Secondary Level exam for clerical posts.', 'https://ssc.nic.in', 'https://ssc.nic.in', 'On-site', 'Full-time', 'Freshers', 'All India', NULL, '2026-02-09', '2026-03-12', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 32000.00, 45000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 12, 2, 1, 1, 37, NULL, NULL, NULL, 'Lower Division Clerk and Data Entry Operator positions', 0, 0, '2026-02-17 10:49:19'),
(47, NULL, NULL, 'SSC CGL - Tax Assistant', 'ssc-cgl-tax-assistant-2026', 1500, 'Staff Selection Commission', NULL, 'Combined Graduate Level for various central government posts.', 'https://ssc.nic.in', 'https://ssc.nic.in', 'On-site', 'Full-time', 'Freshers', 'Pan India', NULL, '2026-02-10', '2026-03-15', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 48000.00, 70000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 12, 2, 1, 2, 37, NULL, NULL, NULL, 'Tax Assistant in Income Tax Department', 0, 0, '2026-02-17 10:49:19'),
(48, NULL, NULL, 'SSC MTS - Multi Tasking Staff', 'ssc-mts-2026', 6000, 'Staff Selection Commission', NULL, 'Multi Tasking Staff for various government departments.', 'https://ssc.nic.in', 'https://ssc.nic.in', 'On-site', 'Full-time', 'Freshers', 'All States', NULL, '2026-02-11', '2026-03-18', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20000.00, 28000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 12, 2, 1, 1, 37, NULL, NULL, NULL, 'Support staff in central government offices', 0, 0, '2026-02-17 10:49:19'),
(49, NULL, NULL, 'Digital Marketing Executive', 'digital-marketing-executive-2026', 40, 'WPP India', NULL, 'Digital marketing professionals for brand campaigns.', 'https://wpp.com/careers', 'https://wpp.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-16', '2026-03-30', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 45000.00, 65000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 2, 3, 1, 2, 14, NULL, NULL, NULL, 'Plan and execute digital marketing campaigns', 0, 0, '2026-02-17 10:49:19'),
(50, NULL, NULL, 'HR Generalist', 'hr-generalist-2026', 60, 'Reliance Industries', NULL, 'HR professionals for employee relations and recruitment.', 'https://ril.com/careers', 'https://ril.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-15', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50000.00, 75000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 2, 2, 1, 4, 14, NULL, NULL, NULL, 'Manage HR operations and employee lifecycle', 0, 0, '2026-02-17 10:49:19'),
(51, NULL, NULL, 'Sales Manager - FMCG', 'sales-manager-fmcg-2026', 100, 'Hindustan Unilever', NULL, 'Sales managers for FMCG product distribution.', 'https://hul.co.in/careers', 'https://hul.co.in', 'On-site', 'Full-time', 'Freshers', 'Multiple Cities', NULL, '2026-02-14', '2026-03-22', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80000.00, 120000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 2, 2, 1, 5, 37, NULL, NULL, NULL, 'Drive sales growth in assigned territories', 0, 0, '2026-02-17 10:49:19'),
(52, NULL, NULL, 'Content Writer - Tech', 'content-writer-tech-2026', 25, 'Zomato', NULL, 'Content writers for tech and food-tech platforms.', 'https://zomato.com/careers', 'https://zomato.com', 'On-site', 'Full-time', 'Freshers', 'Gurugram', NULL, '2026-02-17', '2026-03-28', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 40000.00, 60000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 2, 1, 1, 2, 8, NULL, NULL, NULL, 'Create engaging content for digital platforms', 3, 0, '2026-02-19 04:46:30'),
(53, NULL, NULL, 'Business Analyst', 'business-analyst-2026', 70, 'Deloitte India', NULL, 'Business analysts for consulting and advisory projects.', 'https://deloitte.com/careers', 'https://deloitte.com', 'On-site', 'Full-time', 'Freshers', 'Bangalore', NULL, '2026-02-16', '2026-04-01', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 90000.00, 135000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 2, 3, 1, 4, 11, NULL, NULL, NULL, 'Analyze business processes and provide strategic insights', 0, 0, '2026-02-17 10:49:19'),
(54, NULL, NULL, 'Software Development Intern', 'software-intern-2026', 200, 'Microsoft India', NULL, 'Summer internship for software engineering students.', 'https://careers.microsoft.com', 'https://microsoft.com', 'On-site', 'Full-time', 'Freshers', 'Hyderabad', NULL, '2026-02-15', '2026-03-30', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 30000.00, 40000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 26, 1, 3, 1, 24, NULL, NULL, NULL, '3-month paid internship with stipend', 0, 0, '2026-02-17 10:49:19'),
(55, NULL, NULL, 'Digital Marketing Intern', 'marketing-intern-2026', 50, 'Google India', NULL, 'Marketing internship for MBA/BBA students.', 'https://careers.google.com', 'https://google.com', 'On-site', 'Full-time', 'Freshers', 'Bangalore', NULL, '2026-02-14', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 25000.00, 35000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 26, 1, 3, 1, 11, NULL, NULL, NULL, 'Learn digital marketing from industry leaders', 0, 0, '2026-02-17 10:49:19'),
(56, NULL, NULL, 'Data Analytics Intern', 'data-intern-2026', 80, 'Amazon India', NULL, 'Internship in data science and analytics team.', 'https://amazon.jobs', 'https://amazon.in', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-16', '2026-03-28', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 28000.00, 38000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 26, 1, 3, 1, 14, NULL, NULL, NULL, 'Work on real-world data analytics projects', 0, 0, '2026-02-17 10:49:19'),
(57, NULL, NULL, 'Network Engineer - 5G', 'network-engineer-5g-2026', 100, 'Bharti Airtel', NULL, 'Network engineers for 5G rollout and maintenance.', 'https://airtel.in/careers', 'https://airtel.in', 'On-site', 'Full-time', 'Freshers', 'Delhi NCR', NULL, '2026-02-15', '2026-03-22', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 65000.00, 95000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 18, 2, 1, 4, 29, NULL, NULL, NULL, 'Deploy and maintain 5G network infrastructure', 0, 0, '2026-02-17 10:49:19'),
(58, NULL, NULL, 'Telecom Technical Support', 'telecom-support-2026', 150, 'Reliance Jio', NULL, 'Technical support engineers for telecom services.', 'https://jio.com/careers', 'https://jio.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai', NULL, '2026-02-14', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 40000.00, 60000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 18, 2, 1, 2, 14, NULL, NULL, NULL, 'Provide technical support for telecom networks', 0, 0, '2026-02-17 10:49:19'),
(59, NULL, NULL, 'Petroleum Engineer', 'petroleum-engineer-2026', 60, 'Reliance Industries - O&G', NULL, 'Petroleum engineers for offshore drilling operations.', 'https://ril.com/careers', 'https://ril.com', 'On-site', 'Full-time', 'Freshers', 'Mumbai Offshore', NULL, '2026-02-13', '2026-03-25', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 95000.00, 140000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 20, 2, 1, 4, 14, NULL, NULL, NULL, 'Work on offshore oil and gas exploration projects', 0, 0, '2026-02-17 10:49:19'),
(60, NULL, NULL, 'Refinery Operator', 'refinery-operator-2026', 120, 'Indian Oil Corporation', NULL, 'Operators for petroleum refinery operations.', 'https://iocl.com/careers', 'https://iocl.com', 'On-site', 'Full-time', 'Freshers', 'Panipat', NULL, '2026-02-14', '2026-03-20', 1, '2026-02-17 10:49:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 45000.00, 65000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 20, 2, 1, 2, 8, NULL, NULL, NULL, 'Operate and monitor refinery processing units', 0, 0, '2026-02-17 10:49:19'),
(61, 1, NULL, 'Software Eng', 'software-eng-at-hindu-pvt-ltd', NULL, 'Hindu Pvt Ltd', NULL, '', 'https://in.indeed.com/cmp/Ivy-Overseas?campaignid=mobvjcmp&amp;amp;amp;amp;amp;amp;amp;from=mobviewjob&amp;amp;amp;amp;amp;amp;amp;tk=1jk10qi7bhc38810&amp;amp;amp;amp;amp;amp;amp;fromjk=683bf1d63dd7647c', NULL, 'On-site', 'Full-time', 'Freshers', 'Delhi', NULL, '2026-03-18', '2026-04-11', 1, '2026-03-18 17:45:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 15000.00, 25000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Online', NULL, NULL, NULL, NULL, 11, 3, 1, 1, 1, NULL, NULL, NULL, '', 4, 0, '2026-03-18 17:48:12');

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
(6, '10+ years', '👔', 6, 1, '2026-01-05 06:45:30', NULL),
(7, 'Not Applicable', '🛇', 1, 1, '2026-01-07 06:16:23', NULL);

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
(1, 'Government', 'government', '🏛️', '', 1, 1, '2026-01-05 06:45:30', '2026-01-07 06:01:56'),
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

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `total_experience_years` int(11) DEFAULT 0,
  `current_company` varchar(100) DEFAULT NULL,
  `current_designation` varchar(100) DEFAULT NULL,
  `preferred_job_type_id` int(11) DEFAULT NULL COMMENT 'FK to master_employment_types',
  `preferred_work_mode_id` int(11) DEFAULT NULL COMMENT 'FK to master_work_modes',
  `expected_salary_min` decimal(10,2) DEFAULT NULL,
  `expected_salary_max` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `job_alert_email` tinyint(1) DEFAULT 1 COMMENT 'Email notifications for matching jobs',
  `preferred_locations` text DEFAULT NULL COMMENT 'Comma-separated preferred locations',
  `willing_to_relocate` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `phone`, `location`, `profile_photo`, `resume_path`, `bio`, `linkedin_url`, `github_url`, `portfolio_url`, `total_experience_years`, `current_company`, `current_designation`, `preferred_job_type_id`, `preferred_work_mode_id`, `expected_salary_min`, `expected_salary_max`, `is_active`, `email_verified`, `created_at`, `updated_at`, `last_login`, `job_alert_email`, `preferred_locations`, `willing_to_relocate`) VALUES
(2, 'siva', 'techlume111@gmail.com', '$2y$12$0uCTqz0SADQbWz24utA/GOnJ9cIRNhZcYPjOkIZUAJJFEmitffxvi', '8500721069', 'Delhi', 'profile_2_1771052874.png', 'resume_2_1771215420.pdf', '', '', '', '', 2, '', 'Junior Developer', NULL, 1, NULL, NULL, 1, 0, '2026-02-14 06:09:33', '2026-02-16 04:50:19', '2026-02-14 06:09:41', 1, 'pune', 1),
(3, 'testing', 'testing@gmail.com', '$2y$12$z9lfJXYA9C6VxUQEnryVa.BR5roGVNGt4fqB4fc6.fdYd3oG3n5um', '9988776655', 'guntur', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-02-17 11:27:59', '2026-02-17 11:28:09', '2026-02-17 11:28:09', 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_education`
--

CREATE TABLE `user_education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `institution` varchar(200) NOT NULL,
  `field_of_study` varchar(100) DEFAULT NULL,
  `start_year` year(4) DEFAULT NULL,
  `end_year` year(4) DEFAULT NULL,
  `percentage_cgpa` varchar(10) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_education`
--

INSERT INTO `user_education` (`id`, `user_id`, `degree`, `institution`, `field_of_study`, `start_year`, `end_year`, `percentage_cgpa`, `is_current`, `created_at`) VALUES
(2, 2, 'btech', 'iit', 'cse', '2025', '2026', '11', 0, '2026-02-16 04:23:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_experience`
--

CREATE TABLE `user_experience` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `employment_type` enum('Full-time','Part-time','Contract','Internship','Freelance') DEFAULT 'Full-time',
  `location` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_experience`
--

INSERT INTO `user_experience` (`id`, `user_id`, `company_name`, `designation`, `employment_type`, `location`, `start_date`, `end_date`, `is_current`, `description`, `created_at`) VALUES
(1, 2, 'TCS', 'SE', 'Part-time', 'Ahmedabad, Gujarat', '2025-06-16', NULL, 1, '', '2026-02-16 12:40:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_job_preferences`
--

CREATE TABLE `user_job_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_category_id` int(11) NOT NULL,
  `priority` tinyint(4) DEFAULT 1 COMMENT '1=Low, 2=Medium, 3=High',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_job_preferences`
--

INSERT INTO `user_job_preferences` (`id`, `user_id`, `job_category_id`, `priority`, `created_at`) VALUES
(4, 2, 11, 3, '2026-02-17 11:18:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') DEFAULT 'Intermediate',
  `years_of_experience` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_name`, `proficiency_level`, `years_of_experience`, `created_at`) VALUES
(2, 2, 'PHP', 'Beginner', 1, '2026-02-16 04:50:40');

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
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_employer_id` (`employer_id`);

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
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`job_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_saved_at` (`saved_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `fk_user_job_type` (`preferred_job_type_id`),
  ADD KEY `fk_user_work_mode` (`preferred_work_mode_id`);

--
-- Indexes for table `user_education`
--
ALTER TABLE `user_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_end_year` (`end_year`);

--
-- Indexes for table `user_experience`
--
ALTER TABLE `user_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_current` (`is_current`),
  ADD KEY `idx_end_date` (`end_date`);

--
-- Indexes for table `user_job_preferences`
--
ALTER TABLE `user_job_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_preference` (`user_id`,`job_category_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category_id` (`job_category_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_skill_name` (`skill_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_education`
--
ALTER TABLE `user_education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_experience`
--
ALTER TABLE `user_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_job_preferences`
--
ALTER TABLE `user_job_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `fk_saved_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_job_type` FOREIGN KEY (`preferred_job_type_id`) REFERENCES `master_employment_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_work_mode` FOREIGN KEY (`preferred_work_mode_id`) REFERENCES `master_work_modes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_education`
--
ALTER TABLE `user_education`
  ADD CONSTRAINT `fk_education_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_experience`
--
ALTER TABLE `user_experience`
  ADD CONSTRAINT `fk_experience_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_job_preferences`
--
ALTER TABLE `user_job_preferences`
  ADD CONSTRAINT `fk_pref_category` FOREIGN KEY (`job_category_id`) REFERENCES `master_job_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `fk_skill_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
