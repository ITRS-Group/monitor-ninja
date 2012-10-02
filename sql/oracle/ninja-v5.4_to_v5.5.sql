ALTER TABLE scheduled_reports ADD local_persistent_filepath VARCHAR2(200 CHAR) DEFAULT NULL;

UPDATE ninja_db_version SET version=2;
UPDATE sla_db_version SET version=8;
UPDATE avail_db_version SET version=8;
UPDATE scheduled_reports_db_version SET version=8;
