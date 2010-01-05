description = Reports using timeperiods
logfile = softstates.log

global_vars {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost
}

monday lunchtime 12:00-13:00 {
	monday = 12:00-13:00
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_INACTIVE = 82800
	}
}

simple timeperiod {
	monday = 02:15-03:25
	correct {
		TIME_UP_UNSCHEDULED = 4200
		TIME_INACTIVE = 82200
		TOTAL_TIME_ACTIVE = 4200
	}
}

slightly tricksier timeperiod {
	monday = 02:15-03:25,04:15-05:25
	correct {
		TIME_UP_UNSCHEDULED = 8400
		TIME_INACTIVE = 78000
		TOTAL_TIME_ACTIVE = 8400
	}
}

tricksy timeperiod indeed {
	monday = 00:15-00:25,01:15-01:25,02:15-02:25,03:15-03:25,04:00-05:00
	correct {
		TIME_UP_UNSCHEDULED = 6000
		TIME_INACTIVE = 80400
		TOTAL_TIME_ACTIVE = 6000
	}
}

timeperiod with simple exception {
	monday = 00:00-24:00
	2008-02-11 = 22:00-23:30
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_DOWN_UNSCHEDULED = 1800
		TIME_INACTIVE = 81000
	}
}
