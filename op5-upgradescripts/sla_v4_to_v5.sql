-- Modify saved sla reports tables to case sensitive

ALTER TABLE sla_config COLLATE latin1_general_cs;
ALTER TABLE sla_config_objects COLLATE latin1_general_cs;
