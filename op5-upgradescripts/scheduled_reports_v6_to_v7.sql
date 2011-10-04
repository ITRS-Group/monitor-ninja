ALTER TABLE summary_config CHANGE user username VARCHAR(200) NOT NULL;
ALTER TABLE scheduled_reports CHANGE user username VARCHAR(255) NOT NULL;
