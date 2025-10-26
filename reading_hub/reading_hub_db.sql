-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 09:28 PM
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
-- Database: `reading_hub_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action_type`, `target_id`, `details`, `timestamp`) VALUES
(1, 1, 'login', 1, 'Student logged in successfully.', '2025-10-19 16:02:49'),
(3, 3, 'signup', 3, 'New user signed up.', '2025-10-19 16:18:25'),
(4, 3, 'login', 3, 'User logged in successfully.', '2025-10-19 16:18:41'),
(5, 3, 'logout', 3, 'User logged out.', '2025-10-19 16:19:05'),
(6, 4, 'signup', 4, 'New librarian account created: librarian1', '2025-10-19 16:20:46'),
(7, 4, 'login', 4, 'User logged in successfully.', '2025-10-19 16:21:00'),
(8, 4, 'borrow_book', 5, 'Borrowed 1 copies of A Brief History of Time', '2025-10-19 16:47:46'),
(9, 4, 'logout', 4, 'User logged out.', '2025-10-19 16:48:32'),
(10, 3, 'login', 3, 'User logged in successfully.', '2025-10-19 16:48:41'),
(11, 3, 'borrow_book', 5, 'Borrowed 1 copies of A Brief History of Time', '2025-10-19 16:50:54'),
(12, 3, 'logout', 3, 'User logged out.', '2025-10-19 16:51:16'),
(13, 3, 'login', 3, 'User logged in successfully.', '2025-10-19 16:51:28'),
(14, 4, 'logout', 4, 'User logged out.', '2025-10-20 08:05:12'),
(15, 3, 'login', 3, 'User logged in successfully.', '2025-10-20 08:05:24'),
(16, 4, 'login', 4, 'User logged in successfully.', '2025-10-20 11:21:29'),
(17, 4, 'login', 4, 'User logged in successfully.', '2025-10-20 11:34:20'),
(18, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 14:47:54'),
(19, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 14:48:07'),
(20, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 15:49:54'),
(21, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 15:50:56'),
(22, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 15:54:59'),
(23, 4, 'login', 4, 'User logged in successfully.', '2025-10-20 15:55:36'),
(24, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 15:56:06'),
(25, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 15:59:42'),
(26, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 16:05:26'),
(27, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 16:05:38'),
(28, 4, 'change_password', 1, 'Password changed for user ID 1', '2025-10-20 16:05:58'),
(29, 4, 'update_user', 1, 'Updated user details via AJAX', '2025-10-20 16:08:53'),
(30, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 11:34:48'),
(31, 3, 'logout', 3, 'User logged out.', '2025-10-21 12:50:19'),
(32, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 12:54:08'),
(33, 3, 'logout', 3, 'User logged out.', '2025-10-21 13:09:57'),
(34, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 13:14:36'),
(35, 3, 'logout', 3, 'User logged out.', '2025-10-21 13:18:02'),
(36, 4, 'logout', 4, 'User logged out.', '2025-10-21 13:53:29'),
(37, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 13:53:37'),
(38, 3, 'logout', 3, 'User logged out.', '2025-10-21 13:54:36'),
(39, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 13:54:50'),
(40, 4, 'logout', 4, 'User logged out.', '2025-10-21 13:55:07'),
(41, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 14:02:52'),
(42, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 14:07:32'),
(43, 1, 'logout', 1, 'User logged out.', '2025-10-21 14:07:51'),
(44, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 14:08:01'),
(45, 3, 'logout', 3, 'User logged out.', '2025-10-21 14:08:07'),
(46, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 14:08:18'),
(47, 1, 'borrow_book', 13, 'Borrowed 1 copies of Leaping High in Mathematics', '2025-10-21 14:13:21'),
(48, 1, 'renew_loan', 5, 'Renewed loan ID: 5', '2025-10-21 14:13:35'),
(49, 1, 'renew_loan', 5, 'Renewed loan ID: 5', '2025-10-21 14:13:37'),
(50, 1, 'return_book', 5, 'Returned book for loan ID: 5', '2025-10-21 14:13:42'),
(51, 1, 'return_book', 5, 'Returned book for loan ID: 5', '2025-10-21 14:13:49'),
(52, 1, 'borrow_book', 73, 'Borrowed 1 copies of Si Aling Oktopoda at Ang Walong Munting Pugita', '2025-10-21 14:14:32'),
(53, 4, 'extend_due_date', 6, 'Extended due date for loan ID 6 to 2025-10-24', '2025-10-21 14:16:19'),
(54, 4, 'return_book', 6, 'Book returned for loan ID 6', '2025-10-21 14:17:09'),
(55, 1, 'logout', 1, 'User logged out.', '2025-10-21 14:24:12'),
(56, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 14:24:20'),
(57, 3, 'logout', 3, 'User logged out.', '2025-10-21 15:57:56'),
(58, 4, 'change_password', 3, 'Password changed for user ID 3', '2025-10-21 16:27:00'),
(59, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 16:27:11'),
(60, 3, 'logout', 3, 'User logged out.', '2025-10-21 16:31:46'),
(61, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 16:31:57'),
(62, 1, 'borrow_book', 86, 'Borrowed 1 copies of Bru-ha-ha-ha-ha-ha… Bru-hi-hi-hi-hi-hi…', '2025-10-21 16:35:27'),
(63, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:35:58'),
(64, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:36:00'),
(65, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:36:02'),
(66, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:36:07'),
(67, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:36:16'),
(68, 1, 'logout', 1, 'User logged out.', '2025-10-21 16:42:02'),
(69, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 16:42:09'),
(70, 4, 'assess_penalty', 7, 'Assessed penalty of 10 for loan ID 7 to user 1', '2025-10-21 16:45:04'),
(71, 3, 'logout', 3, 'User logged out.', '2025-10-21 16:51:42'),
(72, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 16:51:54'),
(73, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 16:52:22'),
(74, 1, 'renew_loan', 7, 'Renewed loan ID: 7', '2025-10-21 17:12:58'),
(75, 1, 'logout', 1, 'User logged out.', '2025-10-21 17:13:05'),
(76, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 17:13:31'),
(77, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 17:48:58'),
(78, 4, 'logout', 4, 'User logged out.', '2025-10-21 17:51:47'),
(79, 3, 'login', 3, 'User logged in successfully.', '2025-10-21 17:51:54'),
(80, 3, 'logout', 3, 'User logged out.', '2025-10-21 17:52:35'),
(81, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 17:52:46'),
(82, 1, 'logout', 1, 'User logged out.', '2025-10-21 17:53:38'),
(83, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 17:55:14'),
(84, 4, 'logout', 4, 'User logged out.', '2025-10-21 18:00:02'),
(85, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 18:00:22'),
(86, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 18:24:07'),
(87, 4, 'logout', 4, 'User logged out.', '2025-10-21 18:34:59'),
(88, 1, 'login', 1, 'User logged in successfully.', '2025-10-21 18:35:12'),
(89, 1, 'logout', 1, 'User logged out.', '2025-10-21 18:35:24'),
(90, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 18:50:22'),
(91, 4, 'change_password', 3, 'Password changed for user ID 3', '2025-10-21 18:51:17'),
(92, 4, 'logout', 4, 'User logged out.', '2025-10-21 18:52:56'),
(93, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 18:56:01'),
(94, 4, 'logout', 4, 'User logged out.', '2025-10-21 18:57:15'),
(95, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 19:04:23'),
(96, 4, 'logout', 4, 'User logged out.', '2025-10-21 19:17:32'),
(97, 3, 'logout', 3, 'User logged out.', '2025-10-21 19:27:04'),
(98, 4, 'login', 4, 'User logged in successfully.', '2025-10-21 19:27:14'),
(99, 4, 'logout', 4, 'User logged out.', '2025-10-21 19:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `biography` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `author_name`, `biography`, `created_at`) VALUES
(1, 'Dr. Alex Kumar', 'Expert in machine learning and AI', '2025-10-19 16:02:49'),
(2, 'Maria Rodriguez', 'Renowned engineer and author', '2025-10-19 16:02:49'),
(3, 'Robert Johnson', 'Physics professor and writer', '2025-10-19 16:02:49'),
(4, 'Jane Austen', 'Classic fiction author', '2025-10-19 16:02:49'),
(5, 'Stephen Hawking', 'Theoretical physicist', '2025-10-19 16:02:49'),
(6, 'Laura Raymundo-Jugueta', 'Biography not available.', '2025-10-20 10:34:37'),
(7, 'Fides A. Del Castillo', 'Biography not available.', '2025-10-20 10:34:37'),
(8, 'Nenita A. Apolinario', 'Biography not available.', '2025-10-20 10:34:37'),
(9, 'Ethel Grace A . Ramos', 'Biography not available.', '2025-10-20 10:34:37'),
(10, 'Marina N. Tawag', 'Biography not available.', '2025-10-20 10:45:25'),
(11, 'Josefina V. Suarez', 'Biography not available.', '2025-10-20 10:45:25'),
(12, 'Rosario Claridad Cruz', 'Biography not available.', '2025-10-20 10:45:25'),
(13, 'Annabelle Faner-Baraan', 'Biography not available.', '2025-10-20 10:45:25'),
(14, 'Carolina T. Tirona', 'Biography not available.', '2025-10-20 10:45:25'),
(15, 'Lydia Luza-Libunao', 'Biography not available.', '2025-10-20 10:45:25'),
(16, 'Evelyn C. Jacinto', 'Biography not available.', '2025-10-20 10:45:25'),
(17, 'Maria Soledad A. Dagale', 'Biography not available.', '2025-10-20 10:45:25'),
(18, 'Corazon Capati Concepcion', 'Biography not available.', '2025-10-20 10:45:25'),
(19, 'Ethel Grace A. Ramos', 'Biography not available.', '2025-10-20 10:45:25'),
(20, 'Eden P. Battad', 'Biography not available.', '2025-10-20 10:45:25'),
(21, 'Gloria S. Siman', 'Biography not available.', '2025-10-20 10:45:25'),
(22, 'Dr. Lilia R.Cortez', 'Biography not available.', '2025-10-20 10:45:25'),
(23, 'Donna Belle Piscasio-Bautista', 'Biography not available.', '2025-10-20 10:45:25'),
(24, 'Evelyn M. Eva', 'Biography not available.', '2025-10-20 10:45:25'),
(25, 'Shirley Torre Equipado', 'Biography not available.', '2025-10-20 10:45:25'),
(26, 'Jane Prima R. Paguio', 'Biography not available.', '2025-10-20 10:45:25'),
(27, 'Zenaida V. Maloloyon', 'Biography not available.', '2025-10-20 10:45:25'),
(28, 'Leticia M. Silla', 'Biography not available.', '2025-10-20 10:45:25'),
(29, 'Ferdilyn C. Lacia', 'Biography not available.', '2025-10-20 10:45:25'),
(30, 'Tracylyn H. Umandal', 'Biography not available.', '2025-10-20 10:45:25'),
(31, 'Nelda R. Francisco', 'Biography not available.', '2025-10-20 10:45:25'),
(32, 'Osmunda L. Pineda', 'Biography not available.', '2025-10-20 10:45:25'),
(33, 'Jocelyn L. Lagarto', 'Biography not available.', '2025-10-20 10:45:25'),
(34, 'Angelica Rocio T. Bautista', 'Biography not available.', '2025-10-20 10:45:25'),
(35, 'Yolanda P. Bilgera', 'Biography not available.', '2025-10-20 10:45:25'),
(36, 'MA. Angelica S. Padin', 'Biography not available.', '2025-10-20 10:45:25'),
(37, 'Lilibet Alcala-Amatong', 'Biography not available.', '2025-10-20 10:45:25'),
(38, 'Pia Patricia P. Tenedero', 'Biography not available.', '2025-10-20 10:45:25'),
(39, 'Maricel C.Badilla', 'Biography not available.', '2025-10-20 10:45:25'),
(40, 'Emilia L. Banlaygas', 'Biography not available.', '2025-10-20 10:45:25'),
(41, 'Merlita R. Dimanlanta', 'Biography not available.', '2025-10-20 10:45:25'),
(42, 'Shiela M. Salangsang', 'Biography not available.', '2025-10-20 10:45:25'),
(43, 'Emilia L. Banlayas', 'Biography not available.', '2025-10-20 10:45:25'),
(44, 'Eleanor D. Antonio', 'Biography not available.', '2025-10-20 10:45:25'),
(45, 'Emilia L. Banlaygas Eleanor D. Antonio', 'Biography not available.', '2025-10-20 10:45:25'),
(46, 'Mildred Frago-Ruallo', 'Biography not available.', '2025-10-20 10:45:25'),
(47, 'Anna Cristina G. Nadora', 'Biography not available.', '2025-10-20 10:45:25'),
(48, 'Rene Villanueva', 'Biography not available.', '2025-10-20 10:45:25'),
(49, 'Virgilio S. Almario', 'Biography not available.', '2025-10-20 10:45:25'),
(50, 'Rhandee Garlitos', 'Biography not available.', '2025-10-20 10:45:25'),
(51, 'Aliona Silva', 'Biography not available.', '2025-10-20 10:45:25'),
(52, 'German V. Gervacio', 'Biography not available.', '2025-10-20 10:45:25'),
(53, 'Susan Dela Rosa Aragon', 'Biography not available.', '2025-10-20 10:45:25'),
(54, 'L. E. Antonio', 'Biography not available.', '2025-10-20 10:45:25'),
(55, 'Ma. Corazon Remigio', 'Biography not available.', '2025-10-20 10:45:25'),
(56, 'Rebecca T. Anonuevo', 'Biography not available.', '2025-10-20 10:45:25'),
(57, 'Lamberto E. Antonio', 'Biography not available.', '2025-10-20 10:45:25'),
(58, 'Mike Bigornia', 'Biography not available.', '2025-10-20 10:45:25'),
(59, 'Al Santos', 'Biography not available.', '2025-10-20 10:45:25'),
(60, 'Rodolfo Desuasido', 'Biography not available.', '2025-10-20 10:45:25'),
(61, 'Luis P. Gatmaitan', 'Biography not available.', '2025-10-20 10:45:25'),
(62, 'Rebecca Anonuevo', 'Biography not available.', '2025-10-20 10:45:25'),
(63, 'Joy Ceres', 'Biography not available.', '2025-10-20 10:45:25'),
(64, 'Celine Diego', 'Biography not available.', '2025-10-20 10:45:25'),
(65, 'Lourdes C. Visaya', 'Biography not available.', '2025-10-20 10:45:25'),
(66, 'Jaycee Nuestra', 'Biography not available.', '2025-10-20 10:45:25'),
(67, 'Perla H. Cuanzon', 'Biography not available.', '2025-10-20 10:45:25'),
(68, 'Catherine Yu Untalan', 'Biography not available.', '2025-10-20 10:45:25'),
(69, 'Becky Bravo', 'Biography not available.', '2025-10-20 10:45:25'),
(70, 'Purification C. Balingit', 'Biography not available.', '2025-10-20 10:45:25'),
(71, 'Fiona Fajardo', 'Biography not available.', '2025-10-20 10:45:25'),
(72, 'Gloria Villaraza-Guzman', 'Biography not available.', '2025-10-20 10:45:25'),
(73, 'Kristine Canon', 'Biography not available.', '2025-10-20 10:45:25'),
(74, 'Segunda D. Matias', 'Biography not available.', '2025-10-20 10:45:25'),
(75, 'Renato C. Vibiesca', 'Biography not available.', '2025-10-20 10:45:25'),
(76, 'Yvette Fernandez', 'Biography not available.', '2025-10-20 10:45:25'),
(77, 'May Tobias-Papa', 'Biography not available.', '2025-10-20 10:45:25'),
(78, 'Russell Molina', 'Biography not available.', '2025-10-20 10:45:25'),
(79, 'Boots S. Agbayani-Pastor', 'Biography not available.', '2025-10-20 10:45:25'),
(80, 'H. A. Rey', 'Biography not available.', '2025-10-20 10:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `illustrator` varchar(100) DEFAULT NULL,
  `quantity_total` int(11) NOT NULL DEFAULT 1 CHECK (`quantity_total` >= 1),
  `quantity_available` int(11) NOT NULL DEFAULT 1 CHECK (`quantity_available` >= 0),
  `date_added` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author_id`, `genre_id`, `year_level`, `illustrator`, `quantity_total`, `quantity_available`, `date_added`, `created_at`) VALUES
(1, 'Machine Learning Fundamentals', 1, 5, 'Grade 12', NULL, 3, 2, '2024-01-15', '2025-10-19 16:02:49'),
(2, 'Digital Signal Processing', 2, 4, 'Grade 11', NULL, 2, 1, '2024-02-01', '2025-10-19 16:02:49'),
(3, 'Modern Physics', 3, 3, 'Grade 10', NULL, 1, 0, '2024-03-10', '2025-10-19 16:02:49'),
(4, 'Pride and Prejudice', 4, 1, 'Grade 9', NULL, 5, 4, '2023-12-01', '2025-10-19 16:02:49'),
(5, 'A Brief History of Time', 5, 2, 'Grade 12', NULL, 4, 1, '2024-01-20', '2025-10-19 16:02:49'),
(6, 'Start Smart with MAPE', 6, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 10:34:37'),
(7, 'KENOSIS: The Life-Giving Sacrifice of Jesus', 7, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 10:34:37'),
(8, 'Science', 8, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:34:37'),
(9, 'Start Smart with MAPE', 6, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(10, 'KENOSIS: The Life-Giving Sacrifice of Jesus', 7, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(11, 'Science', 8, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(12, 'Easy Writing', 10, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(13, 'Leaping High in Mathematics', 11, 6, 'K', '', 2, 3, '2025-10-20', '2025-10-20 10:45:25'),
(14, 'Creative Expressions Math', 12, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(15, 'My Learning Friend In Math', 13, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(16, 'Ladders to Learning - Reading and Phonics', 14, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(17, 'Ladders to Learning - Writing', 14, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(18, 'I Love Writing', 15, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(19, 'Stepping Through Language - The Awareness Series', 16, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(20, 'Reading and Me', 17, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(21, 'Fun With Math', 17, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(22, 'Language for Kids', 18, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(23, 'Moving on in English', 11, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(24, 'Language Power - Revised Edition', 19, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(25, 'Learning Language with Fun', 20, 6, 'K-1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(26, 'Getting Ready for Writing', 21, 6, 'Pre-K', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(27, 'Workjobs Numbers', 22, 6, '', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(28, 'Creative Expressions Math', 12, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(29, 'Mathematics for a better Future', 23, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(30, 'Ladders to Learning Mathematics', 24, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(31, 'Easy Writing', 10, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(32, 'Reading Power', 8, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(33, 'Science and Health', 25, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(34, 'My Learning Friend in Language', 26, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(35, 'Language for Kids', 18, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(36, 'Reading Magic', 27, 6, 'Nursery', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(37, 'English for Global Communication', 28, 6, '1', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(38, 'Essential English', 29, 6, '1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(39, 'Essential English', 30, 6, '1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(40, 'Speech Challenge', 31, 6, '1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(41, 'New Dynamic Series in English', 11, 6, '2', '', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(42, 'English Basics and Beyond', 32, 6, '2', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(43, 'Radiance Teacher’s Resource Material', 33, 6, '2', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(44, 'New Dynamic Series in English', 11, 6, '4', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(45, 'English Encounter: Language', 34, 6, '4', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(46, 'Home Economics Livelihood education in the New Generation', 35, 6, '4', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(47, 'English Encounters: Reading', 36, 6, '5', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(48, 'Spoken English - New Edition', 37, 6, '5', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(49, 'English Encounters: Reading', 38, 6, '6', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(50, 'Spoken English - New Edition', 39, 6, '6', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(51, 'Yamang Filipino - Batayang Kagamitang Pampagtuturo', 40, 7, '6', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(52, 'Komunikasyon - Batayang Kagamitang Pampagtuturo', 41, 7, '5', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(53, 'Aklat sa Wika at Pagbasa Bagong Filipino Tungo sa Globalisasyon', 42, 7, '5', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(54, 'Yamang Filipino', 40, 7, '5', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(55, 'Komunikasyon - Batayang Kagamitang Pampagtuturo', 43, 7, '2', '', 4, 4, '2025-10-20', '2025-10-20 10:45:25'),
(56, 'Kayamanan - Batayang Kagamitang Pampagtuturo', 44, 7, '4', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(57, 'Yamang Filipino - Batayang Kagamitang Pampagtuturo', 40, 7, '4', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(58, 'Komunikasyon - Batayang Kagamitang Pampagtuturo', 44, 7, '3', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(59, 'Kayamanan - Batayang Kagamitang Pampagtuturo', 44, 7, '3', '', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(60, 'Aklat sa Wika at Pagbasa Bagong Filipino Tungo sa Globalisasyon', 42, 7, '3', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(61, 'Yamang Filipino - Batayang Kagamitang Pampagtuturo', 40, 7, '3', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(62, 'Kayamanan - Batayang Kagamitang Pampagtuturo', 44, 7, '2', '', 4, 4, '2025-10-20', '2025-10-20 10:45:25'),
(63, 'Komunikasyon - Batayang Kagamitang Pampagtuturo', 40, 7, '2', '', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(64, 'Yamang Filipino - Batayang Kagamitang Pampagtuturo', 40, 7, '2', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(65, 'Aklat sa Wika at Pagbasa Bagong Filipino Tungo sa Globalisasyon', 42, 7, '2', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(66, 'Kayamanan - Batayang Kagamitang Pampagtuturo Bagong edisyon', 44, 7, '1', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(67, 'Kayamanan - Batayang Kagamitamg Pampagtuturo', 44, 7, '1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(68, 'Yamang Filipino - Batayang Kagamitang Pampagtuturo', 45, 7, '1', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(69, 'Aklat sa Wika at Pagbasa Bagong Filipino Tungo sa Globalisasyon', 42, 7, '1', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(70, 'Mga Tunog para sa Nagsisimulang Bumasa', 46, 7, 'K', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(71, 'Tuklas Lahi - Serye sa Araling Panlipunan para sa Preschool', 47, 7, 'P', '', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(72, 'Tiktaktok at Pikpakbum', 48, 8, '', 'Renato Gamos', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(73, 'Si Aling Oktopoda at Ang Walong Munting Pugita', 49, 8, '', 'Jess Abrera, Jr.', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(74, 'Isang Taon na si Beth', 48, 8, '', 'Peter Espina', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(75, 'Chenelyn! Chenelyn!', 50, 8, '', 'Liza Flores', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(76, 'Si Wako', 49, 8, '', 'Jess Abrera, Jr.', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(77, 'Si Emang Engkantada at ang Tatlong Haragan', 48, 8, '', 'Alfonso Onate, Wilfredo Pollarco', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(78, 'Ang Kamatis ni Peles', 49, 8, '', 'Renato Gamos', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(79, 'Si Nina sa Bayan ng Daldalina', 51, 8, '', 'Luisito Chua', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(80, 'Ang Mabait na Kalabaw', 49, 8, '', 'Liza Flores', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(81, 'Si Tanya, Ang Uwak na Gustong Pumuti', 52, 8, '', 'Gino G. Borja', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(82, 'Si Daginding', 49, 8, '', 'Renato Gamos', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(83, 'Nang Magkakulay Ang Nayon', 53, 8, '', 'Susan Dela Rosa Aragon', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(84, 'Si Monica Dalosdalos', 54, 8, '', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(85, 'Si Langgam at si Tipaklong', 49, 8, '', 'Renato Gamos', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(86, 'Bru-ha-ha-ha-ha-ha… Bru-hi-hi-hi-hi-hi…', 55, 8, '', 'Roland Micheal Ilagan', 1, 0, '2025-10-20', '2025-10-20 10:45:25'),
(87, 'Papel de Liha', 55, 8, '', 'Beth Parrocha-Doctolero', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(88, 'Ang Bisikleta ni Momon', 56, 8, '', 'Jo Ann Bereber-Gando', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(89, 'Alamat ng Lansones', 57, 8, '', 'Jose Prado', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(90, 'Kain, Kumain, Kinain', 58, 8, '', 'Jess Abrera, Jr.', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(91, 'Bilog na Itlog', 59, 8, '', 'Josefina Sanchez', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(92, 'Kaya ba ang Nanay ko?', 60, 8, '', 'Reynaldo Tiongson', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(93, 'Si Putot', 58, 8, '', 'Charles Funk', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(94, 'Ang Pambihirang Buhok ni Raquel', 61, 8, '', 'Beth Parrocha-Doctolero', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(95, 'Digong Dilaw', 49, 8, '', 'Nelson Canbrega', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(96, 'Ang Mahiyaing Manok', 62, 8, '', 'Ruben De Jesus', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(97, 'Tippity Top Super Top', 63, 9, '', 'Jomar S. Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(98, 'The Magic Box', 64, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(99, 'Orange Octa', 63, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(100, 'A Perfect Picnic Day', 65, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(101, 'Henry Hen’s New Hat', 66, 9, '', 'Larry A. Diolola, Jomar Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(102, 'Ducky Duck and Her Friends', 67, 9, '', 'Jomar S. Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(103, 'Fanny and the Fireflies', 65, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(104, 'Bakawan', 68, 9, '', 'Van Zeus Allen Bascon', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(105, 'Yuri, the Yellow Vinta', 63, 9, '', 'Larry A. Diolola, Jomar Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(106, 'Magic Mat', 49, 9, '', 'Joanne de Leon', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(107, 'Go!', 69, 9, '', 'Vanessa Tamayo', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(108, 'Mimi and the Mouse', 67, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(109, 'Vilma’s Vineyard Workers', 70, 9, '', 'Larry A. Diolola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(110, 'A Thirsty Sparrow', 49, 9, '', 'Jimmy Torres', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(111, 'Eggy Egg', 63, 9, '', 'Jomar S. Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(112, 'Mario’s Special Day', 71, 9, '', 'Mel Silvestre', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(113, 'Annie Ant', 70, 9, '', 'Jomar S. Montinola', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(114, 'Little Raindrop', 72, 9, '', 'Fidelito Manto', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(115, 'Si Pilong Patago-tago', 73, 10, '', 'Leo Alvarado', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(116, 'Ang Sarimanok', 74, 10, '', 'Erwin J. Arroza', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(117, 'Ang Tsinelas ni Inoy', 75, 10, '', 'James B. Abalos', 1, 1, '2025-10-20', '2025-10-20 10:45:25'),
(118, 'Siya ba Ang Inay ko', 74, 10, '', 'Jomike Tejido', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(119, 'Haluhalo Espesyal', 76, 10, '', 'Jill Arwen Posadas', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(120, 'Arab sa Palengke', 77, 10, '', 'Isabel Roxas', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(121, 'Anong Gupit natin Ngayon', 78, 10, '', 'Hubert Fucio', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(122, 'Taho, Taho, Taho… Tahoooieyy!', 75, 10, '', 'Ray-Ann Bernardo', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(123, 'The Milkmaid and her Jug', 79, 11, '', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(124, 'The Good Samaritan', 79, 11, '', '', 3, 3, '2025-10-20', '2025-10-20 10:45:25'),
(125, 'Curious George gets a Medal', 80, 11, '', '', 2, 2, '2025-10-20', '2025-10-20 10:45:25'),
(126, 'Start Smart with MAPE', 6, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 11:11:28'),
(127, 'KENOSIS: The Life-Giving Sacrifice of Jesus', 7, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 11:11:28'),
(128, 'Science', 8, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 11:11:28'),
(129, 'Start Smart with MAPE', 6, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 12:17:23'),
(130, 'KENOSIS: The Life-Giving Sacrifice of Jesus', 7, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 12:17:23'),
(131, 'Science', 8, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 12:17:23'),
(132, 'Start Smart with MAPE', 6, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 12:17:24'),
(133, 'KENOSIS: The Life-Giving Sacrifice of Jesus', 7, 6, 'P', '', 1, 1, '2025-10-20', '2025-10-20 12:17:24'),
(134, 'Science', 8, 6, 'K', '', 1, 1, '2025-10-20', '2025-10-20 12:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `genre_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `genre_name`, `description`, `created_at`) VALUES
(1, 'Fiction', 'Fictional stories and novels', '2025-10-19 16:02:49'),
(2, 'Non-Fiction', 'Real-world facts and biographies', '2025-10-19 16:02:49'),
(3, 'Science', 'Books on scientific topics', '2025-10-19 16:02:49'),
(4, 'Mathematics', 'Math-related books', '2025-10-19 16:02:49'),
(5, 'Computer Science', 'Programming and IT books', '2025-10-19 16:02:49'),
(6, 'Learning Material English', NULL, '2025-10-20 10:34:37'),
(7, 'Learning Material Filipino', NULL, '2025-10-20 10:45:25'),
(8, 'Big Children’s Book Tagalog', NULL, '2025-10-20 10:45:25'),
(9, 'Big Children’s Book English', NULL, '2025-10-20 10:45:25'),
(10, 'Small Children’s Book Tagalog', NULL, '2025-10-20 10:45:25'),
(11, 'Small Children’s Book English', NULL, '2025-10-20 10:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `extended_due_date` date DEFAULT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') NOT NULL DEFAULT 'borrowed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`loan_id`, `user_id`, `book_id`, `borrow_date`, `extended_due_date`, `due_date`, `return_date`, `status`, `created_at`) VALUES
(3, 4, 5, '2025-10-20', NULL, '2025-10-20', NULL, 'overdue', '2025-10-19 16:47:46'),
(4, 3, 5, '2025-10-20', NULL, '2025-10-20', NULL, 'overdue', '2025-10-19 16:50:54'),
(5, 1, 13, '2025-10-21', NULL, '2025-11-21', '2025-10-21', 'returned', '2025-10-21 14:13:21'),
(6, 1, 73, '2025-10-21', NULL, '2025-10-24', '2025-10-21', 'returned', '2025-10-21 14:14:32'),
(7, 1, 86, '2025-10-22', NULL, '2026-01-28', NULL, 'borrowed', '2025-10-21 16:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `user_id`, `type`, `message`, `date_sent`, `status`) VALUES
(1, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:35:44', 'read'),
(2, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:35:54', 'read'),
(3, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:36:00', 'read'),
(4, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:41:39', 'read'),
(5, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:41:40', 'read'),
(6, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:41:41', 'read'),
(7, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:41:58', 'read'),
(8, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:42:01', 'read'),
(9, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:42:04', 'read'),
(10, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:42:10', 'read'),
(11, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:43:22', 'read'),
(12, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:43:27', 'read'),
(13, 3, 'overdue', 'You have 1 overdue book(s). Please return them to avoid additional penalties.', '2025-10-20 16:43:57', 'read'),
(15, 4, 'password_change_request', 'Student Angela Mariel Torres (Username: Rielangeleu) has requested a password change. Desired new password: 012345678.', '2025-10-21 18:49:55', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `partner_libraries`
--

CREATE TABLE `partner_libraries` (
  `library_id` int(11) NOT NULL,
  `library_name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_libraries`
--

INSERT INTO `partner_libraries` (`library_id`, `library_name`, `address`, `contact_number`, `email`, `website`, `created_at`) VALUES
(1, 'National Library of the Philippines', 'T.M. Kalaw Ave, Ermita, Manila', '8527-7230', 'info@nlp.gov.ph', NULL, '2025-10-19 16:02:49'),
(2, 'Mapúa Reading Center', 'Gil Puyat Ave, Makati', '8891-0837', 'readingcenter@mapua.edu.ph', NULL, '2025-10-19 16:02:49'),
(3, 'Manila City Library', 'Taft Avenue, Manila', '8553-8395', 'citylibrary@manila.gov.ph', NULL, '2025-10-19 16:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `penalty_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 CHECK (`amount` >= 0),
  `status` enum('pending','paid','waived') NOT NULL DEFAULT 'pending',
  `date_assessed` date NOT NULL,
  `date_paid` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalties`
--

INSERT INTO `penalties` (`penalty_id`, `loan_id`, `user_id`, `amount`, `status`, `date_assessed`, `date_paid`, `created_at`) VALUES
(3, 3, 4, 20.00, 'pending', '2025-10-21', NULL, '2025-10-20 16:16:11'),
(4, 4, 3, 20.00, 'pending', '2025-10-21', NULL, '2025-10-20 16:16:11'),
(6, 7, 1, 10.00, 'pending', '2025-10-22', NULL, '2025-10-21 16:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `rec_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) NOT NULL,
  `availability_status` enum('available','unavailable') DEFAULT 'available',
  `recommended_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `suggested_source` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `report_type` varchar(50) NOT NULL,
  `date_generated` timestamp NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('student','librarian') NOT NULL DEFAULT 'student',
  `full_name` varchar(100) NOT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `role`, `full_name`, `lrn`, `year_level`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'studentuser', '$2y$10$KyV18joR7Gqf6btVpvgyS.lz5xTNCI1vzOqJlrm63TaKvMIcyYi3O', 'student@example.com', 'student', 'Kyle Bryan P. Bumatay', '123456789012', '3rd Year', '2025-10-19 16:02:49', '2025-10-20 16:08:53', 1),
(3, 'Rielangeleu', '$2y$10$OVSQfoufahiEy1hGg2i6pu253O5va200mRm9KCXuAgKLTnQNiZCxa', 'emem_0420@yahoo.com', 'student', 'Angela Mariel Torres', '202113637111', '3rd year', '2025-10-19 16:18:25', '2025-10-21 18:51:17', 1),
(4, 'librarian1', '$2y$10$7pmxF0ah43HHdLK106pknOYfVBjKqrvq3yGl8pCZHY6ClcQRBpXj.', 'admin@kyoc.org', 'librarian', 'Angela Mariel Torres', NULL, NULL, '2025-10-19 16:20:46', '2025-10-19 16:20:46', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action_type`),
  ADD KEY `idx_audit_timestamp` (`timestamp`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`),
  ADD KEY `idx_authors_name` (`author_name`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `idx_books_title` (`title`),
  ADD KEY `idx_books_author` (`author_id`),
  ADD KEY `idx_books_genre` (`genre_id`),
  ADD KEY `idx_books_available` (`quantity_available`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `genre_name` (`genre_name`),
  ADD KEY `idx_genres_name` (`genre_name`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `idx_loans_student` (`user_id`),
  ADD KEY `idx_loans_book` (`book_id`),
  ADD KEY `idx_loans_status` (`status`),
  ADD KEY `idx_loans_due_date` (`due_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `idx_notif_student` (`user_id`),
  ADD KEY `idx_notif_type` (`type`);

--
-- Indexes for table `partner_libraries`
--
ALTER TABLE `partner_libraries`
  ADD PRIMARY KEY (`library_id`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `idx_penalties_loan` (`loan_id`),
  ADD KEY `idx_penalties_status` (`status`),
  ADD KEY `idx_penalties_user` (`user_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`rec_id`),
  ADD KEY `idx_rec_student` (`user_id`),
  ADD KEY `idx_rec_book` (`book_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_reports_librarian` (`user_id`),
  ADD KEY `idx_reports_type` (`report_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `partner_libraries`
--
ALTER TABLE `partner_libraries`
  MODIFY `library_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `rec_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `fk_penalties_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
