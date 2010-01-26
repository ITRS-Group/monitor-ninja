description = Group availability
logfile = groups.log

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
	include_soft_states = 0
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
	include_soft_states = 0
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
	cluster_mode = 1
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
	cluster_mode = 1
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
	include_soft_states = 0
	cluster_mode = 1
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
	include_soft_states = 0
	cluster_mode = 1
	host_name {
		testhost2
		testhost
	}
	correct {
		TIME_UP_UNSCHEDULED = 86400
	}
}
