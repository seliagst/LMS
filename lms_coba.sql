-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 06:36 AM
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
-- Database: `lms_coba`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `class_code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `teacher_id`, `title`, `class_code`, `description`) VALUES
(1, 1, 'Matematika Diskrit', '3028D7', 'Matematika diskrit adalah cabang matematika yang mempelajari struktur objek terpisah, terhitung, dan tidak kontinu (lawan dari kalkulus/kontinu). Ini mencakup teori himpunan, graf, logika, kombinatorika, dan teori bilangan, yang menjadi landasan utama ilmu komputer, algoritma, dan pemrograman.'),
(2, 1, 'Statistika Terapan', 'A31B7C', 'Statistika adalah cabang ilmu matematika yang mempelajari cara merencanakan, mengumpulkan, menganalisis, menginterpretasikan, dan menyajikan data angka untuk pengambilan keputusan. '),
(3, 1, 'Pemrograman Web', 'A179A4', 'Pemrograman web adalah proses pembuatan dan pengembangan situs atau aplikasi web yang diakses melalui browser, melibatkan teknologi front-end (HTML, CSS, JavaScript) untuk tampilan interaktif dan back-end (PHP, Python, SQL) untuk logika server');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`) VALUES
(1, 2, 1, '2026-02-16 04:15:50'),
(2, 2, 2, '2026-02-16 04:18:08'),
(3, 3, 2, '2026-02-17 04:19:40'),
(4, 4, 2, '2026-02-17 05:29:40'),
(5, 3, 3, '2026-02-17 05:35:18'),
(6, 5, 1, '2026-02-22 05:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `type` enum('material','assignment') DEFAULT 'material',
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `course_id`, `type`, `title`, `content`, `file_path`) VALUES
(1, 2, 'material', 'Pengantar Statistika Terapan', 'Pertemuan 1 - Membahas sejarah, kegunaan, fungsi statistika dalam kehidupan sehari-hari', '1771214612_gambar1.png'),
(2, 2, 'material', 'Jenis-Jenis Statistika', 'Pertemuan 2 - Memabahas tentang jenis statistika, beserta perbedaannya.', '1771214679_JURNAL+NEWWW.pdf'),
(5, 2, 'assignment', 'TUGAS 2 STATISTIKA', 'BUATLAH SISJDGGSFFDCSAVDASDDGFDGE', '1771303793_Instrumen Pra Penerapan Gamifikasi (Responses).xlsx'),
(6, 3, 'material', 'Pengantar Pemrograman Web', 'Pertemuan 1 - Apa sih pemrograman web itu? Merupakan salah satu cabang ilmu pemrograman yang dikhususkan untuk membuat dan mendesain sebuah website.', '1771306339_KKA 2.pptx'),
(7, 3, 'assignment', 'TUGAS 1 - PW', 'Analisis tentang kebutuhan Web shoping, buat dalam bentuk ppt dengan kelompok mas=ksimal 7 orang.\r\n\r\ndl: 1 minggu', '');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `file_submission` varchar(255) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `material_id`, `student_id`, `file_submission`, `grade`, `submitted_at`) VALUES
(3, 5, 3, 'SUB_1771303933_91464a7760e11b8acbff6485c010bc80.jpg', 90, '2026-02-17 04:52:13'),
(4, 7, 3, 'SUB_1771306554_education-14-00695.pdf', 85, '2026-02-17 05:35:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'guru1', 'guruhebat', 'teacher'),
(2, 'siswa1', 'siswa123', 'student'),
(3, 'seli', 'seli2345', 'student'),
(4, 'agustina', 'agustus', 'student'),
(5, 'caca', 'caca123', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
