-- Modify saved avail reports tables to case sensitive

ALTER TABLE avail_config COLLATE latin1_general_cs;
ALTER TABLE avail_config_objects COLLATE latin1_general_cs;
ALTER TABLE avail_config ADD cluster_mode INT NOT NULL DEFAULT 0;