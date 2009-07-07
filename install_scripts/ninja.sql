--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_name` (`name`)
);

--
-- Data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'login', 'Login privileges, granted after account confirmation'),
(2, 'admin', 'Administrative user, has access to everything.');

-- --------------------------------------------------------

--
-- Table structure for table `roles_users`
--

DROP TABLE IF EXISTS `roles_users`;
CREATE TABLE IF NOT EXISTS `roles_users` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`role_id`),
  KEY `fk_role_id` (`role_id`)
);

--
-- Dumping data for table `roles_users`
--

INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES(1, 1);
INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `realname` varchar(100) NOT NULL,
  `email` varchar(127) NOT NULL,
  `username` varchar(100) NOT NULL default '',
  `password_algo` varchar(20) NOT NULL default 'b64_sha1',
  `password` varchar(50) NOT NULL,
  `logins` int(10) unsigned NOT NULL default '0',
  `last_login` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`)
);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `realname`, `email`, `username`, `password`, `logins`, `last_login`) VALUES
(1, 'Monitor Admin', 'monitor@example.com', 'monitor', 'l5aAn32uSC0xI8FlhfK2D5dAd5Y=', 0, 0);


--
-- Table structure for table `user_tokens`;
--
DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE IF NOT EXISTS `user_tokens` (
  id int(11) unsigned NOT NULL auto_increment,
  user_id int(11) unsigned NOT NULL,
  user_agent varchar(40) NOT NULL,
  token varchar(32) NOT NULL,
  created int(10) unsigned NOT NULL,
  expires int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniq_token (token),
  KEY fk_user_id (user_id)
);

--
-- Table structure for table `ninja_settings`
--

DROP TABLE IF EXISTS `ninja_settings`;
CREATE TABLE IF NOT EXISTS `ninja_settings` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(200) NOT NULL,
  `page` varchar(200) NOT NULL,
  `type` varchar(200) NOT NULL,
  `setting` text NOT NULL,
  `widget_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `page` (`page`),
  KEY `widget_id` (`widget_id`)
);


-- --------------------------------------------------------

--
-- Table structure for table `ninja_widgets`
--

DROP TABLE IF EXISTS `ninja_widgets`;
CREATE TABLE IF NOT EXISTS `ninja_widgets` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(200) NOT NULL,
  `page` varchar(200) NOT NULL,
  `name` varchar(255) NOT NULL,
  `friendly_name` varchar(255) NOT NULL,
  `setting` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
);

--
-- Data for table `ninja_widgets`
--

INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(1, '', 'tac/index', 'tac_problems', 'Unhandled problems', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(2, '', 'tac/index', 'netw_health', 'Network health', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(3, '', 'tac/index', 'tac_scheduled', 'Scheduled downtime', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(4, '', 'tac/index', 'tac_acknowledged', 'Acknowledged problems', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(5, '', 'tac/index', 'tac_disabled', 'Disabled checks', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(6, '', 'tac/index', 'netw_outages', 'Network outages', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(7, '', 'tac/index', 'tac_hosts', 'Hosts', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(8, '', 'tac/index', 'tac_services', 'Services', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(9, '', 'tac/index', 'tac_monfeat', 'Monitoring features', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(10, '', 'status', 'status_totals', 'Status Totals', '');
INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES \
	(11, '', 'tac/index', 'monitoring_performance', 'Monitoring Performance', '');

--
-- Table structure for table `ninja_db_version`
--

CREATE TABLE IF NOT EXISTS `ninja_db_version` (
  `id` int(11) NOT NULL auto_increment,
  `version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

--
-- Data for table `ninja_db_version`
--

INSERT INTO `ninja_db_version` (`id`, `version`) VALUES(1, 1);

--
-- Table structure for table `ninja_user_authorization`
--

DROP TABLE IF EXISTS `ninja_user_authorization`;
CREATE TABLE IF NOT EXISTS `ninja_user_authorization` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `system_information` int(11) NOT NULL default '0',
  `configuration_information` int(11) NOT NULL default '0',
  `system_commands` int(11) NOT NULL default '0',
  `all_services` int(11) NOT NULL default '0',
  `all_hosts` int(11) NOT NULL default '0',
  `all_service_commands` int(11) NOT NULL default '0',
  `all_host_commands` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
);
