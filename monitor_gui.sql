--
-- Database: `monitor_gui`
--

-- --------------------------------------------------------

--
-- Table structure for table `command`
--

DROP TABLE IF EXISTS `command`;
CREATE TABLE IF NOT EXISTS `command` (
  `id` int(11) NOT NULL auto_increment,
  `command_name` varchar(75) NOT NULL,
  `command_line` blob NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `command_name` (`command_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `command`
--


-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) NOT NULL auto_increment,
  `contact_name` varchar(75) default NULL,
  `alias` varchar(160) NOT NULL,
  `host_notifications_enabled` tinyint(1) default NULL,
  `service_notifications_enabled` tinyint(1) default NULL,
  `can_submit_commands` tinyint(1) default NULL,
  `retain_status_information` tinyint(1) default NULL,
  `retain_nonstatus_information` tinyint(1) default NULL,
  `host_notification_period` int(11) default NULL,
  `service_notification_period` int(11) default NULL,
  `host_notification_options` varchar(15) default NULL,
  `service_notification_options` varchar(15) default NULL,
  `host_notification_commands` int(11) default NULL,
  `host_notification_commands_args` text,
  `service_notification_commands` int(11) default NULL,
  `service_notification_commands_args` text,
  `email` varchar(60) default NULL,
  `pager` varchar(18) default NULL,
  `address1` varchar(100) default NULL,
  `address2` varchar(100) default NULL,
  `address3` varchar(100) default NULL,
  `address4` varchar(100) default NULL,
  `address5` varchar(100) default NULL,
  `address6` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `contact_name` (`contact_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contact`
--


-- --------------------------------------------------------

--
-- Table structure for table `contactgroup`
--

DROP TABLE IF EXISTS `contactgroup`;
CREATE TABLE IF NOT EXISTS `contactgroup` (
  `id` int(11) NOT NULL auto_increment,
  `contactgroup_name` varchar(75) NOT NULL,
  `alias` varchar(160) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `contactgroup_name` (`contactgroup_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `contact_contactgroup`
--

DROP TABLE IF EXISTS `contact_contactgroup`;
CREATE TABLE IF NOT EXISTS `contact_contactgroup` (
  `contact` int(11) NOT NULL,
  `contactgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contact_contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `custom_vars`
--

DROP TABLE IF EXISTS `custom_vars`;
CREATE TABLE IF NOT EXISTS `custom_vars` (
  `obj_type` varchar(30) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `variable` varchar(100) default NULL,
  `value` varchar(255) default NULL,
  UNIQUE KEY `objvar` (`obj_type`,`obj_id`,`variable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `custom_vars`
--


-- --------------------------------------------------------

--
-- Table structure for table `gui_access`
--

DROP TABLE IF EXISTS `gui_access`;
CREATE TABLE IF NOT EXISTS `gui_access` (
  `user` varchar(30) NOT NULL,
  `view` tinyint(1) default NULL,
  `view_all` tinyint(1) default NULL,
  `modify_obj` tinyint(1) default NULL,
  `modify_any` tinyint(1) default NULL,
  `delete_obj` tinyint(1) default NULL,
  `delete_all` tinyint(1) default NULL,
  `import` tinyint(1) default NULL,
  `probe` tinyint(1) default NULL,
  `admin` tinyint(1) default NULL,
  PRIMARY KEY  (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_access`
--

INSERT INTO `gui_access` (`user`, `view`, `view_all`, `modify_obj`, `modify_any`, `delete_obj`, `delete_all`, `import`, `probe`, `admin`) VALUES
('monitor', 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `gui_action_log`
--

DROP TABLE IF EXISTS `gui_action_log`;
CREATE TABLE IF NOT EXISTS `gui_action_log` (
  `user` varchar(30) NOT NULL,
  `action` varchar(50) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gui_action_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `host`
--

DROP TABLE IF EXISTS `host`;
CREATE TABLE IF NOT EXISTS `host` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` varchar(75) default NULL,
  `alias` varchar(100) NOT NULL,
  `display_name` varchar(100) default NULL,
  `address` varchar(75) NOT NULL,
  `initial_state` varchar(18) default NULL,
  `check_command` int(11) default NULL,
  `check_command_args` text,
  `max_check_attempts` smallint(6) default NULL,
  `check_interval` smallint(6) default NULL,
  `retry_interval` smallint(6) default NULL,
  `active_checks_enabled` tinyint(1) default NULL,
  `passive_checks_enabled` tinyint(1) default NULL,
  `check_period` int(11) default NULL,
  `obsess_over_host` tinyint(1) default NULL,
  `check_freshness` tinyint(1) default NULL,
  `freshness_threshold` float default NULL,
  `event_handler` int(11) default NULL,
  `event_handler_args` text,
  `event_handler_enabled` tinyint(1) default NULL,
  `low_flap_threshold` float default NULL,
  `high_flap_threshold` float default NULL,
  `flap_detection_enabled` tinyint(1) default NULL,
  `flap_detection_options` varchar(18) default NULL,
  `process_perf_data` tinyint(1) default NULL,
  `retain_status_information` tinyint(1) default NULL,
  `retain_nonstatus_information` tinyint(1) default NULL,
  `notification_interval` mediumint(9) default NULL,
  `first_notification_delay` int(11) default NULL,
  `notification_period` int(11) default NULL,
  `notification_options` varchar(15) default NULL,
  `notifications_enabled` tinyint(1) default NULL,
  `stalking_options` varchar(15) default NULL,
  `notes` varchar(255) default NULL,
  `notes_url` varchar(255) default NULL,
  `action_url` varchar(255) default NULL,
  `icon_image` varchar(60) default NULL,
  `icon_image_alt` varchar(60) default NULL,
  `statusmap_image` varchar(60) default NULL,
  `2d_coords` varchar(20) default NULL,
  `3d_coords` varchar(30) default NULL,
  `failure_prediction_enabled` tinyint(1) default NULL,
  `problem_has_been_acknowledged` int(10) NOT NULL default '0',
  `acknowledgement_type` int(10) NOT NULL default '0',
  `check_type` int(10) NOT NULL default '0',
  `current_state` int(10) NOT NULL default '0',
  `last_state` int(10) NOT NULL default '0',
  `last_hard_state` int(10) NOT NULL default '0',
  `plugin_output` text,
  `long_plugin_output` text,
  `perf_data` text,
  `state_type` int(10) NOT NULL default '0',
  `current_attempt` int(10) NOT NULL default '0',
  `latency` float default NULL,
  `execution_time` float default NULL,
  `is_executing` int(10) NOT NULL default '0',
  `check_options` int(10) NOT NULL default '0',
  `last_host_notification` datetime default NULL,
  `next_host_notification` datetime default NULL,
  `next_check` datetime default NULL,
  `should_be_scheduled` int(10) NOT NULL default '0',
  `last_check` datetime default NULL,
  `last_state_change` datetime default NULL,
  `last_hard_state_change` datetime default NULL,
  `last_time_up` datetime default NULL,
  `last_time_down` datetime default NULL,
  `last_time_unreachable` datetime default NULL,
  `has_been_checked` int(10) NOT NULL default '0',
  `is_being_freshened` int(10) NOT NULL default '0',
  `notified_on_down` int(10) NOT NULL default '0',
  `notified_on_unreachable` int(10) NOT NULL default '0',
  `current_notification_number` int(10) NOT NULL default '0',
  `no_more_notifications` int(10) NOT NULL default '0',
  `current_notification_id` int(10) NOT NULL default '0',
  `check_flapping_recovery_notification` int(10) NOT NULL default '0',
  `scheduled_downtime_depth` int(10) NOT NULL default '0',
  `pending_flex_downtime` int(10) NOT NULL default '0',
  `is_flapping` int(10) NOT NULL default '0',
  `flapping_comment_id` int(10) NOT NULL default '0',
  `percent_state_change` float default NULL,
  `total_services` int(10) NOT NULL default '0',
  `total_service_check_interval` int(10) NOT NULL default '0',
  `modified_attributes` int(10) NOT NULL default '0',
  `current_problem_id` int(10) NOT NULL default '0',
  `last_problem_id` int(10) NOT NULL default '0',
  `max_attempts` int(10) NOT NULL default '1',
  `current_event_id` int(10) NOT NULL default '0',
  `last_event_id` int(10) NOT NULL default '0',
  `process_performance_data` int(10) NOT NULL default '0',
  `last_update` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host_name` (`host_name`),
  KEY `host_status_1_idx` (`next_check`,`host_name`),
  KEY `host_status_2_idx` (`last_check`,`host_name`),
  KEY `host_status_3_idx` (`last_state_change`,`host_name`),
  KEY `host_status_4_idx` (`last_hard_state_change`,`host_name`),
  KEY `host_status_5_idx` (`last_time_up`,`host_name`),
  KEY `host_status_6_idx` (`last_time_down`,`host_name`),
  KEY `host_status_7_idx` (`last_time_unreachable`,`host_name`),
  KEY `host_status_8_idx` (`latency`,`host_name`),
  KEY `host_status_9_idx` (`execution_time`,`host_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `host`
--


-- --------------------------------------------------------

--
-- Table structure for table `hostdependency`
--

DROP TABLE IF EXISTS `hostdependency`;
CREATE TABLE IF NOT EXISTS `hostdependency` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` int(11) NOT NULL,
  `dependent_host_name` int(11) NOT NULL,
  `dependency_period` int(11) default NULL,
  `inherits_parent` tinyint(1) default NULL,
  `execution_failure_options` varchar(15) default NULL,
  `notification_failure_options` varchar(15) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hostdependency`
--


-- --------------------------------------------------------

--
-- Table structure for table `hostescalation`
--

DROP TABLE IF EXISTS `hostescalation`;
CREATE TABLE IF NOT EXISTS `hostescalation` (
  `id` int(11) NOT NULL auto_increment,
  `template` int(11) default NULL,
  `host_name` int(11) NOT NULL,
  `first_notification` int(11) default NULL,
  `last_notification` int(11) default NULL,
  `notification_interval` int(11) default NULL,
  `escalation_period` int(11) default NULL,
  `escalation_options` varchar(15) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hostescalation`
--


-- --------------------------------------------------------

--
-- Table structure for table `hostescalation_contact`
--

DROP TABLE IF EXISTS `hostescalation_contact`;
CREATE TABLE IF NOT EXISTS `hostescalation_contact` (
  `hostescalation` int(11) NOT NULL,
  `contact` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hostescalation_contact`
--


-- --------------------------------------------------------

--
-- Table structure for table `hostescalation_contactgroup`
--

DROP TABLE IF EXISTS `hostescalation_contactgroup`;
CREATE TABLE IF NOT EXISTS `hostescalation_contactgroup` (
  `hostescalation` int(11) NOT NULL,
  `contactgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hostescalation_contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `hostgroup`
--

DROP TABLE IF EXISTS `hostgroup`;
CREATE TABLE IF NOT EXISTS `hostgroup` (
  `id` int(11) NOT NULL,
  `hostgroup_name` varchar(75) default NULL,
  `alias` varchar(160) default NULL,
  `notes` varchar(160) default NULL,
  `notes_url` varchar(160) default NULL,
  `action_url` varchar(160) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hostgroup_name` (`hostgroup_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hostgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `host_contact`
--

DROP TABLE IF EXISTS `host_contact`;
CREATE TABLE IF NOT EXISTS `host_contact` (
  `host` int(11) NOT NULL,
  `contact` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `host_contact`
--


-- --------------------------------------------------------

--
-- Table structure for table `host_contactgroup`
--

DROP TABLE IF EXISTS `host_contactgroup`;
CREATE TABLE IF NOT EXISTS `host_contactgroup` (
  `host` int(11) NOT NULL,
  `contactgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `host_contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `host_hostgroup`
--

DROP TABLE IF EXISTS `host_hostgroup`;
CREATE TABLE IF NOT EXISTS `host_hostgroup` (
  `host` int(11) NOT NULL,
  `hostgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `host_hostgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `host_parents`
--

DROP TABLE IF EXISTS `host_parents`;
CREATE TABLE IF NOT EXISTS `host_parents` (
  `host` int(11) NOT NULL,
  `parents` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `host_parents`
--


-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `role_id` int(11) NOT NULL,
  `var` varchar(255) NOT NULL,
  `value` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permissions`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
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
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` int(11) NOT NULL,
  `service_description` varchar(160) NOT NULL,
  `display_name` varchar(160) default NULL,
  `is_volatile` tinyint(1) default NULL,
  `check_command` int(11) default NULL,
  `check_command_args` text,
  `initial_state` varchar(1) default NULL,
  `max_check_attempts` smallint(6) default NULL,
  `check_interval` smallint(6) default NULL,
  `retry_interval` smallint(6) default NULL,
  `active_checks_enabled` tinyint(1) default NULL,
  `passive_checks_enabled` tinyint(1) default NULL,
  `check_period` int(11) default NULL,
  `parallelize_check` tinyint(1) default NULL,
  `obsess_over_service` tinyint(1) default NULL,
  `check_freshness` tinyint(1) default NULL,
  `freshness_threshold` int(11) default NULL,
  `event_handler` int(11) default NULL,
  `event_handler_args` text,
  `event_handler_enabled` tinyint(1) default NULL,
  `low_flap_threshold` float default NULL,
  `high_flap_threshold` float default NULL,
  `flap_detection_enabled` tinyint(1) default NULL,
  `flap_detection_options` varchar(18) default NULL,
  `process_perf_data` tinyint(1) default NULL,
  `retain_status_information` tinyint(1) default NULL,
  `retain_nonstatus_information` tinyint(1) default NULL,
  `notification_interval` int(11) default NULL,
  `first_notification_delay` int(11) default NULL,
  `notification_period` int(11) default NULL,
  `notification_options` varchar(15) default NULL,
  `notifications_enabled` tinyint(1) default NULL,
  `stalking_options` varchar(15) default NULL,
  `notes` varchar(255) default NULL,
  `notes_url` varchar(255) default NULL,
  `action_url` varchar(255) default NULL,
  `icon_image` varchar(60) default NULL,
  `icon_image_alt` varchar(60) default NULL,
  `failure_prediction_enabled` tinyint(1) default NULL,
  `problem_has_been_acknowledged` int(10) NOT NULL default '0',
  `acknowledgement_type` int(10) NOT NULL default '0',
  `host_problem_at_last_check` int(10) NOT NULL default '0',
  `check_type` int(10) NOT NULL default '0',
  `current_state` int(10) NOT NULL default '0',
  `last_state` int(10) NOT NULL default '0',
  `last_hard_state` int(10) NOT NULL default '0',
  `plugin_output` text,
  `long_plugin_output` text,
  `perf_data` text,
  `state_type` int(10) NOT NULL default '0',
  `next_check` datetime default NULL,
  `should_be_scheduled` int(10) NOT NULL default '0',
  `last_check` datetime default NULL,
  `current_attempt` int(10) NOT NULL default '0',
  `current_event_id` int(10) NOT NULL default '0',
  `last_event_id` int(10) NOT NULL default '0',
  `current_problem_id` int(10) NOT NULL default '0',
  `last_problem_id` int(10) NOT NULL default '0',
  `last_notification` datetime default NULL,
  `next_notification` datetime default NULL,
  `no_more_notifications` int(10) NOT NULL default '0',
  `check_flapping_recovery_notification` int(10) NOT NULL default '0',
  `last_state_change` datetime default NULL,
  `last_hard_state_change` datetime default NULL,
  `last_time_ok` datetime default NULL,
  `last_time_warning` datetime default NULL,
  `last_time_unknown` datetime default NULL,
  `last_time_critical` datetime default NULL,
  `has_been_checked` int(10) NOT NULL default '0',
  `is_being_freshened` int(10) NOT NULL default '0',
  `notified_on_unknown` int(10) NOT NULL default '0',
  `notified_on_warning` int(10) NOT NULL default '0',
  `notified_on_critical` int(10) NOT NULL default '0',
  `current_notification_number` int(10) NOT NULL default '0',
  `current_notification_id` int(10) NOT NULL default '0',
  `latency` float default NULL,
  `execution_time` float default NULL,
  `is_executing` int(10) NOT NULL default '0',
  `check_options` int(10) NOT NULL default '0',
  `scheduled_downtime_depth` int(10) NOT NULL default '0',
  `pending_flex_downtime` int(10) NOT NULL default '0',
  `is_flapping` int(10) NOT NULL default '0',
  `flapping_comment_id` int(10) NOT NULL default '0',
  `percent_state_change` float default NULL,
  `modified_attributes` int(10) NOT NULL default '0',
  `max_attempts` int(10) NOT NULL default '0',
  `process_performance_data` int(10) NOT NULL default '0',
  `last_update` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `service_name` (`host_name`,`service_description`),
  KEY `service_status_1_idx` (`next_check`,`host_name`,`service_description`),
  KEY `service_status_2_idx` (`last_check`,`host_name`,`service_description`),
  KEY `service_status_3_idx` (`last_state_change`,`host_name`,`service_description`),
  KEY `service_status_4_idx` (`last_hard_state_change`,`host_name`,`service_description`),
  KEY `service_status_5_idx` (`last_time_ok`,`host_name`,`service_description`),
  KEY `service_status_6_idx` (`last_time_warning`,`host_name`,`service_description`),
  KEY `service_status_7_idx` (`last_time_unknown`,`host_name`,`service_description`),
  KEY `service_status_8_idx` (`last_time_critical`,`host_name`,`service_description`),
  KEY `service_status_9_idx` (`latency`,`host_name`,`service_description`),
  KEY `service_status_10_idx` (`execution_time`,`host_name`,`service_description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service`
--


-- --------------------------------------------------------

--
-- Table structure for table `servicedependency`
--

DROP TABLE IF EXISTS `servicedependency`;
CREATE TABLE IF NOT EXISTS `servicedependency` (
  `id` int(11) NOT NULL auto_increment,
  `service` int(11) NOT NULL,
  `dependent_service` int(11) NOT NULL,
  `dependency_period` int(11) default NULL,
  `inherits_parent` tinyint(1) default NULL,
  `execution_failure_options` varchar(15) default NULL,
  `notification_failure_options` varchar(15) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `service` (`service`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `servicedependency`
--


-- --------------------------------------------------------

--
-- Table structure for table `serviceescalation`
--

DROP TABLE IF EXISTS `serviceescalation`;
CREATE TABLE IF NOT EXISTS `serviceescalation` (
  `id` int(11) NOT NULL auto_increment,
  `service` int(11) NOT NULL,
  `first_notification` mediumint(9) default NULL,
  `last_notification` mediumint(9) default NULL,
  `notification_interval` mediumint(9) default NULL,
  `escalation_period` int(11) default NULL,
  `escalation_options` varchar(15) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `serviceescalation`
--


-- --------------------------------------------------------

--
-- Table structure for table `serviceescalation_contact`
--

DROP TABLE IF EXISTS `serviceescalation_contact`;
CREATE TABLE IF NOT EXISTS `serviceescalation_contact` (
  `serviceescalation` int(11) NOT NULL,
  `contact` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `serviceescalation_contact`
--


-- --------------------------------------------------------

--
-- Table structure for table `serviceescalation_contactgroup`
--

DROP TABLE IF EXISTS `serviceescalation_contactgroup`;
CREATE TABLE IF NOT EXISTS `serviceescalation_contactgroup` (
  `serviceescalation` int(11) NOT NULL,
  `contactgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `serviceescalation_contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `servicegroup`
--

DROP TABLE IF EXISTS `servicegroup`;
CREATE TABLE IF NOT EXISTS `servicegroup` (
  `id` int(11) NOT NULL auto_increment,
  `servicegroup_name` varchar(75) NOT NULL,
  `alias` varchar(160) NOT NULL,
  `notes` varchar(160) default NULL,
  `notes_url` varchar(160) default NULL,
  `action_url` varchar(160) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `servicegroup_name` (`servicegroup_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `servicegroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `service_contact`
--

DROP TABLE IF EXISTS `service_contact`;
CREATE TABLE IF NOT EXISTS `service_contact` (
  `service` int(11) NOT NULL,
  `contact` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_contact`
--


-- --------------------------------------------------------

--
-- Table structure for table `service_contactgroup`
--

DROP TABLE IF EXISTS `service_contactgroup`;
CREATE TABLE IF NOT EXISTS `service_contactgroup` (
  `service` int(11) NOT NULL,
  `contactgroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_contactgroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `service_servicegroup`
--

DROP TABLE IF EXISTS `service_servicegroup`;
CREATE TABLE IF NOT EXISTS `service_servicegroup` (
  `service` int(11) NOT NULL,
  `servicegroup` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_servicegroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `timeperiod`
--

DROP TABLE IF EXISTS `timeperiod`;
CREATE TABLE IF NOT EXISTS `timeperiod` (
  `id` int(11) NOT NULL auto_increment,
  `timeperiod_name` varchar(75) NOT NULL,
  `alias` varchar(160) NOT NULL,
  `sunday` varchar(50) default NULL,
  `monday` varchar(50) default NULL,
  `tuesday` varchar(50) default NULL,
  `wednesday` varchar(50) default NULL,
  `thursday` varchar(50) default NULL,
  `friday` varchar(50) default NULL,
  `saturday` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `timeperiod_name` (`timeperiod_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timeperiod`
--


-- --------------------------------------------------------

--
-- Table structure for table `timeperiod_exclude`
--

DROP TABLE IF EXISTS `timeperiod_exclude`;
CREATE TABLE IF NOT EXISTS `timeperiod_exclude` (
  `timeperiod` int(11) NOT NULL,
  `exclude` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timeperiod_exclude`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `realname` varchar(100) NOT NULL,
  `email` varchar(127) NOT NULL,
  `username` varchar(32) NOT NULL default '',
  `password` char(50) NOT NULL,
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
(1, 'Ninja Admin', 'per@op5.com', 'admin', 'd0bcecba632cad83350fce159fe23cd8ed4fa897b910ac6bd6', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_pref`
--

DROP TABLE IF EXISTS `user_pref`;
CREATE TABLE IF NOT EXISTS `user_pref` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `widget` int(11) NOT NULL,
  `var` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY  (`id`),
  KEY `user_var` (`user_id`,`var`),
  KEY `user_widget` (`user_id`,`widget`),
  KEY `user_widget_var` (`user_id`,`widget`,`var`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_pref`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `user_agent` varchar(40) NOT NULL,
  `token` varchar(32) NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_tokens`
--


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
