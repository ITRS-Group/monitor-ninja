ALTER TABLE avail_config ADD use_average TINYINT(1) DEFAULT 0;
ALTER TABLE avail_config ADD use_alias TINYINT(1) DEFAULT 0;
UPDATE avail_db_version SET version = 2;