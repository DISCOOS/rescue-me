-- --------------------------------------------------------

-- 
-- Structure for table `missing`
-- 

CREATE TABLE IF NOT EXISTS `missing` (
  `missing_id` int(5) NOT NULL AUTO_INCREMENT,
  `user_id` int(3) NOT NULL,
  `missed_by_name` char(255) NOT NULL,
  `missed_by_email` char(255) NOT NULL,
  `missed_by_mobile` int(8) NOT NULL,
  `missing_name` char(255) NOT NULL,
  `missing_mobile` int(8) NOT NULL,
  `status` enum('Open','Sent','Recieved','Closed') NOT NULL,
  `sms_sent` datetime DEFAULT NULL,
  `sms_delivered` enum('false','true') NOT NULL,
  `sms_provider_ref` varchar(255) NOT NULL,
  `sms_error` varchar(255) NOT NULL,
  `missing_reported` datetime NOT NULL,
  `timestamp_sms_sent` int(12) NOT NULL,
  `timestamp_pos_recieved` int(12) NOT NULL,
  `sms2_sent` enum('false','true') NOT NULL DEFAULT 'false',
  `sms_mb_sent` enum('false','true') NOT NULL,
  PRIMARY KEY (`missing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `modules`
-- 

CREATE TABLE IF NOT EXISTS `modules` (
  `module_id` int(4) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `impl` varchar(50) NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `positions`
-- 

CREATE TABLE IF NOT EXISTS `positions` (
  `pos_id` int(10) NOT NULL AUTO_INCREMENT,
  `missing_id` int(5) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `acc` int(5) NOT NULL,
  `alt` int(4) NOT NULL,
  `timestamp` int(12) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`pos_id`),
  KEY `missing_id` (`missing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `properties`
-- 

CREATE TABLE IF NOT EXISTS `properties` (
  `property_id` int(4) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `users`
-- 

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` char(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `mobile` int(8) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

