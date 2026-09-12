-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 17, 2026 at 09:32 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skymay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_comments_event` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `event_id`, `user_id`, `comment_text`, `created_at`) VALUES
(6, 22, 15, 'Good chance to talk with real companies.', '2026-05-14 11:05:26'),
(5, 21, 15, 'Very interesting talk. I learned new things about AI.', '2026-05-14 11:05:06'),
(7, 20, 15, 'Very challenging but fun experience.', '2026-05-14 11:06:16'),
(8, 25, 15, 'I learned how to create a simple app.', '2026-05-14 11:06:32'),
(9, 26, 15, 'Very useful for preparing internship applications.', '2026-05-14 11:06:44'),
(10, 27, 15, 'Very fun and relaxing after class.', '2026-05-14 11:06:58'),
(11, 29, 15, 'Very creative and enjoyable competition.', '2026-05-14 11:07:15'),
(12, 28, 15, 'Helpful experience from senior students.', '2026-05-14 11:07:27'),
(13, 21, 19, 'Easy to follow and very beginner friendly.', '2026-05-14 11:10:40'),
(14, 22, 19, 'I learned what skills are needed for IT jobs.', '2026-05-14 11:11:02'),
(15, 20, 19, 'I improved my teamwork and coding skills.', '2026-05-14 11:11:16'),
(16, 25, 19, 'The steps were clear and easy to follow.', '2026-05-14 11:11:27'),
(17, 26, 19, 'I learned how to improve my resume format.', '2026-05-14 11:11:41'),
(18, 27, 19, 'I enjoyed playing games with friends.', '2026-05-14 11:11:53'),
(19, 29, 19, 'I improved my design and creativity skills.', '2026-05-14 11:12:04'),
(20, 21, 21, 'The workshop helped me understand UI/UX better.', '2026-05-14 11:13:46'),
(21, 22, 21, 'Some booths were very helpful for career planning.', '2026-05-14 11:13:59'),
(22, 20, 21, 'Not enough time, but still a great event.', '2026-05-14 11:14:28'),
(23, 25, 21, 'Good practice session for beginners.', '2026-05-14 11:14:38'),
(24, 26, 21, 'The interview tips were very practical.', '2026-05-14 11:14:49'),
(25, 27, 21, 'Good event to reduce stress.', '2026-05-14 11:15:08'),
(26, 29, 21, 'Some competition was tough but fun.', '2026-05-14 11:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_name` varchar(120) NOT NULL,
  `sender_email` varchar(160) NOT NULL,
  `sender_role` varchar(30) NOT NULL,
  `message_body` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unread',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `idx_contact_messages_status` (`status`),
  KEY `idx_contact_messages_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `sender_name`, `sender_email`, `sender_role`, `message_body`, `status`, `created_at`, `read_at`) VALUES
(26, 'ST004', 'st004@student.com', 'student', 'The registration page has an error.', 'read', '2026-05-12 11:09:22', '2026-05-12 19:09:46'),
(27, 'ST005', 'st005@admin.com', 'admin', 'System maintenance will be done tonight.', 'read', '2026-05-12 11:09:22', '2026-05-12 19:09:48'),
(25, 'ST003', 'st003@organizer.com', 'organizer', 'Please approve my event proposal.', 'unread', '2026-05-12 11:09:22', NULL),
(24, 'ST002', 'st002@student.com', 'student', 'How can I cancel my registration?', 'read', '2026-05-12 11:09:22', '2026-05-12 19:09:43'),
(23, 'ST001', 'st001@student.com', 'student', 'I want more information about the AI Workshop.', 'unread', '2026-05-12 11:09:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `image_path` varchar(255) DEFAULT NULL,
  `event_link` varchar(255) DEFAULT NULL,
  `event_status` varchar(20) NOT NULL DEFAULT 'pending',
  `event_date` date NOT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `image_path`, `event_link`, `event_status`, `event_date`, `venue`, `category`, `created_by`, `created_at`) VALUES
(23, 'Final Exam Study Group', 'Students gather to study together and prepare for exams. They can ask questions, share notes, and revise important topics. This session is helpful for students who want quick revision before exams.', 'uploads/events/event_20260514_095920_5c266661.jpg', 'https://www.khanacademy.org/', 'rejected', '2026-06-22', 'A-04-01', 'other', 17, '2026-05-14 09:59:20'),
(24, 'Coding Competition 2026', 'Students compete by solving coding problems within a time limit. They can improve their programming skills and learn new techniques. Winners will receive prizes and certificates.', 'uploads/events/event_20260514_100301_1889dd5f.jpg', 'https://www.hackerrank.com/', 'approved', '2026-07-10', 'B-04-02', 'competition', 17, '2026-05-14 10:03:01'),
(20, 'Hackathon Challenge 2026', 'Students work in teams to build a simple project using computers. They solve real problems, share ideas, and present their work. Winners will receive prizes and certificates, and beginners are welcome to join.', 'uploads/events/event_20260514_095419_2fac9bae.webp', 'https://apu.edu.my/events/hackathon', 'approved', '2026-12-06', 'Auditorium 1 @ Level 7', 'competition', 17, '2026-05-14 09:54:19'),
(22, 'IT Career Fair 2026', 'Students can meet companies and learn about jobs in the IT field. They can ask questions, understand different job roles, and explore internship opportunities. Students are encouraged to bring their resume.', 'uploads/events/event_20260514_095720_2062c88e.jpg', 'https://www.jobstreet.com.my/', 'approved', '2026-07-05', 'Auditorium 2 @ Level 6', 'career', 17, '2026-05-14 09:57:20'),
(21, 'UI/UX Design Workshop', 'This workshop teaches students how to design apps and websites. They will learn basic design skills and create simple screens during the session. No experience is needed, and students only need to bring a laptop.', 'uploads/events/event_20260514_095605_b4afdbc7.png', 'https://www.interaction-design.org/', 'approved', '2026-06-11', 'B-03-DesignLAB', 'workshop', 17, '2026-05-14 09:56:05'),
(25, 'Mobile App Development Workshop', 'This workshop teaches students how to build simple mobile apps. They will learn basic coding and create a small app during the session. No experience is required.', 'uploads/events/event_20260514_100411_be48b74b.webp', 'https://developer.android.com/', 'approved', '2026-07-12', 'B-02-03-ID Workshop', 'workshop', 17, '2026-05-14 10:04:11'),
(26, 'Resume Writing & Interview Skills', 'Students will learn how to write a good resume and prepare for job interviews. Tips will be given on how to answer questions and present themselves confidently.', 'uploads/events/event_20260514_100513_998cf24d.webp', 'https://www.indeed.com/career-advice', 'approved', '2026-07-15', 'A-05-01', 'career', 17, '2026-05-14 10:05:13'),
(27, 'Game Night Event', 'Students join fun games and activities to relax and enjoy time with friends. It is a good chance to make new friends and reduce stress after studying.', 'uploads/events/event_20260514_100605_541a3fc8.png', 'https://www.meetup.com/', 'approved', '2026-07-18', 'A-04-03', 'other', 17, '2026-05-14 10:06:05'),
(28, 'Internship Sharing Session', 'Students who have completed internships will share their experience and give useful advice. You can learn how to apply, what to expect, and how to prepare for working life. It is helpful for students planning their future career.', 'uploads/events/event_20260514_101215_30516534.webp', 'https://www.linkedin.com/', 'pending', '2026-07-22', 'A-05-02', 'career', 17, '2026-05-14 10:12:15'),
(29, 'Poster Design Competition', 'Students create posters based on a given theme using design tools. This competition helps improve creativity and design skills. The best designs will win prizes and be displayed on campus.', 'uploads/events/event_20260514_101357_950e2a60.webp', 'https://www.canva.com/', 'approved', '2026-07-25', 'B-03-DesignLAB', 'competition', 17, '2026-05-14 10:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

DROP TABLE IF EXISTS `event_registrations`;
CREATE TABLE IF NOT EXISTS `event_registrations` (
  `registration_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('registered','cancelled') DEFAULT 'registered',
  `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `unique_registration` (`event_id`,`user_id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `event_id`, `user_id`, `status`, `registered_at`, `updated_at`) VALUES
