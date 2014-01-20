ALTER TABLE recurring_downtime
	ADD COLUMN comment TEXT NOT NULL DEFAULT "",
	ADD COLUMN start_time INT NOT NULL,
	ADD COLUMN end_time INT NOT NULL,
	ADD COLUMN duration VARCHAR(5) NOT NULL,
	ADD COLUMN fixed TINYINT(1) DEFAULT 1,
	ADD COLUMN weekdays VARCHAR(255),
	ADD COLUMN months VARCHAR(255);

CREATE TABLE recurring_downtime_objects (
	recurring_downtime_id int(11) NOT NULL,
	object_name VARCHAR(255),
	FOREIGN KEY (recurring_downtime_id) REFERENCES recurring_downtime(id)
);
