description = Group availability
logfile = groups.log

global_vars {
	includesoftstates = 1
}

Group availability including soft states {
	start_time = 1202684400
	end_time = 1202770800
	host_name {
		testhost
		testhost2
	}
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
	}
}

Group availability including soft states, excluding OK {
	start_time = 1202684400
	end_time = 1202770800
	host_filter_status {
		0 = -2
	}
	host_name {
		testhost
		testhost2
	}
	correct {
		TIME_EXCLUDED_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
	}
}

Group availability including soft states (hostgroups) {
	start_time = 1202684400
	end_time = 1202770800
	hostgroup {
		group1 {
			testhost
		}
		group2 {
			testhost2
		}
	}
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
	}
}

Group availability including soft states, reversed host order {
	start_time = 1202684400
	end_time = 1202770800
	host_name {
		testhost2
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 82800
		TIME_DOWN_UNSCHEDULED = 3600
	}
}

Group availability excluding soft states {
	start_time = 1202684400
	end_time = 1202770800
	includesoftstates = 0
	host_name {
		testhost
		testhost2
	}
	correct {
		TIME_UP_UNSCHEDULED = 83400
		TIME_DOWN_UNSCHEDULED = 3000
	}
}

Group availability excluding soft states, reversed host order {
	start_time = 1202684400
	end_time = 1202770800
	includesoftstates = 0
	host_name {
		testhost2
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 83400
		TIME_DOWN_UNSCHEDULED = 3000
	}
}

Cluster mode availability including soft states {
	start_time = 1202684400
	end_time = 1202770800
	sla_mode = 2
	host_name {
		testhost
		testhost2
	}
	correct {
		TIME_UP_UNSCHEDULED = 86400
	}
}

Cluster mode availability including soft states, reversed host order {
	start_time = 1202684400
	end_time = 1202770800
	sla_mode = 2
	host_name {
		testhost2
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 86400
	}
}

Cluster mode availability excluding soft states {
	start_time = 1202684400
	end_time = 1202770800
	includesoftstates = 0
	sla_mode = 2
	host_name {
		testhost
		testhost2
	}
	correct {
		TIME_UP_UNSCHEDULED = 86400
	}
}

Cluster mode availability excluding soft states, reversed host order {
	start_time = 1202684400
	end_time = 1202770800
	includesoftstates = 0
	sla_mode = 2
	host_name {
		testhost2
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 86400
	}
}

Average availability including soft states {
        start_time = 1202684400
        end_time = 1202770800
	sla_mode = 1
        host_name {
                testhost
                testhost2
        }
        correct {
                TIME_UP_UNSCHEDULED = 84600
                TIME_DOWN_UNSCHEDULED = 1800
        }
}
