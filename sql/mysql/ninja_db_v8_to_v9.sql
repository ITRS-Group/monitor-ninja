CREATE TABLE IF NOT EXISTS `ninja_saved_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `filter_name` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `filter_table` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `filter` text COLLATE latin1_general_cs NOT NULL,
  `filter_description` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_2` (`username`,`filter_name`),
  KEY `username` (`username`,`filter_table`),
  KEY `filter_table` (`filter_table`,`username`),
  KEY `filter_name` (`filter_name`)
);

DROP TABLE IF EXISTS `ninja_saved_queries`;