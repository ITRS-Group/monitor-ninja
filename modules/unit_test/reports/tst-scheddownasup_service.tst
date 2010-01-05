description = count scheduled service downtime as uptime
logfile = scheddownasup_service.log

scheduled service downtime as uptime {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost
	service_description = PING
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 3600
		TIME_OK_UNSCHEDULED = 75600
		TIME_WARNING_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 7200
	}
}
