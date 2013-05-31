-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Vert: localhost
-- Generert den: 30. Mai, 2013 22:05 PM
-- Tjenerversjon: 5.0.67
-- PHP-Versjon: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ntrkh_savnet`
--

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `missing`
--

CREATE TABLE IF NOT EXISTS `missing` (
  `missing_id` int(5) NOT NULL auto_increment,
  `user_id` int(3) NOT NULL,
  `missed_by_name` char(255) NOT NULL,
  `missed_by_email` char(255) NOT NULL,
  `missed_by_mobile` int(8) NOT NULL,
  `missing_name` char(255) NOT NULL,
  `missing_mobile` int(8) NOT NULL,
  `status` enum('Open','Sent','Recieved','Closed') NOT NULL,
  `sms_sent` datetime default NULL,
  `missing_reported` datetime NOT NULL,
  `timestamp_sms_sent` int(12) NOT NULL,
  `timestamp_pos_recieved` int(12) NOT NULL,
  `sms2_sent` enum('false','true') NOT NULL default 'false',
  PRIMARY KEY  (`missing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `positions`
--

CREATE TABLE IF NOT EXISTS `positions` (
  `pos_id` int(10) NOT NULL auto_increment,
  `missing_id` int(5) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `acc` int(5) NOT NULL,
  `alt` int(4) NOT NULL,
  `timestamp` int(12) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY  (`pos_id`),
  KEY `missing_id` (`missing_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=185 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `password` char(128) NOT NULL default '',
  `email` varchar(255) NOT NULL,
  `mobile` int(8) NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
