ALTER TABLE recurring_downtime_objects
    DROP FOREIGN KEY recurring_downtime_objects_ibfk_1;
ADD CONSTRAINT recurring_downtime_objects
    FOREIGN KEY (rd_downtime_id) REFERENCES recurring_downtime(id)
    ON DELETE CASCADE;
