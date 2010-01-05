description = Assume initial states
logfile = assume_initial_states.log

assume current state {
	start_time = 1202684400
	end_time = 1202770800
	assume_initial_states = 1
	initial_assumed_host_state = -1
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

assume state unreachable {
	start_time = 1202684400
	end_time = 1202770800
	assume_initial_states = 1
	initial_assumed_host_state = 2
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_UNREACHABLE_UNSCHEDULED = 3600
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}
	
assume first real state {
	start_time = 1202684400
	end_time = 1202770800
	assume_initial_states = 1
	initial_assumed_host_state = -3
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 86400
		TIME_DOWN_UNSCHEDULED = 0
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}
