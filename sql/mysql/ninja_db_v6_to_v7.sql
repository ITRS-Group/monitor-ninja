CREATE TABLE IF NOT EXISTS ninja_saved_queries (
	id int(11) NOT NULL auto_increment,
	username varchar(255) NOT NULL,
	query_name varchar(255) NOT NULL,
	query_table varchar(255) NOT NULL,
	query TEXT NOT NULL,
	query_description TEXT NOT NULL,
	PRIMARY KEY (id),
	INDEX (username,query_table),
	INDEX (query_table,username),
	INDEX (query_name),
	UNIQUE (username,query_name)
) COLLATE latin1_general_cs ENGINE=InnoDB;