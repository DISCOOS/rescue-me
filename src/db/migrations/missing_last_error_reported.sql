ALTER TABLE missing ADD COLUMN `last_error` timestamp NULL DEFAULT NULL;
ALTER TABLE missing ADD COLUMN `last_error_code` int(6) NULL DEFAULT NULL;
ALTER TABLE missing ADD COLUMN `last_error_desc` text NULL DEFAULT NULL;