<?php defined('SYSPATH') OR die("No direct access allowed");

echo "<table><tr><th>$label_rank</th><th>$label_producer_type</th>" .
	"<th>$label_host</th><th>$label_service</th>" .
	"<th>$label_total_alerts</th></tr>\n";

foreach ($result as $rank => $ary) {
	echo "<tr>\n";
	if (empty($ary['service_description'])) {
		$producer = $label_host;
		$ary['service_description'] = 'N/A';
	} else {
		$producer = $label_service;
	}

	echo "<td>$rank</td>\n";
	echo "<td>$producer</td>\n";
	echo "<td>$ary[host_name]</td>\n";
	echo "<td>$ary[service_description]</td>\n";
	echo "<td>$ary[total_alerts]</td>\n";
	echo "</tr>\n\n";
}
echo "</table>\n";

printf("Report completed in %.3f seconds<br />\n", $completion_time);
