-- Adds two columns to saved avail reports that stores which states to display

ALTER TABLE avail_config ADD COLUMN host_filter_status varchar(100), ADD COLUMN service_filter_status varchar(100);
