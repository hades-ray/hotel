CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_name` varchar(100) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `payment_status` enum('Оплачено','Ожидает','Отменено') DEFAULT 'Ожидает',
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `bookings` (`id`, `guest_name`, `room_id`, `check_in`, `check_out`, `payment_status`, `user_id`, `total_price`) VALUES
(1, 'hadesray', 1, '2026-04-15', '2026-04-20', 'Отменено', 3, 17500.00),
(2, 'ben', 2, '2026-04-14', '2026-04-16', 'Отменено', 5, 14400.00),
(3, 'hadesray', 1, '2026-04-27', '2026-04-29', 'Отменено', 3, 7000.00),
(4, 'hadesray', 2, '2026-04-18', '2026-04-21', 'Отменено', 3, 21600.00),
(5, 'hadesray', 1, '2026-04-21', '2026-04-24', 'Отменено', 3, 10500.00),
(6, 'hadesray', 1, '2026-04-15', '2026-04-18', 'Отменено', 3, 10500.00),
(7, 'hadesray', 1, '2026-04-15', '2026-04-19', 'Отменено', 3, 14000.00),


CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','repair') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `max_guests` int(11) DEFAULT 2,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `rooms` (`id`, `name`, `price`, `status`, `description`, `max_guests`, `image_url`) VALUES
(1, 'Стандарт 101', 2000.00, 'active', 'Уютный номер с видом на тихий дворик, идеально подходит для отдыха.', 2, 'std.jpg'),
(2, 'Люкс 201', 10000.00, 'active', 'Роскошный люкс с панорамными окнами на центр города и большой ванной.', 2, 'luxe.jpg'),
(3, 'Семейный 301', 9500.00, 'active', 'Просторный двухкомнатный номер для всей семьи с кухонным уголком.', 4, 'family.jpg');


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'hadesray', 'hadesray@yandex.ru', '1234', 'user', '2026-04-14 08:07:39'),
(4, 'admin', 'admin@hotel.com', 'Qaz12345', 'admin', '2026-04-14 08:11:48'),
(5, 'ben', '123@asd.ru', '1234', 'user', '2026-04-14 09:21:58');


ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;