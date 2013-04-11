INSERT INTO `ninja_saved_filters` (`username`, `filter_name`, `filter_table`, `filter`, `filter_description`) VALUES
(NULL,'acknowledged hosts','hosts','[hosts] acknowledged = 1','acknowledged hosts'),
(NULL,'acknowledged services','services','[services] acknowledged = 1','acknowledged services'),
(NULL,'host groups with problems','hostgroups','[hostgroups] (worst_host_state != 0 or worst_service_state != 0)','host groups with problems'),
(NULL,'service groups with problems','servicegroups','[servicegroups] worst_service_state != 0','service groups with problems'),
(NULL,'unhandled host problems','hosts','[hosts] state != 0 and acknowledged = 0','unhandled host problems'),
(NULL,'unhandled problems','services','[services] in \"unhandled service problems\"\nor host in \"unhandled host problems\"','unhandled problems'),
(NULL,'unhandled service problems','services','[services] state != 0 and acknowledged = 0','unhandled service problems');

