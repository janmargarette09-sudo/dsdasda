-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 09:12 AM
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
-- Database: `teacher_load_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assignment_type` enum('auto','manual') DEFAULT 'auto',
  `rationale` text DEFAULT NULL,
  `status` enum('active','removed','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `teacher_id`, `schedule_id`, `assigned_by`, `assignment_type`, `rationale`, `status`, `created_at`, `updated_at`) VALUES
(15, 1, 1, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(16, 5, 10, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(17, 1, 5, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(18, 2, 3, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(19, 3, 8, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(20, 4, 11, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(21, 1, 2, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(22, 1, 6, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(23, 2, 4, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(24, 3, 9, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(25, 1, 7, 1, 'auto', 'Auto-matched: Available slot, Load 0%, Full-time', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01'),
(26, 6, 12, 1, 'auto', 'Auto-matched: Expertise match: 1x, Available slot, Load 0%', 'active', '2026-04-28 05:21:01', '2026-04-28 05:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 19:18:08'),
(2, 1, 'login', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 20:05:09'),
(3, 1, 'export', 'report', NULL, 'Exported load report as csv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 20:08:05'),
(4, 1, 'export', 'report', NULL, 'Exported load report as pdf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 20:08:11'),
(5, 1, 'auto_match', NULL, NULL, 'Auto-matched 12 assignments', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 20:53:38'),
(6, 1, 'login', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:16:36'),
(7, 1, 'delete', 'assignment', 3, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:16:50'),
(8, 1, 'delete', 'assignment', 1, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:16:55'),
(9, 1, 'delete', 'assignment', 14, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:17:00'),
(10, 1, 'delete', 'assignment', 4, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:17:05'),
(11, 1, 'delete', 'assignment', 5, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:41'),
(12, 1, 'delete', 'assignment', 6, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:44'),
(13, 1, 'delete', 'assignment', 7, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:46'),
(14, 1, 'delete', 'assignment', 8, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:47'),
(15, 1, 'delete', 'assignment', 9, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:49'),
(16, 1, 'delete', 'assignment', 10, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:51'),
(17, 1, 'delete', 'assignment', 11, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:53'),
(18, 1, 'delete', 'assignment', 12, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:54'),
(19, 1, 'delete', 'assignment', 13, 'Assignment removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:18:56'),
(20, 1, 'auto_match', NULL, NULL, 'Auto-matched 12 assignments', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 05:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` enum('1st','2nd','summer') DEFAULT '1st',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `subject_id`, `day_of_week`, `start_time`, `end_time`, `room`, `section`, `school_year`, `semester`, `is_active`, `created_at`) VALUES
(1, 1, 'Mon', '08:00:00', '10:00:00', 'Lab 1', 'CS-1A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(2, 1, 'Wed', '08:00:00', '10:00:00', 'Lab 1', 'CS-1A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(3, 2, 'Tue', '10:00:00', '12:00:00', 'Room 201', 'CS-1B', '2024-2025', '2nd', 1, '2026-04-27 18:58:25'),
(4, 2, 'Thu', '10:00:00', '12:00:00', 'Room 201', 'CS-1B', '2024-2025', '2nd', 1, '2026-04-27 18:58:25'),
(5, 3, 'Mon', '13:00:00', '15:00:00', 'Lab 2', 'CS-2A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(6, 3, 'Wed', '13:00:00', '15:00:00', 'Lab 2', 'CS-2A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(7, 4, 'Fri', '08:00:00', '11:00:00', 'Room 301', 'CS-2B', '2024-2025', '2nd', 1, '2026-04-27 18:58:25'),
(8, 5, 'Tue', '14:00:00', '16:00:00', 'Lab 3', 'IT-1A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(9, 5, 'Thu', '14:00:00', '16:00:00', 'Lab 3', 'IT-1A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(10, 6, 'Mon', '10:00:00', '12:00:00', 'Lab 4', 'IT-2A', '2024-2025', '1st', 0, '2026-04-27 18:58:25'),
(11, 7, 'Wed', '08:00:00', '11:00:00', 'Room 101', 'MATH-1A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(12, 8, 'Fri', '13:00:00', '15:00:00', 'Room 202', 'CS-3A', '2024-2025', '1st', 1, '2026-04-27 18:58:25'),
(13, 2, 'Tue', '07:30:00', '10:30:00', '', '', '2024-2025', '2nd', 1, '2026-04-28 05:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `units` decimal(3,1) NOT NULL DEFAULT 3.0,
  `lecture_hours` decimal(4,1) DEFAULT 3.0,
  `lab_hours` decimal(4,1) DEFAULT 0.0,
  `department` varchar(50) DEFAULT NULL,
  `semester` enum('1st','2nd','summer') DEFAULT '1st',
  `year_level` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `units`, `lecture_hours`, `lab_hours`, `department`, `semester`, `year_level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CS101', 'Introduction to Programming', 'Fundamentals of programming using Python', 3.0, 2.0, 1.0, 'Computer Science', '1st', 1, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(2, 'CS102', 'Data Structures', 'Arrays, linked lists, trees, graphs', 3.0, 2.0, 1.0, 'Computer Science', '2nd', 1, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(3, 'CS201', 'Database Systems', 'Relational DB design, SQL, normalization', 3.0, 2.0, 1.0, 'Computer Science', '1st', 2, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(4, 'CS202', 'Algorithms', 'Sorting, searching, dynamic programming', 3.0, 3.0, 0.0, 'Computer Science', '2nd', 2, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(5, 'IT101', 'Web Development', 'HTML, CSS, JavaScript, PHP', 3.0, 1.0, 2.0, 'Information Technology', '1st', 1, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(6, 'IT201', 'System Administration', 'Linux, Windows server management', 3.0, 2.0, 1.0, 'Information Technology', '1st', 2, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(7, 'MATH101', 'Discrete Mathematics', 'Logic, sets, graphs, combinatorics', 3.0, 3.0, 0.0, 'Mathematics', '1st', 1, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(8, 'CS301', 'Software Engineering', 'SDLC, agile, design patterns', 3.0, 2.0, 1.0, 'Computer Science', '1st', 3, 1, '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(9, 'CS304', 'AMC', 'APP', 3.0, 3.0, 3.0, 'Information Technology', '1st', 3, 1, '2026-04-27 19:40:29', '2026-04-27 19:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `subject_prerequisites`
--

CREATE TABLE `subject_prerequisites` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `prerequisite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `max_units` decimal(4,1) DEFAULT 24.0,
  `min_units` decimal(4,1) DEFAULT 12.0,
  `employment_type` enum('full_time','part_time','contractual') DEFAULT 'full_time',
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `max_units`, `min_units`, `employment_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'T001', 'Maria', 'Santos', 'maria.santos@school.edu', '09171234501', 'Computer Science', 24.0, 12.0, 'full_time', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(2, 'T002', 'Juan', 'Dela Cruz', 'juan.delacruz@school.edu', '09171234502', 'Computer Science', 24.0, 12.0, 'full_time', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(3, 'T003', 'Ana', 'Reyes', 'ana.reyes@school.edu', '09171234503', 'Information Technology', 18.0, 9.0, 'part_time', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(4, 'T004', 'Pedro', 'Garcia', 'pedro.garcia@school.edu', '09171234504', 'Mathematics', 24.0, 12.0, 'full_time', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(5, 'T005', 'Lisa', 'Lim', 'lisa.lim@school.edu', '09171234505', 'Information Technology', 24.0, 12.0, 'full_time', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(6, 'T006', 'Carlos', 'Tan', 'carlos.tan@school.edu', '09171234506', 'Computer Science', 15.0, 6.0, 'contractual', 'active', '2026-04-27 18:58:25', '2026-04-27 18:58:25'),
(7, 'hfdf', 'hfgdfgd', 'gcdgcvhhcg', 'cgfcfg@sd.com', '121454531', 'Information Technology', 24.0, 12.0, 'full_time', 'active', '2026-04-27 19:36:42', '2026-04-27 19:36:42'),
(10, 'hfdfad', 'hfgdfgd', 'gcdgcvhhcg', 'cgfcfg@sd.com', '121454531', 'Information Technology', 24.0, 12.0, 'full_time', 'active', '2026-04-27 19:37:53', '2026-04-27 19:37:53');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_availability`
--

CREATE TABLE `teacher_availability` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_preferred` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_availability`
--

INSERT INTO `teacher_availability` (`id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `is_preferred`) VALUES
(1, 1, 'Mon', '08:00:00', '17:00:00', 1),
(2, 1, 'Wed', '08:00:00', '17:00:00', 1),
(3, 1, 'Fri', '08:00:00', '12:00:00', 1),
(4, 2, 'Tue', '08:00:00', '17:00:00', 1),
(5, 2, 'Thu', '08:00:00', '17:00:00', 1),
(6, 3, 'Tue', '14:00:00', '18:00:00', 1),
(7, 3, 'Thu', '14:00:00', '18:00:00', 1),
(8, 4, 'Mon', '08:00:00', '17:00:00', 1),
(9, 4, 'Wed', '08:00:00', '17:00:00', 1),
(10, 5, 'Mon', '10:00:00', '17:00:00', 1),
(11, 6, 'Fri', '13:00:00', '17:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_expertise`
--

CREATE TABLE `teacher_expertise` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_area` varchar(100) NOT NULL,
  `proficiency_level` enum('primary','secondary','tertiary') DEFAULT 'primary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_expertise`
--

INSERT INTO `teacher_expertise` (`id`, `teacher_id`, `subject_area`, `proficiency_level`) VALUES
(1, 1, 'Programming', 'primary'),
(2, 1, 'Database Systems', 'secondary'),
(3, 2, 'Data Structures', 'primary'),
(4, 2, 'Algorithms', 'primary'),
(5, 3, 'Web Development', 'primary'),
(6, 3, 'Networking', 'secondary'),
(7, 4, 'Discrete Mathematics', 'primary'),
(8, 4, 'Calculus', 'secondary'),
(9, 5, 'System Administration', 'primary'),
(10, 5, 'Cybersecurity', 'secondary'),
(11, 6, 'Software Engineering', 'primary'),
(14, 7, 'It', 'primary');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','chair') DEFAULT 'chair',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Program Chair Admin', 'admin@school.edu', 'admin', 1, '2026-04-27 19:17:55', '2026-04-27 19:17:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_teacher_schedule` (`teacher_id`,`schedule_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_assignments_teacher` (`teacher_id`),
  ADD KEY `idx_assignments_schedule` (`schedule_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_schedules_day` (`day_of_week`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_subjects_dept` (`department`),
  ADD KEY `idx_subjects_active` (`is_active`);

--
-- Indexes for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prereq` (`subject_id`,`prerequisite_id`),
  ADD KEY `prerequisite_id` (`prerequisite_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_teachers_status` (`status`),
  ADD KEY `idx_teachers_dept` (`department`);

--
-- Indexes for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher_expertise`
--
ALTER TABLE `teacher_expertise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teacher_expertise`
--
ALTER TABLE `teacher_expertise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD CONSTRAINT `subject_prerequisites_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_availability`
--
ALTER TABLE `teacher_availability`
  ADD CONSTRAINT `teacher_availability_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_expertise`
--
ALTER TABLE `teacher_expertise`
  ADD CONSTRAINT `teacher_expertise_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
