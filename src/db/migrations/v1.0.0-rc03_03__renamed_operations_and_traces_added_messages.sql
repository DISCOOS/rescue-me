-- MySQL Workbench Synchronization
-- Generated: 2017-09-07 20:32
-- Model: RescueMe
-- Version: 1.0
-- Project: RescueMe
-- Author: Kenneth Gulbrandsøy
-- RescueMe MySQL Model

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER TABLE `${schema}`.`missing`
DROP FOREIGN KEY `fk_missing_operations`;

ALTER TABLE `${schema}`.`operations` 
DROP FOREIGN KEY `fk_operations_users`;

ALTER TABLE `${schema}`.`positions` 
DROP FOREIGN KEY `fk_positions_missing`;

CREATE TABLE IF NOT EXISTS `${schema}`.`versions` (
  `version_id` INT(6) NOT NULL AUTO_INCREMENT,
  `version_name` VARCHAR(255) NOT NULL,
  `version_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `${schema}`.`missing` 
CHANGE COLUMN `op_id` `trace_id` INT(11) NOT NULL FIRST,
CHANGE COLUMN `sms_delivery` `sms_delivered` TIMESTAMP NULL DEFAULT NULL,
CHANGE COLUMN `sms_mb_sent` `sms_mb_sent` ENUM('false', 'true') NOT NULL DEFAULT 'false' AFTER `sms_sent`,
CHANGE COLUMN `sms2_sent` `sms2_sent` ENUM('false', 'true') NOT NULL DEFAULT 'false' AFTER `sms_mb_sent`,
CHANGE COLUMN `missing_id` `mobile_id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `missing_name` `mobile_name` CHAR(255) NOT NULL ,
CHANGE COLUMN `missing_mobile_country` `mobile_country` CHAR(4) NOT NULL ,
CHANGE COLUMN `missing_mobile` `mobile_number` VARCHAR(25) NOT NULL ,
CHANGE COLUMN `missing_locale` `mobile_locale` VARCHAR(10) NOT NULL ,
CHANGE COLUMN `missing_hash` `mobile_hash` VARCHAR(45) NULL DEFAULT NULL ,
CHANGE COLUMN `missing_answered` `mobile_responded` TIMESTAMP NULL DEFAULT NULL AFTER `mobile_alerted`,
CHANGE COLUMN `missing_reported` `mobile_alerted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
ADD INDEX `fk_mobiles_traces_idx` (`trace_id` ASC), RENAME TO `${schema}`.`mobiles` ;

ALTER TABLE `${schema}`.`operations` 
CHANGE COLUMN `op_id` `trace_id` INT(11) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `user_id` `user_id` INT(11) NOT NULL ,
CHANGE COLUMN `op_type` `trace_type` ENUM('trace', 'test', 'exercise') NOT NULL ,
CHANGE COLUMN `op_name` `trace_name` VARCHAR(255) NOT NULL ,
CHANGE COLUMN `op_ref` `trace_ref` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `op_opened` `trace_opened` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
CHANGE COLUMN `op_closed` `trace_closed` TIMESTAMP NULL DEFAULT NULL ,
CHANGE COLUMN `op_comments` `trace_comments` TEXT NOT NULL ,
CHANGE COLUMN `alert_mobile_country` `trace_alert_country` CHAR(4) NOT NULL,
CHANGE COLUMN `alert_mobile` `trace_alert_number` VARCHAR(25) NOT NULL,

ADD INDEX `fk_traces_users_idx` (`user_id` ASC), RENAME TO  `${schema}`.`traces` ;

ALTER TABLE `${schema}`.`positions`
CHANGE COLUMN `missing_id` `mobile_id` INT(11) NOT NULL FIRST,
CHANGE COLUMN `pos_id` `pos_id` INT(11) NOT NULL AUTO_INCREMENT ;


CREATE TABLE IF NOT EXISTS `${schema}`.`messages` (
  `mobile_id` INT(11) NOT NULL,
  `message_id` INT(11) NOT NULL AUTO_INCREMENT,
  `message_type` ENUM('sms') NOT NULL,
  `message_locale` VARCHAR(45) NOT NULL,
  `message_text` TEXT NULL DEFAULT NULL,
  `message_provider` VARCHAR(255) NULL DEFAULT NULL,
  `message_provider_ref` VARCHAR(255) NULL DEFAULT NULL,
  `message_provider_status` VARCHAR(255) NULL DEFAULT NULL,
  `message_provider_error` BLOB NULL DEFAULT NULL,
  `message_sent` TIMESTAMP NULL DEFAULT NULL,
  `message_delivered` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  INDEX `fk_messages_mobiles_idx` (`mobile_id` ASC),
  CONSTRAINT `fk_messages_mobiles`
    FOREIGN KEY (`mobile_id`)
    REFERENCES `${schema}`.`mobiles` (`mobile_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `${schema}`.`mobiles` 
ADD CONSTRAINT `fk_mobiles_traces`
  FOREIGN KEY (`trace_id`)
  REFERENCES `${schema}`.`traces` (`trace_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `${schema}`.`traces` 
ADD CONSTRAINT `fk_traces_users`
  FOREIGN KEY (`user_id`)
  REFERENCES `${schema}`.`users` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `${schema}`.`positions` 
ADD CONSTRAINT `fk_positions_mobiles`
  FOREIGN KEY (`mobile_id`)
  REFERENCES `${schema}`.`mobiles` (`mobile_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

-- Copy all existing sms from mobiles to messages table
INSERT INTO `${schema}`.`messages` (`mobile_id`, `message_id`, `message_locale`, `message_text`, `message_provider`, `message_provider_ref`, `message_provider_error`, `message_sent`, `message_delivered`)
  SELECT `mobile_id` m1, `mobile_id` m2, `mobile_locale`, `sms_text`, `sms_provider`, `sms_provider_ref`, `sms_error`, `sms_sent`, `sms_delivered` FROM `${schema}`.`mobiles`
ON DUPLICATE KEY UPDATE 
  `mobile_id` = VALUES(`mobile_id`),
  `message_locale` = VALUES(`message_locale`),
  `message_text` = VALUES(`message_text`),
  `message_provider` = VALUES(`message_provider`),
  `message_provider_ref` = VALUES(`message_provider_ref`),
  `message_provider_error` = VALUES(`message_provider_error`),
  `message_sent` = VALUES(`message_sent`),
  `message_delivered` = VALUES(`message_delivered`);

-- Drop unused columns
ALTER TABLE `${schema}`.`mobiles` 
DROP COLUMN `sms_error`,
DROP COLUMN `sms_provider_ref`,
DROP COLUMN `sms_provider`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
