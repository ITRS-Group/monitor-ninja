-- When modifying this file, make sure to also update
-- the version if necessary (set last in the file)

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
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS avail_config_objects (
  id int(11) NOT NULL auto_increment,
  avail_id int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (id),
  KEY avail_id (avail_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS avail_db_version (
  version int(11) NOT NULL default '0'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
INSERT INTO avail_db_version VALUES(1);
