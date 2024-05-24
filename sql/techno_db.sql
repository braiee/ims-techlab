-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2024 at 03:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techno_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ticketing_table`
--

CREATE TABLE `ticketing_table` (
  `id` int(11) NOT NULL,
  `ticket_id` varchar(20) NOT NULL,
  `task_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `due_date` datetime NOT NULL,
  `status` varchar(100) NOT NULL,
  `assigned_to` varchar(100) NOT NULL,
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticketing_table`
--

INSERT INTO `ticketing_table` (`id`, `ticket_id`, `task_name`, `description`, `due_date`, `status`, `assigned_to`, `date_created`) VALUES
(478, 'TCKT002', 'VR Goggles', 'qweqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 01:51:33'),
(479, 'TCKT003', 'VR Goggles', 'qweqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:10:30'),
(480, 'TCKT004', 'Drone', 'ewqeqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:10:35'),
(481, 'TCKT005', 'VR Goggles', 'wqeqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:10:42'),
(482, 'TCKT006', 'VR Goggles', 'wqewqeqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:10:49'),
(483, 'TCKT007', 'VR Goggles', 'qweqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:15:07'),
(484, 'TCKT008', 'VR Goggles', 'qweqwe', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:16:44'),
(486, 'TCKT010', 'VR Goggles', 'qwqeqw', '0000-00-00 00:00:00', 'Borrowed', 'Jermaine', '2024-05-22 02:16:56');

--
-- Triggers `ticketing_table`
--
DELIMITER $$
CREATE TRIGGER `before_ticket_insert` BEFORE INSERT ON `ticketing_table` FOR EACH ROW BEGIN
    DECLARE last_id INT;
    DECLARE custom_id_prefix VARCHAR(5);
    SET custom_id_prefix = 'TCKT';
    
    SELECT MAX(SUBSTRING_INDEX(ticket_id, custom_id_prefix, -1)) INTO last_id FROM ticketing_table WHERE ticket_id LIKE CONCAT(custom_id_prefix, '%');
    
    IF last_id IS NULL THEN
        SET last_id = 0;
    END IF;
    
    SET NEW.ticket_id = CONCAT(custom_id_prefix, LPAD(last_id + 1, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `identity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `username`, `password`, `identity`) VALUES
(1, '1', 'admin', 'admin', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ticketing_table`
--
ALTER TABLE `ticketing_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ticketing_table`
--
ALTER TABLE `ticketing_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=488;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
