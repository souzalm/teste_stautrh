-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 23, 2020 at 05:59 PM
-- Server version: 5.7.24
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `usersdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbdrink`
--

CREATE TABLE `tbdrink` (
  `iddrink` bigint(20) NOT NULL COMMENT 'Drink ID + Primary Key',
  `drink_counter` bigint(20) NOT NULL COMMENT 'Contador de vezes bebidas',
  `drink_ml` bigint(20) NOT NULL COMMENT 'Drink ml',
  `userid` bigint(20) NOT NULL COMMENT 'Users Id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbsessions`
--

CREATE TABLE `tbsessions` (
  `id` bigint(20) NOT NULL COMMENT 'Session Id',
  `userid` bigint(20) NOT NULL COMMENT 'User Id',
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sessions Table';

-- --------------------------------------------------------

--
-- Table structure for table `tbusers`
--

CREATE TABLE `tbusers` (
  `idusers` bigint(20) NOT NULL COMMENT 'Users Id',
  `email` varchar(255) NOT NULL COMMENT 'Email',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Senha',
  `name` varchar(255) NOT NULL COMMENT 'Nome do Usuário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Usuários';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbdrink`
--
ALTER TABLE `tbdrink`
  ADD PRIMARY KEY (`iddrink`),
  ADD KEY `drinkuserid_fk` (`userid`);

--
-- Indexes for table `tbsessions`
--
ALTER TABLE `tbsessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accesstoken` (`token`),
  ADD KEY `sessionuserid_fk` (`userid`);

--
-- Indexes for table `tbusers`
--
ALTER TABLE `tbusers`
  ADD PRIMARY KEY (`idusers`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbdrink`
--
ALTER TABLE `tbdrink`
  MODIFY `iddrink` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Drink ID + Primary Key', AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `tbsessions`
--
ALTER TABLE `tbsessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Session Id', AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbusers`
--
ALTER TABLE `tbusers`
  MODIFY `idusers` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Users Id', AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbdrink`
--
ALTER TABLE `tbdrink`
  ADD CONSTRAINT `drinkuserid_fk` FOREIGN KEY (`userid`) REFERENCES `tbusers` (`idusers`) ON DELETE CASCADE;

--
-- Constraints for table `tbsessions`
--
ALTER TABLE `tbsessions`
  ADD CONSTRAINT `sessionuserid_fk` FOREIGN KEY (`userid`) REFERENCES `tbusers` (`idusers`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
