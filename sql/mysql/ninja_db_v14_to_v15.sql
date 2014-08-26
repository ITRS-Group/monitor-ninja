INSERT INTO `ninja_saved_filters` (`username`, `filter_name`, `filter_table`, `filter`, `filter_description`) VALUES
(NULL, 'error messages', 'log_messages', '[log_messages] severity <= 3', 'error messages'),
(NULL, 'warning messages', 'log_messages', '[log_messages] severity = 4', 'warning messages'),
(NULL, 'failed login', 'log_messages', '[log_messages] (event = 4625) or (facility = 10 and severity = 2)', 'failed login'),
(NULL, 'windows account locked', 'log_messages', '[log_messages] event = 4740', 'windows account locked'),
(NULL, 'reboot', 'log_messages', '[log_messages] (event = 4740) or (ident = "shutdown")', 'reboot'),
(NULL, 'sudo usage', 'log_messages', '[log_messages] ident = "sudo"', 'sudo usage'),
(NULL, 'kernel messages', 'log_messages', '[log_messages] facility = 0', 'kernel messages'),
(NULL, 'monitoring events', 'log_messages', '[log_messages] ident = "monitor"', 'monitoring events');

