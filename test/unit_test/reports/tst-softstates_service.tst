description = Include/exclude service soft states
logfile = softstates_service.log

Include service soft states {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 1
	host_name = testhost
	service_description = PING
	correct {
		TIME_OK_UNSCHEDULED = 82800
		TIME_WARNING_UNSCHEDULED = 3600
	}
}

Exclude service soft states {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	host_name = testhost
	service_description = PING
	correct {
		TIME_OK_UNSCHEDULED = 83400
		TIME_WARNING_UNSCHEDULED = 3000
		TOTAL_TIME_WARNING = 3000
		TOTAL_TIME_OK = 83400
		PERCENT_TOTAL_TIME_OK = 96.527777777778
		PERCENT_TOTAL_TIME_WARNING = 3.4722222222222
		PERCENT_TIME_OK_UNSCHEDULED = 96.527777777778
		PERCENT_TIME_WARNING_UNSCHEDULED = 3.4722222222222
		PERCENT_KNOWN_TIME_WARNING_UNSCHEDULED = 3.4722222222222
		PERCENT_KNOWN_TIME_OK_UNSCHEDULED = 96.527777777778
	}
}