(21, 24, 21, 'registered', '2026-05-14 11:12:43', '2026-05-14 11:12:43'),
(20, 25, 21, 'registered', '2026-05-14 11:12:42', '2026-05-14 11:12:42'),
(19, 27, 21, 'registered', '2026-05-14 11:12:41', '2026-05-14 11:12:41'),
(18, 20, 21, 'registered', '2026-05-14 11:12:39', '2026-05-14 11:12:39'),
(17, 25, 19, 'registered', '2026-05-14 11:09:55', '2026-05-14 11:09:55'),
(16, 29, 19, 'registered', '2026-05-14 11:09:54', '2026-05-14 11:09:54'),
(15, 24, 19, 'registered', '2026-05-14 11:09:53', '2026-05-14 11:09:53'),
(12, 21, 15, 'registered', '2026-05-14 10:16:41', '2026-05-14 10:16:41'),
(13, 27, 15, 'registered', '2026-05-14 10:16:42', '2026-05-14 10:16:42'),
(14, 28, 15, 'registered', '2026-05-14 10:16:43', '2026-05-14 10:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`),
  UNIQUE KEY `unique_like` (`event_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_likes_event` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `event_id`, `user_id`, `created_at`) VALUES
(21, 27, 21, '2026-05-14 11:12:57'),
(20, 26, 21, '2026-05-14 11:12:54'),
(19, 21, 21, '2026-05-14 11:12:49'),
(18, 22, 19, '2026-05-14 11:10:57'),
(17, 24, 19, '2026-05-14 11:10:14'),
(16, 26, 19, '2026-05-14 11:10:09'),
(15, 27, 19, '2026-05-14 11:10:04'),
(14, 21, 19, '2026-05-14 11:09:58'),
(10, 21, 15, '2026-05-14 11:01:57'),
(11, 27, 15, '2026-05-14 11:02:02'),
(12, 28, 15, '2026-05-14 11:02:13'),
(13, 22, 15, '2026-05-14 11:07:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Student','Organizer','Admin') DEFAULT 'Student',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Admin User', 'admin@skymay.com', '123456', 'Admin', 'active', '2026-04-14 11:40:56'),
(2, 'John Student', 'john@apu.edu.my', 'ad6a280417a0f533d8b670c61667e1a0', 'Student', 'active', '2026-04-14 11:40:56'),
(3, 'Event Organizer', 'organizer@skymay.com', '138cfe09d04bf461d169ed0ef1607cda', 'Organizer', 'active', '2026-04-14 11:40:56'),
(21, 'ST005', 'st005@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Student', 'active', '2026-05-14 11:01:16'),
(18, 'ST002', 'st002@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Student', 'active', '2026-05-14 11:00:05'),
(19, 'ST003', 'st003@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Student', 'active', '2026-05-14 11:00:22'),
(20, 'ST004', 'st004@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Student', 'active', '2026-05-14 11:00:59'),
(9, 'AM001', 'am001@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Admin', 'active', '2026-05-14 09:39:36'),
(15, 'ST001', 'st001@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Student', 'active', '2026-05-14 09:46:20'),
(17, 'OZ001', 'oz001@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Organizer', 'active', '2026-05-14 09:48:48');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
