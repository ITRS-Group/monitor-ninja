CREATE TABLE saved_reports (
	id int(11) NOT NULL auto_increment,
	type varchar(255) NOT NULL,
	report_name varchar(255) NOT NULL,
	created_by varchar(255) NOT NULL,
	created_at int NOT NULL DEFAULT 0,
	updated_by varchar(255) NOT NULL,
	updated_at int NOT NULL DEFAULT 0,
	PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE saved_reports_options (
	report_id int(11) NOT NULL,
	name varchar(255) NOT NULL,
	value text NOT NULL,
	PRIMARY KEY (report_id, name),
	FOREIGN KEY (report_id) REFERENCES saved_reports (id)
) ENGINE=InnoDB;

CREATE TABLE saved_reports_objects (
	report_id int(11) NOT NULL,
	object_name varchar(255) NOT NULL,
	PRIMARY KEY (report_id, object_name),
	FOREIGN KEY (report_id) REFERENCES saved_reports (id)
) ENGINE=InnoDB;
