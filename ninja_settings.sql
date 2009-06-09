
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
(1, '', 'tac/index', 'tac_problems', 'Unhandled problems', 'a:1:{i:0;s:5:"index";} '),
(2, '', 'tac/index', 'netw_health', 'Network health', 'a:1:{i:0;s:5:"index";} '),
(3, '', 'tac/index', 'tac_scheduled', 'Scheduled downtime', 'a:1:{i:0;s:5:"index";} '),
(4, '', 'tac/index', 'tac_acknowledged', 'Acknowledged problems', 'a:1:{i:0;s:5:"index";} '),
(5, '', 'tac/index', 'tac_disabled', 'Disabled checks', 'a:1:{i:0;s:5:"index";} '),
(6, '', 'tac/index', 'netw_outages', 'Network outages', 'a:1:{i:0;s:5:"index";} '),
(7, '', 'tac/index', 'tac_hosts', 'Hosts', 'a:1:{i:0;s:5:"index";} '),
(8, '', 'tac/index', 'tac_services', 'Services', 'a:1:{i:0;s:5:"index";} '),
(9, '', 'tac/index', 'tac_monfeat', 'Monitoring features', 'a:1:{i:0;s:5:"index";} ');
