-- When modifying this file, make sure to also update
-- the version if necessary (set last in the file)

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE sla_config (
  id int(11) NOT NULL auto_increment,
  sla_name varchar(255) NOT NULL,
  info text NOT NULL,
  created timestamp NOT NULL default CURRENT_TIMESTAMP,
  rpttimeperiod varchar(75) NOT NULL,
  report_period varchar(50) NOT NULL,
  start_time int(11) NOT NULL default '0',
  end_time int(11) NOT NULL default '0',
  report_type varchar(30) NOT NULL,
  initialassumedhoststate int(11) NOT NULL default '0',
  initialassumedservicestate int(11) NOT NULL default '0',
  assumeinitialstates int(11) NOT NULL default '0',
  scheduleddowntimeasuptime int(11) NOT NULL default '0',
  assumestatesduringnotrunning int(11) NOT NULL default '0',
  includesoftstates int(11) NOT NULL default '0',
  updated timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE sla_config_objects (
  id int(11) NOT NULL auto_increment,
  sla_id int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (id),
  KEY sla_id (sla_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE sla_periods (
  id int(11) NOT NULL auto_increment,
  sla_id int(11) NOT NULL default '0',
  `name` varchar(20) NOT NULL,
  `value` float NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY sla_id (sla_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE sla_db_version (
  version int(11) NOT NULL default '0'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
INSERT INTO sla_db_version VALUES(1);
