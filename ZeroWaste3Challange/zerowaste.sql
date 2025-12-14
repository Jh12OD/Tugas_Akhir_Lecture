-- =========================================
-- ZeroWaste Database Schema for MySQL
-- Compatible with XAMPP/phpMyAdmin
-- =========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `zerowaste` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `zerowaste`;

-- =========================================
-- Table structure for users
-- =========================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `role` varchar(20) DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `is_forum_blocked` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for waste_exchanges
-- =========================================
CREATE TABLE `waste_exchanges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(10) DEFAULT 'kg',
  `photo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `points_earned` int(11) DEFAULT 0,
  `reject_reason` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `waste_exchanges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `waste_exchanges_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for daily_waste_logs
-- =========================================
CREATE TABLE `daily_waste_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT 'kg',
  `notes` text DEFAULT NULL,
  `log_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `daily_waste_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for forum_posts
-- =========================================
CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for forum_comments
-- =========================================
CREATE TABLE `forum_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for events
-- =========================================
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for inbox
-- =========================================
CREATE TABLE `inbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(20) DEFAULT 'notification',
  `is_read` tinyint(1) DEFAULT 0,
  `is_global` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `inbox_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Table structure for recycling_locations
-- =========================================
CREATE TABLE `recycling_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `maps_link` text DEFAULT NULL,
  `operating_hours` varchar(100) DEFAULT NULL,
  `waste_types` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- INSERT DATA: Users
-- Password for all users: password123
-- untuk admin: ZeroWaste2026!
-- =========================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `address`, `points`, `role`) VALUES
('admin', 'admin@zerowaste.id', '$2y$10$EyBGNV.FqV4resgmqE9BZeqlBm3LlujxvDsJokkCiNhgNpYKF3noq', 'Administrator', '081234567890', 'Jakarta', 0, 'admin'),
('budi', 'budi@email.com', '$2y$10$EyBGNV.FqV4resgmqE9BZeqlBm3LlujxvDsJokkCiNhgNpYKF3noq', 'Budi Santoso', '081234567891', 'Jl. Merdeka No. 10, Jakarta', 250, 'user'),
('siti', 'siti@email.com', '$2y$10$EyBGNV.FqV4resgmqE9BZeqlBm3LlujxvDsJokkCiNhgNpYKF3noq', 'Siti Aminah', '081234567892', 'Jl. Sudirman No. 20, Bandung', 150, 'user'),
('andi', 'andi@email.com', '$2y$10$EyBGNV.FqV4resgmqE9BZeqlBm3LlujxvDsJokkCiNhgNpYKF3noq', 'Andi Wijaya', '081234567893', 'Jl. Gatot Subroto No. 30, Surabaya', 520, 'user'),
('dewi', 'dewi@email.com', '$2y$10$EyBGNV.FqV4resgmqE9BZeqlBm3LlujxvDsJokkCiNhgNpYKF3noq', 'Dewi Lestari', '081234567894', 'Jl. Ahmad Yani No. 40, Yogyakarta', 75, 'user');

-- =========================================
-- INSERT DATA: Waste Exchanges
-- =========================================
INSERT INTO `waste_exchanges` (`user_id`, `category`, `quantity`, `unit`, `status`, `points_earned`, `created_at`) VALUES
(2, 'plastik', 5.5, 'kg', 'approved', 55, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 'kertas', 3.0, 'kg', 'approved', 30, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 'logam', 2.0, 'kg', 'approved', 20, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 'plastik', 4.0, 'kg', 'approved', 40, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 'kaca', 2.5, 'kg', 'approved', 25, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 'plastik', 10.0, 'kg', 'approved', 100, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 'kertas', 8.0, 'kg', 'approved', 80, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'logam', 5.0, 'kg', 'approved', 50, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(5, 'plastik', 2.0, 'kg', 'pending', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'elektronik', 1.5, 'kg', 'pending', 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- =========================================
-- INSERT DATA: Daily Waste Logs
-- =========================================
INSERT INTO `daily_waste_logs` (`user_id`, `category`, `quantity`, `unit`, `log_date`) VALUES
(2, 'plastik', 0.5, 'kg', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(2, 'kertas', 0.3, 'kg', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(2, 'organik', 1.0, 'kg', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(2, 'plastik', 0.4, 'kg', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
(3, 'plastik', 0.6, 'kg', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(3, 'kaca', 0.2, 'kg', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(4, 'plastik', 1.0, 'kg', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(4, 'kertas', 0.8, 'kg', DATE_SUB(CURDATE(), INTERVAL 2 DAY));

-- =========================================
-- INSERT DATA: Forum Posts
-- =========================================
INSERT INTO `forum_posts` (`user_id`, `title`, `content`, `created_at`) VALUES
(2, 'Tips Memilah Sampah di Rumah', 'Berikut tips memilah sampah yang baik dan benar:\n\n1. Pisahkan sampah organik dan anorganik\n2. Cuci bersih sampah plastik sebelum dikumpulkan\n3. Lipat kardus agar hemat tempat\n4. Simpan sampah elektronik terpisah\n\nSemoga bermanfaat!', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'Pengalaman Pertama Menukar Sampah', 'Hari ini saya pertama kali menukar sampah plastik dan mendapat 40 poin! Prosesnya mudah dan cepat. Terima kasih ZeroWaste!', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 'Cara Mengurangi Sampah Plastik', 'Beberapa tips yang bisa dilakukan:\n\n- Membawa tas belanja sendiri\n- Menggunakan botol minum isi ulang\n- Menghindari sedotan plastik\n- Membawa wadah makanan sendiri\n\nMari bersama kurangi sampah plastik!', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- =========================================
-- INSERT DATA: Forum Comments
-- =========================================
INSERT INTO `forum_comments` (`post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 3, 'Terima kasih tipsnya! Sangat bermanfaat untuk pemula seperti saya.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 4, 'Saya sudah menerapkan tips ini di rumah. Hasilnya bagus!', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 2, 'Selamat! Terus semangat mengumpulkan sampah untuk ditukar.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 5, 'Setuju! Saya juga sudah berhenti pakai sedotan plastik.', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- =========================================
-- INSERT DATA: Events
-- =========================================
INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `location`, `is_active`) VALUES
('Aksi Bersih Pantai Jakarta', 'Mari bergabung dalam aksi bersih pantai untuk menjaga kebersihan laut kita. Peserta akan mendapatkan 50 poin bonus!', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '07:00:00', 'Pantai Ancol, Jakarta Utara', 1),
('Workshop Daur Ulang Kreatif', 'Pelajari cara mengubah sampah plastik menjadi kerajinan bernilai. Cocok untuk semua usia. Gratis!', DATE_ADD(CURDATE(), INTERVAL 21 DAY), '09:00:00', 'Balai Kota Bandung', 1),
('Kompetisi Eco-Warrior', 'Jadilah Eco-Warrior dengan mengumpulkan sampah terbanyak! Hadiah menarik menanti pemenang.', DATE_ADD(CURDATE(), INTERVAL 30 DAY), '08:00:00', 'Taman Kota Surabaya', 1);

