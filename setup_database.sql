-- Создание базы данных для LEBROOM Poker Club
-- Импортируйте этот файл через phpMyAdmin в панели Beget

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Таблица пользователей
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` bigint(20) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `total_tournaments` int(11) DEFAULT 0,
  `total_wins` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegram_id` (`telegram_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица турниров
CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `tournament_date` date NOT NULL,
  `tournament_time` time NOT NULL,
  `total_seats` int(11) NOT NULL,
  `buy_in` decimal(10,2) NOT NULL,
  `prize_pool` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_date` (`tournament_date`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица записей на турниры
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `registered_at` datetime NOT NULL,
  `status` enum('active','cancelled','completed') DEFAULT 'active',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`user_id`,`tournament_id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_registrations_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`telegram_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица истории рейтинга
CREATE TABLE `rating_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(200) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_tournament` (`tournament_id`),
  CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`telegram_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Тестовые данные
INSERT INTO `tournaments` (`title`, `description`, `tournament_date`, `tournament_time`, `total_seats`, `buy_in`, `prize_pool`, `is_active`) VALUES
('LEBROOM HIGH ROLLER', 'Еженедельный турнир с гарантированным призовым фондом', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', 100, 5000.00, 500000.00, 1),
('FRIDAY NIGHT POKER', 'Турнир в пятницу вечером', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '20:00:00', 80, 3000.00, 240000.00, 1),
('WEEKEND SPECIAL', 'Специальный турнир выходного дня', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', 120, 2000.00, 240000.00, 1);

INSERT INTO `users` (`telegram_id`, `username`, `first_name`, `last_name`, `rating`, `total_tournaments`) VALUES
(123456789, 'ivan_petrov', 'Иван', 'Петров', 2540, 15),
(987654321, 'alex_smirnov', 'Алексей', 'Смирнов', 2120, 12),
(555555555, 'maria_ivanova', 'Мария', 'Иванова', 1980, 10);

INSERT INTO `registrations` (`user_id`, `tournament_id`, `registered_at`, `status`) VALUES
(123456789, 1, NOW(), 'active'),
(987654321, 1, NOW(), 'active'),
(555555555, 1, NOW(), 'active');

INSERT INTO `rating_history` (`user_id`, `points`, `reason`, `tournament_id`) VALUES
(123456789, 100, '1 место в турнире', 1),
(123456789, 50, 'Участие в турнире', 2),
(987654321, 80, '2 место в турнире', 1),
(987654321, 30, 'Участие в турнире', 2),
(555555555, 60, '3 место в турнире', 1);

COMMIT;