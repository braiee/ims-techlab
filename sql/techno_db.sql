-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2024 at 05:11 AM
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
-- Table structure for table `office_supplies`
--

CREATE TABLE `office_supplies` (
  `id` int(11) NOT NULL,
  `Item_Code` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `QTY` int(11) NOT NULL,
  `EMEI` int(11) NOT NULL,
  `S/N_BuildNum` int(11) NOT NULL,
  `REF_RNSS` int(11) NOT NULL,
  `Owner` varchar(100) NOT NULL,
  `Custodian` varchar(100) NOT NULL,
  `RNSS_Accountability` varchar(100) NOT NULL,
  `Remarks` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(466, 'TCKT002', 'Laptop', 'for service', '2024-05-16 13:30:00', 'Borrowed', 'Jermaine', '2024-05-16 07:26:30');

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
-- Indexes for table `office_supplies`
--
ALTER TABLE `office_supplies`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `office_supplies`
--
ALTER TABLE `office_supplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticketing_table`
--
ALTER TABLE `ticketing_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