-- =========================================
-- INSERT DATA: Inbox
-- =========================================
INSERT INTO `inbox` (`user_id`, `title`, `message`, `type`, `is_global`) VALUES
(NULL, 'Selamat Datang di ZeroWaste!', 'Terima kasih telah bergabung dengan ZeroWaste. Mari bersama-sama menjaga lingkungan dengan menukarkan sampah menjadi poin!', 'notification', 1),
(2, 'Penukaran Sampah Disetujui', 'Penukaran sampah plastik 5.5 kg Anda telah disetujui. 55 poin telah ditambahkan ke akun Anda.', 'notification', 0),
(3, 'Penukaran Sampah Disetujui', 'Penukaran sampah plastik 4.0 kg Anda telah disetujui. 40 poin telah ditambahkan ke akun Anda.', 'notification', 0);

-- =========================================
-- INSERT DATA: Recycling Locations
-- =========================================
INSERT INTO `recycling_locations` (`name`, `address`, `city`, `phone`, `maps_link`, `operating_hours`, `waste_types`) VALUES
('Drop Point Menteng', 'Jl. HOS Cokroaminoto No. 15, Menteng', 'Jakarta', '021-1234567', 'https://maps.google.com', 'Senin - Sabtu, 08:00 - 17:00', 'Plastik, Kertas, Logam, Kaca'),
('Eco Center Bandung', 'Jl. Asia Afrika No. 50, Braga', 'Bandung', '022-7654321', 'https://maps.google.com', 'Senin - Jumat, 09:00 - 16:00', 'Plastik, Kertas, Elektronik'),
('Green Hub Surabaya', 'Jl. Tunjungan No. 88, Genteng', 'Surabaya', '031-9876543', 'https://maps.google.com', 'Setiap Hari, 07:00 - 18:00', 'Plastik, Kertas, Logam, Kaca, Organik'),
('Recycle Station Yogyakarta', 'Jl. Malioboro No. 100, Gedongtengen', 'Yogyakarta', '0274-567890', 'https://maps.google.com', 'Senin - Sabtu, 08:00 - 15:00', 'Plastik, Kertas, Kaca');

COMMIT;
