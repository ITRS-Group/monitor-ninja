INSERT INTO `ninja_saved_filters` (`username`, `filter_name`, `filter_table`, `filter`, `filter_description`) VALUES
('monitor', 'error messages', 'log_messages', '[log_messages] severity <= 3', 'error messages'),
('monitor', 'warning messages', 'log_messages', '[log_messages] severity = 4', 'warning messages'),
('monitor', 'failed login', 'log_messages', '[log_messages] (event = 4625) or (facility = 10 and severity = 2)', 'failed login'),
('monitor', 'windows account locked', 'log_messages', '[log_messages] event = 4740', 'windows account locked'),
('monitor', 'reboot', 'log_messages', '[log_messages] (event = 4740) or (ident = "shutdown")', 'reboot'),
('monitor', 'sudo usage', 'log_messages', '[log_messages] ident = "sudo"', 'sudo usage'),
('monitor', 'kernel messages', 'log_messages', '[log_messages] facility = 0', 'kernel messages'),
('monitor', 'monitoring events', 'log_messages', '[log_messages] ident = "monitor"', 'monitoring events');

