CREATE TABLE IF NOT EXISTS ninja_report_comments (
  timestamp int(11) NOT NULL default '0',
  event_type int(11) NOT NULL default '0',
  host_name varchar(255) default '',
  service_description varchar(255) default '',
  username varchar(100) NOT NULL,
  user_comment TEXT NOT NULL,
  KEY report_entry (timestamp, host_name, service_description, event_type)
) COLLATE latin1_general_cs;

