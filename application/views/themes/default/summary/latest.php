<?php defined('SYSPATH') OR die("No direct access allowed");

echo "<table><tr><th>$label_time</th><th>$label_alert_type</th>" .
	"<th>$label_host</th><th>$label_service</th>" .
	"<th>$label_state</th><th>$label_state_type</th>" .
	"<th>$label_information</th></tr>\n";

foreach ($result as $ary) {
	echo "<tr>\n";
	if (empty($ary['service_description'])) {
		$alert_type = $label_host_alert;
		$ary['service_description'] = 'N/A';
		$state_ary = $host_state_names;
	} else {
		$alert_type = $label_service_alert;
		$state_ary = $service_state_names;
	}

	echo "<td>" . date("Y-m-d H:i:s", $ary['timestamp']) . "</td>\n";
	echo "<td>$alert_type</td>\n";
	echo "<td>$ary[host_name]</td>\n";
	echo "<td>$ary[service_description]</td>\n";
	echo "<td>$ary[total_alerts]</td>\n";
	echo "</tr>\n\n";
}
echo "</table>\n";

printf("Report completed in %.3f seconds<br />\n", $completion_time);
