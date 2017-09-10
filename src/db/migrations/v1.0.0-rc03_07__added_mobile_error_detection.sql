-- MySQL Workbench Synchronization
-- Generated: 2017-09-10 22:32
-- Model: RescueMe
-- Version: 1.0
-- Project: RescueMe
-- Author: Kenneth Gulbrandsøy
-- RescueMe MySQL Model

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER TABLE `${schema}`.`requests`
CHANGE COLUMN `request_type` `request_type` ENUM('response', 'error', 'position') NOT NULL ;

CREATE TABLE IF NOT EXISTS `${schema}`.`errors` (
  `error_id` INT(11) NOT NULL AUTO_INCREMENT,
  `error_number` INT(6) NOT NULL,
  `error_data` BLOB NULL DEFAULT NULL,
  `mobile_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`error_id`),
  INDEX `fk_errors_mobiles_idx` (`mobile_id` ASC),
  CONSTRAINT `fk_errors_mobiles`
    FOREIGN KEY (`mobile_id`)
    REFERENCES `${schema}`.`mobiles` (`mobile_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
