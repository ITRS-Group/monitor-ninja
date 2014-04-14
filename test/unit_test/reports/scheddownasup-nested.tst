description = count scheduled downtime as uptime, nested
sqlfile = test_nested_downtimes.sql

global_vars {
	start_time = 1202684400
	end_time = 1202770800
	includesoftstates = 1
}

down_dtstart_dtstart_dtend_dtend: normal {
	report_type = hosts
	objects {
		down_dtstart_dtstart_dtend_dtend
	}
	scheduleddowntimeasuptime = 0
	correct {
		TIME_DOWN_UNSCHEDULED = 78600
		TIME_DOWN_SCHEDULED = 7800
	}
}

down_dtstart_dtstart_dtend_dtend: scheduled downtime as uptime {
	report_type = hosts
	objects {
		down_dtstart_dtstart_dtend_dtend
	}
	scheduleddowntimeasuptime = 1
	correct {
		TIME_UP_SCHEDULED = 7800
		TIME_DOWN_UNSCHEDULED = 78600
	}
}
