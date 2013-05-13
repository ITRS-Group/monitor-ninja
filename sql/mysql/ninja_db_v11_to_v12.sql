UPDATE ninja_saved_filters
	SET filter="[services] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0 and host.scheduled_downtime_depth = 0"
	WHERE filter_name="unhandled service problems"
	AND filter="[services] state != 0 and acknowledged = 0";

UPDATE ninja_saved_filters
	SET filter="[hosts] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0"
	WHERE filter_name="unhandled host problems"
	AND filter="[hosts] state != 0 and acknowledged = 0";