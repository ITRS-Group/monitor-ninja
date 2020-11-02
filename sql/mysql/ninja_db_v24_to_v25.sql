ALTER TABLE recurring_downtime
   ADD COLUMN start_date VARCHAR(50),
   ADD COLUMN end_date VARCHAR(50),
   ADD COLUMN recurrence VARCHAR(100);

ALTER TABLE recurring_downtime
   ADD COLUMN recurrence_on VARCHAR(100),
   ADD COLUMN recurrence_ends VARCHAR(100);