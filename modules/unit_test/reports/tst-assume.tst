description = Assume states during program downtime
logfile = assumed_states_during_program_downtime.log

assumed states during program downtime #1 {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost
	correct {
		TIME_UP_SCHEDULED = 0
		TIME_UP_UNSCHEDULED = 86400
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}
