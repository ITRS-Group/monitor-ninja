description = Assume states during program downtime
logfile = assumed_states_during_program_downtime.log

assumed states during program downtime #1 {
	assumestatesduringnotrunning = true
	start_time = 1202684400
	end_time = 1202770800
	report_type = hosts
	objects {
		testhost
	}
	correct {
		TIME_UP_SCHEDULED = 0
		TIME_UP_UNSCHEDULED = 86400
		TIME_UNDETERMINED_NOT_RUNNING = 0
	}
}

first state is undetermined {
	assumestatesduringnotrunning = 0
	start_time = 1202690000
	end_time = 1202699000
	report_type = hosts
	objects {
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 3800
		TIME_UNDETERMINED_NOT_RUNNING = 5200
		subs {
			testhost {
				TIME_UP_UNSCHEDULED = 3800
				TIME_UNDETERMINED_NOT_RUNNING = 5200
			}
		}
	}
}
