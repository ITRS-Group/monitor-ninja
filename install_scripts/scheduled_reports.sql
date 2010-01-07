CREATE TABLE IF NOT EXISTS `scheduled_report_periods` (
  `id` int(11) NOT NULL auto_increment,
  `periodname` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `scheduled_report_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `script_reports_path` varchar(255) NOT NULL,
  `script_reports_run` varchar(255) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `identifier` (`identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;


CREATE TABLE IF NOT EXISTS scheduled_reports_db_version (
  version varchar(10) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- Fix the paths below when decided
/*INSERT INTO `scheduled_report_types` (`id`, `name`, `script_reports_path`, `script_reports_run`, `identifier`) VALUES
(1, 'Availability Report', '/opt/monitor/op5/reports/gui/saved_reports.php', '/opt/monitor/op5/reports/gui/avail_auto.php', 'avail'),
(2, 'SLA Report', '/opt/monitor/op5/reports/gui/sla/saved_reports.php', '/opt/monitor/op5/reports/gui/sla/sla_auto.php', 'sla');
*/
INSERT INTO `scheduled_report_periods` (`id`, `periodname`) VALUES
(1, 'Weekly'),
(2, 'Monthly');

-- ALTER TABLE auto_reports ADD avail_config_id INT;
INSERT INTO scheduled_reports_db_version VALUES('1.0.0');