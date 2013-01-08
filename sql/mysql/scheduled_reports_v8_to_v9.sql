ALTER TABLE scheduled_reports CHANGE local_persistent_filepath VARCHAR(200) NOT NULL DEFAULT '';
UPDATE scheduled_reports SET local_persistent_filepath = '' WHERE local_persistent_filepath IS NULL;
