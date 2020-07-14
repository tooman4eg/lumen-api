-- phpMyAdmin SQL Dump
-- version 4.0.10.6
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1:3310
-- Время создания: Июл 14 2020 г., 19:10
-- Версия сервера: 5.6.43-log
-- Версия PHP: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `email_check`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Email`
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