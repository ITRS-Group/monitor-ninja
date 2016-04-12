DROP TABLE IF EXISTS `dashboards`;
CREATE TABLE IF NOT EXISTS `dashboards` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) NOT NULL,
	`username` varchar(255) NOT NULL,
	`layout` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `username` (`username`)
);

DROP TABLE IF EXISTS `dashboard_widgets`;
CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
  `id` int(11) NOT NULL auto_increment,
  `dashboard_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `setting` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `dashboard_id` (`dashboard_id`)
);
