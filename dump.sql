-- phpMyAdmin SQL Dump
-- version 4.0.10.6
-- http://www.phpmyadmin.net
--
-- ����: 127.0.0.1:3310
-- ����� ��������: ��� 14 2020 �., 19:10
-- ������ �������: 5.6.43-log
-- ������ PHP: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- ���� ������: `email_check`
--

-- --------------------------------------------------------

--
-- ��������� ������� `Email`
--

CREATE TABLE IF NOT EXISTS `Email` (
 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `code` varchar(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_valid` smallint(2) NOT NULL,
  `updated_at` datetime NOT NULL,
   `attempts` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;