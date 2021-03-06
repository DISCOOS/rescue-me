-- MySQL Script generated by MySQL Workbench
-- ma. 31. juli 2017 kl. 23.29 +0200
-- Model: RescueMe    Version: 1.0

-- RescueMe MySQL Model

-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema rm_test
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `password` CHAR(128) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL,
  `mobile_country` CHAR(4) NOT NULL,
  `mobile` VARCHAR(25) NOT NULL,
  `state` ENUM('pending', 'active', 'disabled', 'deleted') NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `email_idx` (`email` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `issues`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `issues` (
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
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `alerts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `alerts` (
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
    REFERENCES `issues` (`issue_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_alerts_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `alerts_closed`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `alerts_closed` (
  `alert_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`, `alert_id`),
  INDEX `fk_alerts_closed_users_idx` (`user_id` ASC),
  INDEX `fk_alerts_closed_alerts_idx` (`alert_id` ASC),
  CONSTRAINT `fk_alerts_closed_alerts`
    FOREIGN KEY (`alert_id`)
    REFERENCES `alerts` (`alert_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_alerts_closed_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `versions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `versions` (
  `version_id` INT(6) NOT NULL AUTO_INCREMENT,
  `version_name` VARCHAR(255) NOT NULL,
  `version_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_name` TINYTEXT NOT NULL,
  `group_owner_user_id` INT(11) NOT NULL,
  PRIMARY KEY (`group_id`),
  INDEX `fk_groups_users_idx` (`group_owner_user_id` ASC),
  CONSTRAINT `fk_groups_users`
    FOREIGN KEY (`group_owner_user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `context` LONGTEXT NULL DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `client_ip` VARCHAR(40) NOT NULL DEFAULT 'UNKNOWN',
  PRIMARY KEY (`log_id`),
  INDEX `level_idx` (`level` ASC),
  INDEX `name_idx` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `members`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `members` (
  `member_id` INT(11) NOT NULL AUTO_INCREMENT,
  `member_group_id` INT(11) NOT NULL,
  `member_user_id` INT(11) NOT NULL,
  PRIMARY KEY (`member_id`),
  INDEX `fk_members_groups_idx` (`member_group_id` ASC),
  INDEX `fk_members_users_idx` (`member_user_id` ASC),
  CONSTRAINT `fk_members_groups`
    FOREIGN KEY (`member_group_id`)
    REFERENCES `groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_members_users`
    FOREIGN KEY (`member_user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `operations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `operations` (
  `op_id` INT(6) NOT NULL AUTO_INCREMENT,
  `user_id` INT(6) NOT NULL,
  `op_type` ENUM('trace', 'test', 'exercise') NOT NULL,
  `op_name` VARCHAR(255) NOT NULL,
  `alert_mobile_country` CHAR(4) NOT NULL,
  `alert_mobile` VARCHAR(25) NOT NULL,
  `op_ref` VARCHAR(255) NULL DEFAULT NULL,
  `op_opened` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `op_closed` TIMESTAMP NULL DEFAULT NULL,
  `op_comments` TEXT NOT NULL,
  PRIMARY KEY (`op_id`),
  INDEX `fk_operations_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_operations_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `missing`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `missing` (
  `missing_id` INT(6) NOT NULL AUTO_INCREMENT,
  `op_id` INT(6) NOT NULL,
  `missing_name` CHAR(255) NOT NULL,
  `missing_mobile_country` CHAR(4) NOT NULL,
  `missing_mobile` VARCHAR(25) NOT NULL,
  `missing_locale` VARCHAR(10) NOT NULL,
  `missing_hash` VARCHAR(45) NULL DEFAULT NULL,
  `sms_sent` TIMESTAMP NULL DEFAULT NULL,
  `sms_delivery` TIMESTAMP NULL DEFAULT NULL,
  `sms_provider` VARCHAR(255) NULL DEFAULT NULL,
  `sms_provider_ref` VARCHAR(255) NULL DEFAULT NULL,
  `sms_error` VARCHAR(255) NULL DEFAULT NULL,
  `missing_answered` TIMESTAMP NULL DEFAULT NULL,
  `missing_reported` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sms2_sent` ENUM('false', 'true') NOT NULL DEFAULT 'false',
  `sms_mb_sent` ENUM('false', 'true') NOT NULL DEFAULT 'false',
  `sms_text` TEXT NOT NULL,
  PRIMARY KEY (`missing_id`),
  INDEX `fk_missing_operations_idx` (`op_id` ASC),
  CONSTRAINT `fk_missing_operations`
    FOREIGN KEY (`op_id`)
    REFERENCES `operations` (`op_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `modules`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `modules` (
  `module_id` INT(4) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `type` VARCHAR(50) NOT NULL,
  `impl` VARCHAR(50) NOT NULL,
  `config` TEXT NOT NULL,
  PRIMARY KEY (`module_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `user_id` INT(11) NOT NULL,
  `role_name` VARCHAR(100) NOT NULL,
  `role_id` INT(11) NOT NULL,
  INDEX `role_name_idx` (`role_name` ASC),
  INDEX `role_id_idx` (`role_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `role_id` INT(11) NOT NULL DEFAULT '0',
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `access` VARCHAR(100) NOT NULL,
  `resource` VARCHAR(100) NOT NULL,
  INDEX `fk_permissions_roles_idx` (`role_id` ASC),
  CONSTRAINT `fk_permissions_roles`
    FOREIGN KEY (`role_id`)
    REFERENCES `roles` (`role_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `positions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `positions` (
  `pos_id` INT(10) NOT NULL AUTO_INCREMENT,
  `missing_id` INT(6) NOT NULL,
  `lat` DOUBLE NOT NULL,
  `lon` DOUBLE NOT NULL,
  `acc` INT(5) NOT NULL,
  `alt` INT(4) NOT NULL,
  `timestamp_device` TIMESTAMP NULL DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_agent` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`pos_id`),
  INDEX `fk_positions_missing_idx` (`missing_id` ASC),
  CONSTRAINT `fk_positions_missing`
    FOREIGN KEY (`missing_id`)
    REFERENCES `missing` (`missing_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `properties`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `properties` (
  `property_id` INT(4) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `name` VARCHAR(50) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`property_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `templates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `templates` (
  `template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_type` ENUM('message') NOT NULL DEFAULT 'message',
  `template_name` VARCHAR(50) NOT NULL,
  `template_locale` VARCHAR(10) NOT NULL,
  `template_content` TEXT NOT NULL,
  PRIMARY KEY (`template_id`),
  INDEX `template_name_idx` (`template_name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
