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

-- Replace 'operations' with 'traces' in all permissions
CREATE TEMPORARY TABLE IF NOT EXISTS `tmp` ENGINE=MEMORY AS
(
	SELECT `role_id`, `user_id`, `access`, REPLACE(`resource`, 'operations', 'traces') as `resource` FROM `permissions`
);
TRUNCATE `permissions`;
INSERT INTO `permissions` SELECT * FROM `tmp`;
DROP TABLE `tmp`;

-- Remove all WURFL modules if any
DELETE FROM `modules` WHERE `impl` LIKE '%WURFL%';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
