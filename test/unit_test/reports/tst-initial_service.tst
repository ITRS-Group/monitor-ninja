description = Assume initial service states
logfile = assume_initial_service_states.log

assume current service state {
	start_time = 1202684400
	end_time = 1202770800
	assume_initial_states = 1
	initial_assumed_service_state = -1
	host_name = testhost
	service_description = PING
	correct {
		TIME_OK_UNSCHEDULED = 82800
		TIME_WARNING_UNSCHEDULED = 3600
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

assume state warning {
	start_time = 1202684400
	end_time = 1202770800
	assume_initial_states = 1
	initial_assumed_service_state = 1
	host_name = testhost
	service_description = PING
	correct {
		TIME_OK_UNSCHEDULED = 82800
		TIME_WARNING_UNSCHEDULED = 3600
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

