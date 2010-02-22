<?php defined('SYSPATH') OR die("No direct access allowed");

echo "<br />" . $label_overall_totals . "<br />\n";
foreach ($result as $service_name => $ary) {
	$foo = explode(';');
	$host_name = $foo[0];
	$service = $foo[1];
	echo $label_service . "'" . $service . "' on " .
		$label_host . "'" . $host_name . "'<br />\n";
	$this->_print_alert_totals_table($label_service_alerts, $ary['service'], $service_state_names, $ary['service_totals']);
}
printf("Report completed in %.3f seconds<br />\n", $completion_time);
