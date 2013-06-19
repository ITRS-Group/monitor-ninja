ALTER TABLE sla_config ADD COLUMN sla_mode TINYINT NOT NULL DEFAULT 0;
UPDATE sla_config SET sla_mode = 2 WHERE cluster_mode = 1;
UPDATE sla_config SET sla_mode = 1 WHERE use_average = 1;
ALTER TABLE sla_config DROP COLUMN cluster_mode;
ALTER TABLE sla_config DROP COLUMN use_average;
