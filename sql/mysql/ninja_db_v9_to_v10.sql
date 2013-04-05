INSERT INTO `ninja_saved_filters` (`username`, `filter_name`, `filter_table`, `filter`, `filter_description`) VALUES
(NULL,'Acknowledged hosts','hosts','[hosts] acknowledged = 1','Acknowledged hosts'),
(NULL,'Acknowledged services','services','[services] acknowledged = 1','Acknowledged services'),
(NULL,'Host groups with problems','hostgroups','[hostgroups] (worst_host_state != 0 or worst_service_state != 0)','Host groups with problems'),
(NULL,'Service groups with problems','servicegroups','[servicegroups] worst_service_state != 0','service groups with problems'), (NULL,'unhandled host problems','hosts','[hosts] state != 0 and acknowledged = 0','Unhandled host problems'),
(NULL,'Unhandled problems','services','[services] in \"unhandled service problems\"\nor host in \"unhandled host problems\"','Unhandled problems'),
(NULL,'Unhandled service problems','services','[services] state != 0 and acknowledged = 0','Unhandled service problems');

