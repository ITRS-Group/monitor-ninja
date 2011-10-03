DROP TABLE IF EXISTS summary_config;
CREATE TABLE IF NOT EXISTS summary_config (
  id int(11) NOT NULL auto_increment,
  user varchar(200) NOT NULL,
  report_name varchar(200) NOT NULL,
  setting text NOT NULL,
  PRIMARY KEY (id),
  KEY user (user)
);

INSERT INTO scheduled_report_types (name, identifier) VALUES('Alert Summary Reports', 'summary');
