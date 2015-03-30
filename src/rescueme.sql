-- --------------------------------------------------------

-- 
-- Structure for table `logs`
-- 

CREATE TABLE IF NOT EXISTS `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `context` longtext,
  `user_id` int(11) NOT NULL,
  `client_ip` varchar(40) NOT NULL DEFAULT 'UNKNOWN',
  PRIMARY KEY (`log_id`),
  KEY `level` (`level`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `messages`
-- 

CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` enum('sms') NOT NULL,
  `message_from` tinytext NOT NULL,
  `message_to` tinytext NOT NULL,
  `message_subject` tinytext NOT NULL,
  `message_data` text NOT NULL,
  `message_state` tinytext NOT NULL,
  `message_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message_provider` tinytext NOT NULL,
  `message_reference` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `missing`
-- 

CREATE TABLE IF NOT EXISTS `missing` (
  `missing_id` int(6) NOT NULL AUTO_INCREMENT,
  `op_id` int(6) NOT NULL,
  `missing_name` char(255) NOT NULL,
  `missing_mobile_country` char(4) NOT NULL,
  `missing_mobile` varchar(25) NOT NULL,
  `missing_locale` varchar(10) NOT NULL,
  `sms_sent` timestamp NULL DEFAULT NULL,
  `sms_delivery` timestamp NULL DEFAULT NULL,
  `sms_provider` varchar(255) DEFAULT NULL,
  `sms_provider_ref` varchar(255) DEFAULT NULL,
  `sms_error` varchar(255) DEFAULT NULL,
  `missing_answered` timestamp NULL DEFAULT NULL,
  `missing_accept_id` int(11) NOT NULL,
  `missing_reported` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sms2_sent` enum('false','true') NOT NULL DEFAULT 'false',
  `sms_mb_sent` enum('false','true') NOT NULL DEFAULT 'false',
  `sms_text` text NOT NULL,
  PRIMARY KEY (`missing_id`),
  KEY `oper_id` (`op_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `modules`
-- 

CREATE TABLE IF NOT EXISTS `modules` (
  `module_id` int(4) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL,
  `impl` varchar(50) NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `operations`
-- 

CREATE TABLE IF NOT EXISTS `operations` (
  `op_id` int(6) NOT NULL AUTO_INCREMENT,
  `user_id` int(6) NOT NULL,
  `op_type` enum('trace','test','exercise') NOT NULL,
  `op_name` varchar(255) NOT NULL,
  `alert_mobile_country` char(4) NOT NULL,
  `alert_mobile` varchar(25) NOT NULL,
  `op_ref` varchar(255) DEFAULT NULL,
  `op_opened` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `op_closed` timestamp NULL DEFAULT NULL,
  `op_comments` text NOT NULL,
  PRIMARY KEY (`op_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `permissions`
-- 

CREATE TABLE IF NOT EXISTS `permissions` (
  `role_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `access` varchar(100) NOT NULL,
  `resource` varchar(100) NOT NULL,
  KEY `role_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `positions`
-- 

CREATE TABLE IF NOT EXISTS `positions` (
  `pos_id` int(10) NOT NULL AUTO_INCREMENT,
  `missing_id` int(6) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `acc` int(5) NOT NULL,
  `alt` int(4) NOT NULL,
  `timestamp_device` timestamp NULL DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `requests`
-- 

CREATE TABLE IF NOT EXISTS `requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_type` varchar(10) CHARACTER SET utf8 NOT NULL,
  `request_uri` tinytext CHARACTER SET utf8 NOT NULL,
  `request_query` tinytext CHARACTER SET utf8 NOT NULL,
  `request_data` text CHARACTER SET utf8 NOT NULL,
  `request_headers` text CHARACTER SET utf8 NOT NULL,
  `request_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `roles`
-- 

CREATE TABLE IF NOT EXISTS `roles` (
  `user_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `role_name` (`role_name`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `templates`
-- 

CREATE TABLE IF NOT EXISTS `templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_type` enum('message') NOT NULL DEFAULT 'message',
  `template_name` varchar(50) NOT NULL,
  `template_locale` varchar(10) NOT NULL,
  `template_content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`template_id`),
  KEY `template_name` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure for table `users`
-- 

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` char(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `mobile_country` char(4) NOT NULL,
  `mobile` varchar(25) NOT NULL,
  `state` enum('pending','active','disabled','deleted') DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

