description = Reports using timeperiods
logfile = softstates.log

# for now, these tests only work when run on CET-using machine

global_vars {
	# mon feb 11 00:00:00 CET 2008
	start_time = 1202684400
	# tue feb 12 00:00:00 CET 2008
	end_time = 1202770800
	report_type = hosts
	objects {
		testhost
	}
	timeperiod {
		timeperiod_name = an_exclude
		2008-02-11 = 22:00-23:30
	}
}

monday lunchtime 12:00-13:00 {
	timeperiod {
		monday = 12:00-13:00
	}
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_INACTIVE = 82800
	}
}

simple timeperiod {
	timeperiod {
		monday = 02:15-03:25
	}
	correct {
		TIME_UP_UNSCHEDULED = 4200
		TIME_INACTIVE = 82200
		TOTAL_TIME_ACTIVE = 4200
	}
}

slightly tricksier timeperiod {
	timeperiod {
		monday = 02:15-03:25,04:15-05:25
	}
	correct {
		TIME_UP_UNSCHEDULED = 8400
		TIME_INACTIVE = 78000
		TOTAL_TIME_ACTIVE = 8400
	}
}

tricksy timeperiod indeed {
	timeperiod {
		monday = 00:15-00:25,01:15-01:25,02:15-02:25,03:15-03:25,04:00-05:00
	}
	correct {
		TIME_UP_UNSCHEDULED = 6000
		TIME_INACTIVE = 80400
		TOTAL_TIME_ACTIVE = 6000
	}
}

timeperiod with simple exception {
	timeperiod {
		monday = 00:00-24:00
		2008-02-11 = 22:00-23:30
	}
	correct {
		TIME_UP_UNSCHEDULED = 4200
		TIME_DOWN_UNSCHEDULED = 1200
		TIME_INACTIVE = 81000
	}
}

# ensure that exceptions are only added once
timeperiod with simple exception and three days {
	# sun feb 10 00:00:00 CET 2008
	start_time = 1202598000
	# wed feb 13 00:00:00 CET 2008
	end_time = 1202857200
	timeperiod {
		monday = 00:00-24:00
		2008-02-11 = 22:00-23:30
	}
	correct {
		TIME_UP_UNSCHEDULED = 4200
		TIME_DOWN_UNSCHEDULED = 1200
		TIME_INACTIVE = 253800
	}
}

timeperiod with simple excludes {
	timeperiod {
		monday = 00:00-24:00
		excludes {
			0 = an_exclude
		}
	}
	correct {
		TIME_UP_UNSCHEDULED = 79200
		TIME_DOWN_UNSCHEDULED = 1800
		TIME_INACTIVE = 5400
	}
}

# apparently, some versions have failed to set the "down" event's stop to the
# timeperiod end, rather than the report period end.
another timeperiod {
	timeperiod {
		monday = 23:00-23:30
	}
	correct {
		TIME_UP_UNSCHEDULED = 600
		TIME_DOWN_UNSCHEDULED = 1200
		TIME_INACTIVE = 84600
	}
}
