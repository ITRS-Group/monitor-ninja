-- SQL declarations for avail, sla and scheduled reports

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `avail_config` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `rpttimeperiod` varchar(75) NOT NULL,
  `report_period` varchar(50) NOT NULL,
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `report_type` varchar(30) NOT NULL,
  `initialassumedhoststate` int(11) NOT NULL default '0',
  `initialassumedservicestate` int(11) NOT NULL default '0',
  `assumeinitialstates` int(11) NOT NULL default '0',
  `scheduleddowntimeasuptime` int(11) NOT NULL default '0',
  `assumestatesduringnotrunning` int(11) NOT NULL default '0',
  `includesoftstates` int(11) NOT NULL default '0',
  `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
  `use_average` TINYINT(1) DEFAULT 0,
  `use_alias` TINYINT(1) DEFAULT 0,
  `cluster_mode` INT NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS avail_config_objects (
  `id` int(11) NOT NULL auto_increment,
  `avail_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (id),
  KEY avail_id (avail_id)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS avail_db_version (
  version int(11) NOT NULL default '0'
) COLLATE latin1_general_cs;

INSERT INTO avail_db_version VALUES(1);

CREATE TABLE IF NOT EXISTS `scheduled_reports` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `report_type_id` int(11) NOT NULL default '0',
  `report_id` int(11) NOT NULL default '0',
  `recipients` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `period_id` int(11) NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `report_type_id` (`report_type_id`),
  KEY `user` (`user`)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS `scheduled_report_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `script_reports_path` varchar(255) NOT NULL,
  `script_reports_run` varchar(255) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `identifier` (`identifier`)
) COLLATE latin1_general_cs;


CREATE TABLE IF NOT EXISTS scheduled_reports_db_version (
  version varchar(10) NOT NULL
) COLLATE latin1_general_cs;

INSERT INTO scheduled_reports_db_version VALUES('1.0.0');

INSERT INTO `scheduled_report_types` (`id`, `identifier`) VALUES
(1, 'avail'),
(2, 'sla');

CREATE TABLE IF NOT EXISTS `scheduled_report_periods` (
  `id` int(11) NOT NULL auto_increment,
  `periodname` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) COLLATE latin1_general_cs;

INSERT INTO `scheduled_report_periods` (`id`, `periodname`) VALUES
(1, 'Weekly'),
(2, 'Monthly'),
(3, 'Daily');

CREATE TABLE IF NOT EXISTS sla_config (
 `id` int(11) NOT NULL auto_increment,
 `user` varchar(255) NOT NULL,
 `sla_name` varchar(255) NOT NULL,
 `info` text NOT NULL,
 `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
 `rpttimeperiod` varchar(75) NOT NULL,
 `report_period` varchar(50) NOT NULL,
 `start_time` int(11) NOT NULL default '0',
 `end_time` int(11) NOT NULL default '0',
 `report_type` varchar(30) NOT NULL,
 `initialassumedhoststate` int(11) NOT NULL default '0',
 `initialassumedservicestate` int(11) NOT NULL default '0',
 `assumeinitialstates` int(11) NOT NULL default '0',
 `scheduleddowntimeasuptime` int(11) NOT NULL default '0',
 `assumestatesduringnotrunning` int(11) NOT NULL default '0',
 `includesoftstates` int(11) NOT NULL default '0',
 `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
 `use_average` TINYINT(1) DEFAULT 0,
 `use_alias` TINYINT(1) DEFAULT 0,
 `cluster_mode` INT NOT NULL DEFAULT 0,
 PRIMARY KEY  (id)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS sla_config_objects (
`id` int(11) NOT NULL auto_increment,
`sla_id` int(11) NOT NULL default '0',
`name` varchar(255) NOT NULL,
PRIMARY KEY  (id),
KEY sla_id (sla_id)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS sla_periods (
`id` int(11) NOT NULL auto_increment,
`sla_id` int(11) NOT NULL default '0',
`name` varchar(20) NOT NULL,
`value` float NOT NULL default '0',
PRIMARY KEY  (id),
KEY sla_id (sla_id)
) COLLATE latin1_general_cs;

CREATE TABLE IF NOT EXISTS sla_db_version (
 version int(11) NOT NULL default '0'
) COLLATE latin1_general_cs;

INSERT INTO sla_db_version VALUES(1);


