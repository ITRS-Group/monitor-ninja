ALTER TABLE comment RENAME TO comment_tbl;
ALTER TABLE ninja_widgets CHANGE COLUMN user username varchar(200) NOT NULL;
ALTER TABLE ninja_settings CHANGE COLUMN user username varchar(200) NOT NULL;
ALTER TABLE avail_config CHANGE COLUMN user username varchar(255) NOT NULL;
ALTER TABLE sla_config CHANGE user username VARCHAR(255) NOT NULL;
ALTER TABLE summary_config CHANGE user username VARCHAR(200) NOT NULL;
ALTER TABLE scheduled_reports CHANGE user username VARCHAR(255) NOT NULL;