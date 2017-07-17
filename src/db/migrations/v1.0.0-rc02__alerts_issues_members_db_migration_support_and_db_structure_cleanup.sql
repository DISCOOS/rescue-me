-- MySQL Workbench Synchronization
-- Generated: 2017-07-17 12:35
-- Model: RescueMe
-- Version: 1.0
-- Project: RescueMe
-- Author: Kenneth Gulbrandsøy
-- RescueMe MySQL Model

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER SCHEMA `${schema}` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `${schema}`.`alerts` (
  `alert_id` INT(11) NOT NULL AUTO_INCREMENT,
  `alert_type` ENUM('info', 'warning', 'error') NOT NULL DEFAULT 'info',
  `alert_subject` TINYTEXT NULL DEFAULT NULL,
  `alert_message` TEXT NOT NULL,
  `alert_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alert_until` TIMESTAMP NULL DEFAULT NULL,
  `alert_closeable` TINYINT(1) NOT NULL DEFAULT '1',
  `user_id` INT(11) NOT NULL,
  `issue_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`alert_id`),
  INDEX `fk_alerts_issues_idx` (`issue_id` ASC),
  INDEX `fk_alerts_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_alerts_issues`
    FOREIGN KEY (`issue_id`)
    REFERENCES `${schema}`.`issues` (`issue_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_alerts_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `${schema}`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `${schema}`.`alerts_closed` (
  `alert_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`, `alert_id`),
  INDEX `fk_alerts_closed_users_idx` (`user_id` ASC),
  INDEX `fk_alerts_closed_alerts_idx` (`alert_id` ASC),
  CONSTRAINT `fk_alerts_closed_alerts`
    FOREIGN KEY (`alert_id`)
    REFERENCES `${schema}`.`alerts` (`alert_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_alerts_closed_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `${schema}`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `${schema}`.`versions` (
  `version_id` INT(6) NOT NULL AUTO_INCREMENT,
  `version_name` VARCHAR(255) NOT NULL,
  `version_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `${schema}`.`groups` (
  `group_id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_name` TINYTEXT NOT NULL,
  `group_owner_user_id` INT(11) NOT NULL,
  PRIMARY KEY (`group_id`),
  INDEX `fk_groups_users_idx` (`group_owner_user_id` ASC),
  CONSTRAINT `fk_groups_users`
    FOREIGN KEY (`group_owner_user_id`)
    REFERENCES `${schema}`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `${schema}`.`issues` (
  `issue_id` INT(11) NOT NULL AUTO_INCREMENT,
  `issue_type` ENUM('planned', 'incident') NOT NULL,
  `issue_state` ENUM('open', 'closed') NOT NULL DEFAULT 'open',
  `issue_summary` TINYTEXT NOT NULL,
  `issue_description` TEXT NOT NULL,
  `issue_cause` TEXT NULL DEFAULT NULL,
  `issue_actions` TEXT NULL DEFAULT NULL,
  `issue_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `issue_sent` TIMESTAMP NULL DEFAULT NULL,
  `issue_send_to` VARCHAR(40) NOT NULL DEFAULT 'active',
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`issue_id`),
  INDEX `fk_issues_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_issues_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `${schema}`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `${schema}`.`logs` 
ADD INDEX `level_idx` (`level` ASC),
ADD INDEX `name_idx` (`name` ASC),
ADD INDEX `fk_logs_users_idx` (`user_id` ASC),
DROP INDEX `name` ,
DROP INDEX `level` ;

CREATE TABLE IF NOT EXISTS `${schema}`.`members` (
  `member_id` INT(11) NOT NULL AUTO_INCREMENT,
  `member_group_id` INT(11) NOT NULL,
  `member_user_id` INT(11) NOT NULL,
  PRIMARY KEY (`member_id`),
  INDEX `fk_members_groups_idx` (`member_group_id` ASC),
  INDEX `fk_members_users_idx` (`member_user_id` ASC),
  CONSTRAINT `fk_members_groups`
    FOREIGN KEY (`member_group_id`)
    REFERENCES `${schema}`.`groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_members_users`
    FOREIGN KEY (`member_user_id`)
    REFERENCES `${schema}`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `${schema}`.`missing` 
CHANGE COLUMN `missing_locale` `missing_locale` VARCHAR(10) NOT NULL AFTER `missing_mobile`,
CHANGE COLUMN `sms_provider` `sms_provider` VARCHAR(255) NULL DEFAULT NULL AFTER `sms_delivery`,
CHANGE COLUMN `missing_answered` `missing_answered` TIMESTAMP NULL DEFAULT NULL AFTER `sms_error`,
CHANGE COLUMN `sms_provider_ref` `sms_provider_ref` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `sms_error` `sms_error` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `sms_mb_sent` `sms_mb_sent` ENUM('false', 'true') NOT NULL DEFAULT 'false' ,
ADD INDEX `fk_missing_operations_idx` (`op_id` ASC),
DROP INDEX `oper_id` ;

ALTER TABLE `${schema}`.`modules` 
ADD INDEX `fk_modules_users_idx` (`user_id` ASC);

ALTER TABLE `${schema}`.`operations` 
CHANGE COLUMN `op_type` `op_type` ENUM('trace', 'test', 'exercise') NOT NULL AFTER `user_id`,
CHANGE COLUMN `op_ref` `op_ref` VARCHAR(255) NULL DEFAULT NULL ,
ADD INDEX `fk_operations_users_idx` (`user_id` ASC),
DROP INDEX `user_id` ;

ALTER TABLE `${schema}`.`permissions` 
CHANGE COLUMN `role_id` `role_id` INT(11) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `user_id` `user_id` INT(11) NOT NULL DEFAULT '0' ,
ADD INDEX `fk_permissions_roles_idx` (`role_id` ASC),
ADD INDEX `fk_permissions_users_idx` (`user_id` ASC),
DROP INDEX `user_id` ,
DROP INDEX `role_id` ;

ALTER TABLE `${schema}`.`positions` 
CHANGE COLUMN `timestamp_device` `timestamp_device` TIMESTAMP NULL DEFAULT NULL AFTER `alt`,
ADD INDEX `fk_positions_missing_idx` (`missing_id` ASC),
DROP INDEX `missing_id` ;

ALTER TABLE `${schema}`.`properties` 
ADD INDEX `fk_properties_users_idx` (`user_id` ASC);

ALTER TABLE `${schema}`.`roles` 
ADD INDEX `role_name_idx` (`role_name` ASC),
ADD INDEX `role_id_idx` (`role_id` ASC),
DROP INDEX `role_name` ;

ALTER TABLE `${schema}`.`templates` 
ADD INDEX `template_name_idx` (`template_name` ASC),
DROP INDEX `template_name` ;

ALTER TABLE `${schema}`.`users` 
CHANGE COLUMN `user_id` `user_id` INT(11) NOT NULL AUTO_INCREMENT ,
ADD UNIQUE INDEX `email_idx` (`email` ASC),
DROP INDEX `email` ;

DROP TABLE IF EXISTS `${schema}`.`countries` ;

ALTER TABLE `${schema}`.`missing` 
ADD CONSTRAINT `fk_missing_operations`
  FOREIGN KEY (`op_id`)
  REFERENCES `${schema}`.`operations` (`op_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `${schema}`.`operations` 
ADD CONSTRAINT `fk_operations_users`
  FOREIGN KEY (`user_id`)
  REFERENCES `${schema}`.`users` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `${schema}`.`permissions` 
ADD CONSTRAINT `fk_permissions_roles`
  FOREIGN KEY (`role_id`)
  REFERENCES `${schema}`.`roles` (`role_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `${schema}`.`positions` 
ADD CONSTRAINT `fk_positions_missing`
  FOREIGN KEY (`missing_id`)
  REFERENCES `${schema}`.`missing` (`missing_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
