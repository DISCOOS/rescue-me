-- MySQL Workbench Synchronization
-- Generated: 2017-09-08 23:00
-- Model: RescueMe
-- Version: 1.0
-- Project: RescueMe
-- Author: Kenneth Gulbrandsøy
-- RescueMe MySQL Model

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE TABLE IF NOT EXISTS `${schema}`.`requests` (
  `request_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_ua` TEXT NOT NULL,
  `request_client_ip` VARCHAR(40) NULL DEFAULT NULL,
  `request_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_type` ENUM('response', 'position') NOT NULL,
  `foreign_id` INT(11) NOT NULL,
  `mobile_id` INT(11) NOT NULL,
  PRIMARY KEY (`request_id`),
  INDEX `fk_requests_mobiles_idx` (`mobile_id` ASC),
  CONSTRAINT `fk_requests_mobiles`
    FOREIGN KEY (`mobile_id`)
    REFERENCES `${schema}`.`mobiles` (`mobile_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `${schema}`.`devices` (
  `device_id` INT(11) NOT NULL,
  `device_type` VARCHAR(45) NULL DEFAULT NULL,
  `device_os_name` VARCHAR(45) NULL DEFAULT NULL,
  `device_os_version` VARCHAR(45) NULL DEFAULT NULL,
  `device_browser_name` VARCHAR(45) NULL DEFAULT NULL,
  `device_browser_version` VARCHAR(45) NULL DEFAULT NULL,
  `device_is_phone` ENUM('true', 'false') NULL DEFAULT NULL,
  `device_is_smartphone` ENUM('true', 'false') NULL DEFAULT NULL,
  `device_supports_xhr2` ENUM('true', 'false') NULL DEFAULT NULL,
  `device_supports_geolocation` ENUM('true', 'false') NULL DEFAULT NULL,
  `device_lookup_provider` VARCHAR(45) NULL DEFAULT NULL,
  `device_lookup_provider_ref` VARCHAR(45) NULL DEFAULT NULL,
  `request_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  INDEX `fk_devices_requests_idx` (`request_id` ASC),
  CONSTRAINT `fk_devices_requests`
    FOREIGN KEY (`request_id`)
    REFERENCES `${schema}`.`requests` (`request_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- Copy all existing requests from position to requests table
INSERT INTO `${schema}`.`requests` (`mobile_id`, `request_id`, `request_type`, `foreign_id`, `request_ua`, `request_timestamp`)
  SELECT `mobile_id` m, `mobile_id` r, 'position', `pos_id`, `user_agent`, `timestamp` FROM `${schema}`.`positions`
ON DUPLICATE KEY UPDATE 
  `request_ua` = VALUES(`request_ua`),
  `request_timestamp` = VALUES(`request_timestamp`);


-- Drop unused columns
ALTER TABLE `${schema}`.`positions` 
DROP COLUMN `user_agent`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
