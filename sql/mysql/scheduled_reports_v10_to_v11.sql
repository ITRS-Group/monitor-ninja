ALTER TABLE scheduled_reports
   ADD COLUMN report_time VARCHAR(100),
   ADD COLUMN report_on VARCHAR(100),
   ADD COLUMN report_period VARCHAR(100),
   ADD COLUMN last_sent VARCHAR(100);

