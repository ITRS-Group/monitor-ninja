CREATE TABLE IF NOT EXISTS recurring_downtime (
 id int(11) NOT NULL auto_increment,
 author varchar(255) NOT NULL,
 downtime_type varchar(255) NOT NULL,
 data text NOT NULL,
 last_update int(11) NOT NULL default '0',
 PRIMARY KEY  (id),
 KEY author (author),
 KEY downtime_type (downtime_type)
) COLLATE latin1_general_cs;
