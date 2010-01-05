description = Assume service states during program downtime
logfile = assumed_states_during_program_downtime_service.log

assumed service states during program downtime #1 {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost
	service_description = PING
	correct {
		TIME_OK_SCHEDULED = 0
		TIME_OK_UNSCHEDULED = 86400
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}
