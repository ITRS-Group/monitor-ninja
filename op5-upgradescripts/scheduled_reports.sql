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
) ENGINE=MyISAM DEFAULT CHARSET=latin1_general_cs;

CREATE TABLE IF NOT EXISTS `scheduled_report_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `script_reports_path` varchar(255) NOT NULL,
  `script_reports_run` varchar(255) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `identifier` (`identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1_general_cs;


CREATE TABLE IF NOT EXISTS scheduled_reports_db_version (
  version varchar(10) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1_general_cs;

INSERT INTO scheduled_reports_db_version VALUES('5');

INSERT INTO `scheduled_report_types` (`id`, `identifier`) VALUES
(1, 'avail'),
(2, 'sla');

CREATE TABLE IF NOT EXISTS `scheduled_report_periods` (
  `id` int(11) NOT NULL auto_increment,
  `periodname` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1_general_cs;

INSERT INTO `scheduled_report_periods` (`id`, `periodname`) VALUES
(1, 'Weekly'),
(2, 'Monthly'),
(3, 'Daily');
