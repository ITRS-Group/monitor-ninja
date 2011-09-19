description = count scheduled service downtime as uptime
logfile = scheddownasup_service.log
global_vars {
	include_soft_states = 0
}

scheduled service downtime as uptime {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost
	service_description = PING
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 3600
		TIME_OK_UNSCHEDULED = 75600
		TIME_WARNING_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 7200
	}
}

host in scheduled downtime, service as uptime {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost2
	service_description = PING
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 3600
		TIME_OK_UNSCHEDULED = 75600
		TIME_WARNING_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 7200
	}
}

host in scheduled downtime, service as uptime, 2 services {
	start_time = 1202684400
	end_time = 1202770800
	service_description {
		testhost;PING
		testhost2;PING
	}
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 3500
		TIME_OK_UNSCHEDULED = 75600
		TIME_WARNING_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 7300
		subs {
			testhost;PING {
				TIME_OK_SCHEDULED = 3600
				TIME_OK_UNSCHEDULED = 75600
				TIME_WARNING_SCHEDULED = 0
				TIME_WARNING_UNSCHEDULED = 7200
			}
			testhost2;PING {
				TIME_OK_SCHEDULED = 3600
				TIME_OK_UNSCHEDULED = 75600
				TIME_WARNING_SCHEDULED = 0
				TIME_WARNING_UNSCHEDULED = 7200
			}
		}
	}
}

host in dt before report_period starts, service never in dt {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost3
	service_description = PING
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 2100
		TIME_CRITICAL_UNSCHEDULED = 84300
	}
}

host with two services {
	start_time = 1202684400
	end_time = 1202770800
	host_name = testhost2
	service_description {
		testhost2;PING
		testhost2;PING2
	}
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_OK_SCHEDULED = 3600
		TIME_OK_UNSCHEDULED = 75600
		TIME_WARNING_SCHEDULED = 0
		TIME_WARNING_UNSCHEDULED = 7200
		subs {
			testhost2;PING {
				TIME_OK_SCHEDULED = 3600
				TIME_OK_UNSCHEDULED = 75600
				TIME_WARNING_SCHEDULED = 0
				TIME_WARNING_UNSCHEDULED = 7200
			}
			testhost2;PING2 {
				TIME_OK_SCHEDULED = 3600
				TIME_OK_UNSCHEDULED = 75600
				TIME_WARNING_SCHEDULED = 0
				TIME_WARNING_UNSCHEDULED = 7200
			}
		}
	}
}
