ALTER TABLE sla_config CHANGE COLUMN sla_name report_name VARCHAR(255) NOT NULL;
ALTER TABLE sla_config ADD COLUMN host_filter_status varchar(100);
ALTER TABLE sla_config ADD COLUMN service_filter_status varchar(100);
ALTER TABLE sla_config DROP COLUMN assumeinitialstates;
ALTER TABLE sla_config DROP COLUMN initialassumedhoststate;
ALTER TABLE sla_config DROP COLUMN initialassumedservicestate;
