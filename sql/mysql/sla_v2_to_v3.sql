ALTER TABLE sla_config ADD use_average TINYINT(1) DEFAULT 0;
UPDATE sla_db_version SET version = 3;
