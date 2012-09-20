description = Assume service states during program downtime
logfile = assumed_states_during_program_downtime_service.log

assumed service states during program downtime #1 {
	assumestatesduringnotrunning = true
	start_time = 1202684400
	end_time = 1202770800
	service_description {
		testhost;PING
	}
	correct {
		TIME_OK_SCHEDULED = 0
		TIME_OK_UNSCHEDULED = 86400
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

first state is undetermined {
	start_time = 1202690000
	end_time = 1202699000
	service_description {
		testhost;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 3800
		TIME_UNDETERMINED_NOT_RUNNING = 5200
		subs {
			testhost;PING {
				TIME_OK_UNSCHEDULED = 3800
				TIME_UNDETERMINED_NOT_RUNNING = 5200
			}
		}
	}
}
