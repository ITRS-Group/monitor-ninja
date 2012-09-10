description = Assume initial states
logfile = assume_initial_states.log

assume whatever and get real state from db {
	start_time = 1202857200
	end_time = 1202943600
	assume_initial_states = 1
	initial_assumed_host_state = -1
	host_name = testhost
	correct {
		TIME_DOWN_UNSCHEDULED = 86400
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

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

assume first real state outside report period end {
	start_time = 1202598000
	end_time = 1202684400
	assume_initial_states = 1
	initial_assumed_host_state = -3
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 86400
		TIME_DOWN_UNSCHEDULED = 0
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}
