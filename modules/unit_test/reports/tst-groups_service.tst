description = Group availability for services
logfile = groups_service.log

Group availability including soft states (services) {
	start_time = 1202684400
	end_time = 1202770800
	service_description {
		testhost;PING
		testhost2;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 82800
		TIME_WARNING_UNSCHEDULED = 3600
	}
}

Group availability including soft states, reversed host order (services) {
	start_time = 1202684400
	end_time = 1202770800
	service_description {
		testhost2;PING
		testhost;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 82800
		TIME_WARNING_UNSCHEDULED = 3600
	}
}

Group availability excluding soft states (services) {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	service_description {
		testhost;PING
		testhost2;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 83400
		TIME_WARNING_UNSCHEDULED = 3000
	}
}

Group availability excluding soft states, reversed host order (services) {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	service_description {
		testhost2;PING
		testhost;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 83400
		TIME_WARNING_UNSCHEDULED = 3000
	}
}

Cluster mode availability including soft states (services) {
	start_time = 1202684400
	end_time = 1202770800
	cluster_mode = 1
	service_description {
		testhost;PING
		testhost2;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 86400
	}
}

Cluster mode availability including soft states, reversed host order (services) {
	start_time = 1202684400
	end_time = 1202770800
	cluster_mode = 1
	service_description {
		testhost2;PING
		testhost;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 86400
	}
}

Cluster mode availability excluding soft states (services) {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	cluster_mode = 1
	service_description {
		testhost;PING
		testhost2;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 86400
	}
}

Cluster mode availability excluding soft states, reversed host order (services) {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 0
	cluster_mode = 1
	service_description {
		testhost2;PING
		testhost;PING
	}
	correct {
		TIME_OK_UNSCHEDULED = 86400
	}
}
