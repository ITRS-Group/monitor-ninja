CREATE TABLE IF NOT EXISTS saved_searches (
	id int(11) NOT NULL auto_increment,
	username varchar(255) NOT NULL,
	search_name varchar(255) NOT NULL,
	search_query TEXT NOT NULL,
	search_description TEXT NOT NULL,
	PRIMARY KEY (id),
	KEY username (username)
);