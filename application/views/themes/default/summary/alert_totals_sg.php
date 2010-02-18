<?php defined('SYSPATH') OR die("No direct access allowed");

echo "<br />" . $label_overall_totals . "<br />\n";
foreach ($result as $sg_name => $ary) {
	echo "$label_servicegroup '" . $sg_name . "'<br />\n";
	$this->_print_alert_totals_table($label_host_alerts, $ary['host'], $host_state_names, $ary['host_totals']);
	$this->_print_alert_totals_table($label_service_alerts, $ary['service'], $service_state_names, $ary['service_totals']);
}
printf("Report completed in %.3f seconds<br />\n", $completion_time);
