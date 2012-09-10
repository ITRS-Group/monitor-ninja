description = Include/exclude soft states
logfile = softstates.log

Include soft states {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 1
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
	}
}

Exclude soft states {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	host_name = testhost
	correct {
		TIME_UP_UNSCHEDULED = 83400
		TIME_DOWN_UNSCHEDULED = 3000
	}
}
