
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles_users`
--

INSERT INTO `roles_users` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2);

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
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `realname`, `email`, `username`, `password`, `logins`, `last_login`) VALUES
(1, 'Monitor Admin', 'monitor@example.com', 'monitor', 'l5aAn32uSC0xI8FlhfK2D5dAd5Y=', 0, 0);

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `roles_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `ninja_widgets`
--

INSERT INTO `ninja_widgets` (`id`, `user`, `page`, `name`, `friendly_name`, `setting`) VALUES
(1, '', 'tac/index', 'tac_problems', 'Unhandled problems', ''),
(2, '', 'tac/index', 'netw_health', 'Network health', ''),
(3, '', 'tac/index', 'tac_scheduled', 'Scheduled downtime', ''),
(4, '', 'tac/index', 'tac_acknowledged', 'Acknowledged problems', ''),
(5, '', 'tac/index', 'tac_disabled', 'Disabled checks', ''),
(6, '', 'tac/index', 'netw_outages', 'Network outages', ''),
(7, '', 'tac/index', 'tac_hosts', 'Hosts', ''),
(8, '', 'tac/index', 'tac_services', 'Services', ''),
(9, '', 'tac/index', 'tac_monfeat', 'Monitoring features', '');

--
-- Table structure for table `db_version`
--

CREATE TABLE IF NOT EXISTS `ninja_db_version` (
  `id` int(11) NOT NULL auto_increment,
  `owner` varchar(100) NOT NULL,
  `version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;

--
-- Dumping data for table `db_version`
--

INSERT INTO `ninja_db_version` (`id`, `owner`, `version`) VALUES
(1, 'ninja', 1);