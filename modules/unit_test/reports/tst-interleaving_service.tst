description = Interleaving downtimes and multiple states, services
logfile = interleaving_states_service.log

worst state, down as down {
	start_time = 000000000
	end_time = 000005000
	service_description {
		host1;PING
		host2;PING
		host2;PING2
	}
	correct {
		TIME_OK_UNSCHEDULED = 140
		TIME_OK_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 700
		TIME_WARNING_SCHEDULED = 859
		TIME_CRITICAL_UNSCHEDULED = 2401
		TIME_CRITICAL_SCHEDULED = 900
	}
}
worst state, down as up {
	start_time = 000000000
	end_time = 000005000
	service_description {
		host1;PING
		host2;PING
		host2;PING2
	}
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_UNSCHEDULED = 140
		TIME_OK_SCHEDULED = 859
		TIME_WARNING_UNSCHEDULED = 1600
		TIME_WARNING_SCHEDULED = 0
		TIME_CRITICAL_UNSCHEDULED = 2401
		TIME_CRITICAL_SCHEDULED = 0
	}
}
best state, down as down {
	start_time = 000000000
	end_time = 000005000
	service_description {
		host1;PING
		host2;PING
		host2;PING2
	}
	cluster_mode = 1
	correct {
		TIME_OK_UNSCHEDULED = 2800
		TIME_OK_SCHEDULED = 404
		TIME_WARNING_UNSCHEDULED = 1100
		TIME_WARNING_SCHEDULED = 696
		TIME_CRITICAL_UNSCHEDULED = 0
		TIME_CRITICAL_SCHEDULED = 0
	}
}
best state, down as up {
	start_time = 000000000
	end_time = 000005000
	service_description {
		host1;PING
		host2;PING
		host2;PING2
	}
	scheduled_downtime_as_uptime = 1
	cluster_mode = 1
	correct {
		TIME_OK_UNSCHEDULED = 2800
		TIME_OK_SCHEDULED = 1100
		TIME_WARNING_UNSCHEDULED = 1100
		TIME_WARNING_SCHEDULED = 0
		TIME_CRITICAL_UNSCHEDULED = 0
		TIME_CRITICAL_SCHEDULED = 0
	}
}
