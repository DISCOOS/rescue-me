ALTER TABLE `missing` ADD `sms_mb_sent` ENUM( 'false', 'true' ) NOT NULL ,
ADD `sms_delivered` ENUM( 'false', 'true' ) NOT NULL AFTER `sms_sent` ,
ADD `sms_error` VARCHAR( 255 ) NOT NULL AFTER `sms_delivered`,
ADD `sms_provider_ref` VARCHAR( 255 ) NOT NULL AFTER `sms_delivered` 