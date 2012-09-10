description = count scheduled downtime as uptime
logfile = scheddownasup.log

global_vars {
	start_time = 1202684400
	end_time = 1202770800
	include_soft_states = 1
}

up_down_dtstart_dtend_up: normal {
	host_name = up_down_dtstart_dtend_up
	scheduled_downtime_as_uptime = 0
	correct {
		TIME_UP_SCHEDULED = 0
		TIME_UP_UNSCHEDULED = 75600
		TIME_DOWN_SCHEDULED = 7200
		TIME_DOWN_UNSCHEDULED = 3600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

up_down_dtstart_dtend_up: scheduled downtime as uptime {
	host_name = up_down_dtstart_dtend_up
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_UP_SCHEDULED = 7200
		TIME_UP_UNSCHEDULED = 75600
		TIME_DOWN_SCHEDULED = 0
		TIME_DOWN_UNSCHEDULED = 3600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

down_dtstart_up_dtend: normal {
	host_name = down_dtstart_up_dtend
	scheduled_downtime_as_uptime = 0
	correct {
		TIME_UP_SCHEDULED = 3600
		TIME_UP_UNSCHEDULED = 75600
		TIME_DOWN_SCHEDULED = 3600
		TIME_DOWN_UNSCHEDULED = 3600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

down_dtstart_up_dtend: scheduled downtime as uptime {
	host_name = down_dtstart_up_dtend
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_UP_SCHEDULED = 7200
		TIME_UP_UNSCHEDULED = 75600
		TIME_DOWN_SCHEDULED = 0
		TIME_DOWN_UNSCHEDULED = 3600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

up_dtstart_down_dtend: normal {
	host_name = up_dtstart_down_dtend
	scheduled_downtime_as_uptime = 0
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_UP_SCHEDULED = 3600
		TIME_DOWN_SCHEDULED = 3600
		TIME_DOWN_UNSCHEDULED = 75600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

up_dtstart_down_dtend: scheduled downtime as uptime {
	host_name = up_dtstart_down_dtend
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_UP_SCHEDULED = 7200
		TIME_UP_UNSCHEDULED = 3600
		TIME_DOWN_SCHEDULED = 0
		TIME_DOWN_UNSCHEDULED = 75600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

up_dtstart_down_up_down_dtend: normal {
	host_name = up_dtstart_down_up_down_dtend
	scheduled_downtime_as_uptime = 0
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_UP_SCHEDULED = 3600
		TIME_DOWN_SCHEDULED = 3600
		TIME_DOWN_UNSCHEDULED = 75600
		TOTAL_TIME_SCHEDULED = 7200
		TOTAL_TIME_UNSCHEDULED = 79200
		TOTAL_TIME_ACTIVE = 86400
		TOTAL_TIME_KNOWN = 86400
	}
}

up_dtstart_down_up_down_dtend: scheduled downtime as uptime {
	host_name = up_dtstart_down_up_down_dtend
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_UP_SCHEDULED = 7200
		TIME_DOWN_SCHEDULED = 0
		TIME_DOWN_UNSCHEDULED = 75600
	}
}

group avail: normal {
	host_name {
		up_down_dtstart_dtend_up
		up_dtstart_down_dtend
	}
	scheduled_downtime_as_uptime = 0
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_UP_SCHEDULED = 0
		TIME_DOWN_SCHEDULED = 7200
		TIME_DOWN_UNSCHEDULED = 75600
	}
}

group avail: scheduled downtime as uptime {
	host_name {
		up_down_dtstart_dtend_up
		up_dtstart_down_dtend
	}
	scheduled_downtime_as_uptime = 1
	correct {
		TIME_UP_UNSCHEDULED = 3600
		TIME_UP_SCHEDULED = 7200
		TIME_DOWN_SCHEDULED = 0
		TIME_DOWN_UNSCHEDULED = 75600
	}
}
