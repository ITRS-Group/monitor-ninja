ALTER TABLE avail_config ADD COLUMN sla_mode TINYINT NOT NULL DEFAULT 0;
UPDATE avail_config SET sla_mode = 2 WHERE cluster_mode = 1;
UPDATE avail_config SET sla_mode = 1 WHERE use_average = 1;
ALTER TABLE avail_config DROP COLUMN cluster_mode;
ALTER TABLE avail_config DROP COLUMN use_average;
