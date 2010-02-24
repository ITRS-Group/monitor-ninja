-- Adding user field for authentication

ALTER TABLE `sla_config` ADD `user` VARCHAR( 255 ) NOT NULL AFTER `id` ;
ALTER TABLE `sla_config` ADD INDEX ( `user` ) ;
UPDATE sla_db_version SET version = 2;
