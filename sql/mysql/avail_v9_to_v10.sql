ALTER TABLE avail_config ADD COLUMN skin varchar(100);
ALTER TABLE avail_config ADD COLUMN description TEXT DEFAULT NULL;
ALTER TABLE avail_config DROP COLUMN assumeinitialstates;
ALTER TABLE avail_config DROP COLUMN initialassumedhoststate;
ALTER TABLE avail_config DROP COLUMN initialassumedservicestate;
ALTER TABLE avail_config ADD COLUMN include_alerts int(1) DEFAULT 0;
